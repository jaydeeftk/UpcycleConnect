package handlers

import (
	"net/http"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetFactures(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT f.Id_Facture, COALESCE(f.Numero_facture,''), COALESCE(f.Date_emission,''),
			COALESCE(f.Montant_HT,0), COALESCE(f.TVA,0), COALESCE(f.Montant_TTC,0),
			COALESCE(f.Statut,''), COALESCE(f.Type,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Factures f
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		ORDER BY f.Date_emission DESC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()

	factures := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var numero, date, statut, typef, nom, prenom string
		var montantHT, tva, montantTTC float64
		rows.Scan(&id, &numero, &date, &montantHT, &tva, &montantTTC, &statut, &typef, &nom, &prenom)
		factures = append(factures, map[string]interface{}{
			"id": id, "numero": numero, "date_emission": date,
			"montant_ht": montantHT, "tva": tva, "montant_ttc": montantTTC,
			"statut": statut, "type": typef, "nom": nom, "prenom": prenom,
		})
	}
	httpx.JSONOK(w, http.StatusOK, factures)
}

func AdminGetFacture(w http.ResponseWriter, r *http.Request) {
	id := strings.TrimPrefix(r.URL.Path, "/api/admin/factures/")
	id = strings.Split(id, "/")[0]
	row := database.DB.QueryRow(
		`SELECT f.Id_Facture, COALESCE(f.Numero_facture,''), COALESCE(f.Date_emission,''),
			COALESCE(f.Montant_HT,0), COALESCE(f.TVA,0), COALESCE(f.Montant_TTC,0),
			COALESCE(f.Statut,''), COALESCE(f.Type,''),
			COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Factures f
		LEFT JOIN Utilisateurs u ON u.Id_Utilisateurs = f.Id_Utilisateurs
		WHERE f.Id_Facture=?`, id,
	)
	var fid int
	var numero, date, statut, typef, nom, prenom string
	var montantHT, tva, montantTTC float64
	if err := row.Scan(&fid, &numero, &date, &montantHT, &tva, &montantTTC, &statut, &typef, &nom, &prenom); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Facture introuvable")
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"id": fid, "numero": numero, "date_emission": date,
		"montant_ht": montantHT, "tva": tva, "montant_ttc": montantTTC,
		"statut": statut, "type": typef, "nom": nom, "prenom": prenom,
	})
}
