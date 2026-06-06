package handlers

import (
	"database/sql"
	"errors"
	"fmt"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"

	"github.com/jung-kurt/gofpdf"
)

type factureDoc struct {
	id           int
	numero       string
	dateEmission string
	montantHT    float64
	tva          float64
	montantTTC   float64
	typ          string
	statut       string
	nom          string
	prenom       string
	idUser       int
}

func chargerFacture(id int) (factureDoc, error) {
	var f factureDoc
	err := database.DB.QueryRow(
		`SELECT f.Id_Facture, COALESCE(f.Numero_facture,''), COALESCE(f.Date_emission,''),
			COALESCE(f.Montant_HT,0), COALESCE(f.TVA,0), COALESCE(f.Montant_TTC,0),
			COALESCE(f.Type,''), COALESCE(f.Statut,''), f.Id_Utilisateurs,
			COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Factures f
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		WHERE f.Id_Facture = ?`, id,
	).Scan(&f.id, &f.numero, &f.dateEmission, &f.montantHT, &f.tva, &f.montantTTC,
		&f.typ, &f.statut, &f.idUser, &f.nom, &f.prenom)
	return f, err
}

func factureAccessible(r *http.Request, f factureDoc) bool {
	return middleware.GetRole(r) == "admin" || f.idUser == middleware.GetUserID(r)
}

func cheminFacturePDF(id int) string {
	return filepath.Join("storage", "factures", strconv.Itoa(id)+".pdf")
}

func ecrireFacturePDF(f factureDoc) error {
	path := cheminFacturePDF(f.id)
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		return err
	}
	pdf := gofpdf.New("P", "mm", "A4", "")
	tr := pdf.UnicodeTranslatorFromDescriptor("")
	pdf.AddPage()

	pdf.SetFont("Arial", "B", 20)
	pdf.Cell(0, 12, "UpcycleConnect")
	pdf.Ln(14)
	pdf.SetFont("Arial", "B", 16)
	pdf.Cell(0, 10, tr("Facture "+f.numero))
	pdf.Ln(12)

	pdf.SetFont("Arial", "", 11)
	pdf.Cell(0, 8, tr("Client : "+f.prenom+" "+f.nom))
	pdf.Ln(7)
	pdf.Cell(0, 8, tr("Date d'emission : "+f.dateEmission))
	pdf.Ln(7)
	pdf.Cell(0, 8, tr("Type : "+f.typ))
	pdf.Ln(7)
	pdf.Cell(0, 8, tr("Statut : "+f.statut))
	pdf.Ln(12)

	pdf.SetFont("Arial", "B", 11)
	pdf.CellFormat(120, 8, tr("Designation"), "1", 0, "L", false, 0, "")
	pdf.CellFormat(60, 8, "Montant", "1", 1, "R", false, 0, "")
	pdf.SetFont("Arial", "", 11)
	pdf.CellFormat(120, 8, "Montant HT", "1", 0, "L", false, 0, "")
	pdf.CellFormat(60, 8, fmt.Sprintf("%.2f EUR", f.montantHT), "1", 1, "R", false, 0, "")
	pdf.CellFormat(120, 8, fmt.Sprintf("TVA (%.0f%%)", f.tva), "1", 0, "L", false, 0, "")
	pdf.CellFormat(60, 8, fmt.Sprintf("%.2f EUR", f.montantTTC-f.montantHT), "1", 1, "R", false, 0, "")
	pdf.SetFont("Arial", "B", 11)
	pdf.CellFormat(120, 8, "Total TTC", "1", 0, "L", false, 0, "")
	pdf.CellFormat(60, 8, fmt.Sprintf("%.2f EUR", f.montantTTC), "1", 1, "R", false, 0, "")
	pdf.Ln(16)

	pdf.SetFont("Arial", "I", 9)
	pdf.Cell(0, 6, tr("Document genere le "+time.Now().Format("02/01/2006")))
	return pdf.OutputFileAndClose(path)
}

func GenerateFacturePDF(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	id, err := idDepuisChemin(r.URL.Path, "/api/factures/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	f, err := chargerFacture(id)
	if errors.Is(err, sql.ErrNoRows) {
		httpx.JSONError(w, http.StatusNotFound, "Facture introuvable")
		return
	} else if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	if !factureAccessible(r, f) {
		httpx.JSONError(w, http.StatusForbidden, "Accès refusé")
		return
	}
	if err := ecrireFacturePDF(f); err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	url := "/api/factures/" + strconv.Itoa(id) + "/pdf"
	database.DB.Exec("UPDATE Factures SET PDF_URL = ? WHERE Id_Facture = ?", url, id)
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"pdf_url": url})
}

func ServeFacturePDF(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/factures/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	f, err := chargerFacture(id)
	if errors.Is(err, sql.ErrNoRows) {
		httpx.JSONError(w, http.StatusNotFound, "Facture introuvable")
		return
	} else if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	if !factureAccessible(r, f) {
		httpx.JSONError(w, http.StatusForbidden, "Accès refusé")
		return
	}
	path := cheminFacturePDF(id)
	if _, statErr := os.Stat(path); statErr != nil {
		if err := ecrireFacturePDF(f); err != nil {
			httpx.JSONServerError(w, err)
			return
		}
	}
	w.Header().Set("Content-Type", "application/pdf")
	w.Header().Set("Content-Disposition", "inline; filename=facture-"+f.numero+".pdf")
	http.ServeFile(w, r, path)
}
