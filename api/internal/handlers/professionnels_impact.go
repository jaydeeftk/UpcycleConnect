package handlers

import (
	"fmt"
	"net/http"
	"sort"
	"strconv"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"

	"github.com/jung-kurt/gofpdf"
)

// co2FacteurParKg : estimation indicative du CO2 evite par kg de matiere
// reemployee plutot que jetee. Valeur transparente et ajustable ; l'impact reel
// depend du materiau. Toujours presentee comme une estimation, jamais une mesure.
const co2FacteurParKg = 1.8

type impactMateriau struct {
	Type    string  `json:"type"`
	Nombre  int     `json:"nombre"`
	PoidsKg float64 `json:"poids_kg"`
}

type impactPro struct {
	ObjetsRecuperes int              `json:"objets_recuperes"`
	PoidsTotalKg    float64          `json:"poids_total_kg"`
	CO2EstimeKg     float64          `json:"co2_estime_kg"`
	CO2Facteur      float64          `json:"co2_facteur"`
	ProjetsTotal    int              `json:"projets_total"`
	ProjetsTermines int              `json:"projets_termines"`
	Materiaux       []impactMateriau `json:"materiaux"`
}

// parsePoidsKg extrait la valeur numerique d'un poids stocke en texte
// ("12 kg", "12,5kg", "8") ; retourne 0 si rien d'exploitable.
func parsePoidsKg(s string) float64 {
	s = strings.TrimSpace(strings.ReplaceAll(s, ",", "."))
	var b strings.Builder
	dotSeen := false
	for _, c := range s {
		switch {
		case c >= '0' && c <= '9':
			b.WriteRune(c)
		case c == '.' && !dotSeen && b.Len() > 0:
			dotSeen = true
			b.WriteRune(c)
		default:
			if b.Len() > 0 {
				v, _ := strconv.ParseFloat(b.String(), 64)
				return v
			}
		}
	}
	v, _ := strconv.ParseFloat(b.String(), 64)
	return v
}

func calculerImpactPro(profID int) (impactPro, error) {
	imp := impactPro{CO2Facteur: co2FacteurParKg}

	rows, err := database.DB.Query(
		`SELECT COALESCE(NULLIF(TRIM(Type),''),'Autre'), COALESCE(Poids,'')
		FROM Objets WHERE Id_Professionnels = ? AND Statut = 'recupere'`, profID,
	)
	if err != nil {
		return imp, err
	}
	defer rows.Close()

	parType := map[string]*impactMateriau{}
	for rows.Next() {
		var typ, poids string
		if err := rows.Scan(&typ, &poids); err != nil {
			return imp, err
		}
		kg := parsePoidsKg(poids)
		imp.ObjetsRecuperes++
		imp.PoidsTotalKg += kg
		m, ok := parType[typ]
		if !ok {
			m = &impactMateriau{Type: typ}
			parType[typ] = m
		}
		m.Nombre++
		m.PoidsKg += kg
	}
	if err := rows.Err(); err != nil {
		return imp, err
	}

	imp.Materiaux = make([]impactMateriau, 0, len(parType))
	for _, m := range parType {
		imp.Materiaux = append(imp.Materiaux, *m)
	}
	sort.Slice(imp.Materiaux, func(i, j int) bool {
		if imp.Materiaux[i].Nombre != imp.Materiaux[j].Nombre {
			return imp.Materiaux[i].Nombre > imp.Materiaux[j].Nombre
		}
		return imp.Materiaux[i].Type < imp.Materiaux[j].Type
	})

	database.DB.QueryRow(
		`SELECT COUNT(*), COALESCE(SUM(CASE WHEN Statut='termine' THEN 1 ELSE 0 END),0)
		FROM Projets WHERE Id_Professionnels = ?`, profID,
	).Scan(&imp.ProjetsTotal, &imp.ProjetsTermines)

	imp.CO2EstimeKg = imp.PoidsTotalKg * co2FacteurParKg
	return imp, nil
}

// ProfessionnelImpact expose le bilan d'impact ecologique (PR7) et la
// repartition par materiau (PR8) du professionnel connecte, en JSON.
func ProfessionnelImpact(w http.ResponseWriter, r *http.Request) {
	_, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	imp, err := calculerImpactPro(profID)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, imp)
}

// ProfessionnelImpactPDF genere le bilan d'impact ecologique en PDF (PR7).
func ProfessionnelImpactPDF(w http.ResponseWriter, r *http.Request) {
	userID, profID, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	imp, err := calculerImpactPro(profID)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}

	var entreprise, nom, prenom string
	database.DB.QueryRow(
		`SELECT COALESCE(pa.Nom_Entreprise,''), COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Utilisateurs u JOIN Professionnels_artisans pa ON pa.Id_Utilisateurs=u.Id_Utilisateurs
		WHERE u.Id_Utilisateurs=?`, userID,
	).Scan(&entreprise, &nom, &prenom)

	pdf := gofpdf.New("P", "mm", "A4", "")
	tr := pdf.UnicodeTranslatorFromDescriptor("")
	pdf.AddPage()

	pdf.SetFont("Arial", "B", 20)
	pdf.Cell(0, 12, "UpcycleConnect")
	pdf.Ln(13)
	pdf.SetFont("Arial", "B", 16)
	pdf.Cell(0, 10, tr("Bilan d'impact ecologique"))
	pdf.Ln(11)

	pdf.SetFont("Arial", "", 11)
	titulaire := strings.TrimSpace(entreprise)
	if titulaire == "" {
		titulaire = strings.TrimSpace(prenom + " " + nom)
	}
	pdf.Cell(0, 7, tr("Professionnel : "+titulaire))
	pdf.Ln(6)
	pdf.Cell(0, 7, tr("Edite le "+time.Now().Format("02/01/2006")))
	pdf.Ln(12)

	pdf.SetFont("Arial", "B", 13)
	pdf.Cell(0, 8, tr("Indicateurs"))
	pdf.Ln(9)
	pdf.SetFont("Arial", "", 11)
	ligne := func(label, val string) {
		pdf.CellFormat(120, 8, tr(label), "1", 0, "L", false, 0, "")
		pdf.CellFormat(60, 8, tr(val), "1", 1, "R", false, 0, "")
	}
	ligne("Objets recuperes / valorises", strconv.Itoa(imp.ObjetsRecuperes))
	ligne("Poids total valorise", fmt.Sprintf("%.1f kg", imp.PoidsTotalKg))
	ligne("Projets d'upcycling realises", fmt.Sprintf("%d / %d", imp.ProjetsTermines, imp.ProjetsTotal))
	ligne(fmt.Sprintf("CO2 evite (estimation, %.1f kg/kg)", imp.CO2Facteur), fmt.Sprintf("%.1f kg", imp.CO2EstimeKg))
	pdf.Ln(8)

	pdf.SetFont("Arial", "B", 13)
	pdf.Cell(0, 8, tr("Repartition par materiau"))
	pdf.Ln(9)
	pdf.SetFont("Arial", "B", 11)
	pdf.CellFormat(100, 8, tr("Materiau"), "1", 0, "L", false, 0, "")
	pdf.CellFormat(40, 8, "Nombre", "1", 0, "R", false, 0, "")
	pdf.CellFormat(40, 8, "Poids", "1", 1, "R", false, 0, "")
	pdf.SetFont("Arial", "", 11)
	if len(imp.Materiaux) == 0 {
		pdf.CellFormat(180, 8, tr("Aucun objet recupere pour le moment."), "1", 1, "C", false, 0, "")
	} else {
		for _, m := range imp.Materiaux {
			pdf.CellFormat(100, 8, tr(m.Type), "1", 0, "L", false, 0, "")
			pdf.CellFormat(40, 8, strconv.Itoa(m.Nombre), "1", 0, "R", false, 0, "")
			pdf.CellFormat(40, 8, fmt.Sprintf("%.1f kg", m.PoidsKg), "1", 1, "R", false, 0, "")
		}
	}
	pdf.Ln(10)
	pdf.SetFont("Arial", "I", 9)
	pdf.MultiCell(0, 5, tr("Le CO2 evite est une estimation indicative (facteur applique au poids valorise), non une mesure certifiee."), "", "L", false)

	w.Header().Set("Content-Type", "application/pdf")
	w.Header().Set("Content-Disposition", "inline; filename=bilan-impact.pdf")
	if err := pdf.Output(w); err != nil {
		httpx.JSONServerError(w, err)
	}
}
