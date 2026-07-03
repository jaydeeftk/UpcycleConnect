package handlers

import (
	"encoding/json"
	"net/http"
	"strconv"
	"strings"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

// tableEtId renvoie le nom de table et la colonne Id pour un type d'entite
// planifiable (formation/evenement/atelier). Restreint a une liste blanche pour
// eviter toute injection SQL via le type recu dans l'URL.
func tableEtId(typeParent string) (table, idCol string, ok bool) {
	switch typeParent {
	case "formation":
		return "Formations", "Id_Formations", true
	case "evenement":
		return "Evenements", "Id_Evenements", true
	case "atelier":
		return "Atelier", "Id_Atelier", true
	}
	return "", "", false
}

// GetSalariesListe renvoie la liste des salaries (id, nom, prenom), utilisee par
// le selecteur de delegation (choisir qui anime une formation/evenement/atelier).
func GetSalariesListe(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query(
		`SELECT s.Id_Salaries, COALESCE(u.Nom,''), COALESCE(u.Prenom,'')
		FROM Salaries s
		JOIN Utilisateurs u ON u.Id_Utilisateurs = s.Id_Utilisateurs
		ORDER BY u.Nom ASC, u.Prenom ASC`,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()
	liste := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var nom, prenom string
		if rows.Scan(&id, &nom, &prenom) == nil {
			liste = append(liste, map[string]interface{}{"id": id, "nom": nom, "prenom": prenom})
		}
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

// SalarieDeleguer reassigne l'animateur d'une formation/evenement/atelier.
// Seul le createur (Id_Salaries) peut deleguer ; l'animateur assigne ne peut
// pas a son tour redeleguer, pour eviter les chaines de delegation en cascade.
// Route : PUT /api/salaries/deleguer/{type}/{id}
func SalarieDeleguer(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPut {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, salarieID, ok := getSalarieFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil salarié introuvable")
		return
	}
	parts := strings.Split(strings.Trim(strings.TrimPrefix(r.URL.Path, "/api/salaries/deleguer/"), "/"), "/")
	if len(parts) != 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	typeParent := parts[0]
	idParent, err := strconv.Atoi(parts[1])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	table, idCol, ok := tableEtId(typeParent)
	if !ok {
		httpx.JSONError(w, http.StatusBadRequest, "Type invalide")
		return
	}

	var idCreateur int
	if err := database.DB.QueryRow(
		"SELECT Id_Salaries FROM "+table+" WHERE "+idCol+"=?", idParent,
	).Scan(&idCreateur); err != nil {
		httpx.JSONError(w, http.StatusNotFound, "Introuvable")
		return
	}
	if idCreateur != salarieID {
		httpx.JSONError(w, http.StatusForbidden, "Seul le créateur peut déléguer")
		return
	}

	var body struct {
		IdAnimateur int `json:"id_animateur"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	if body.IdAnimateur == 0 {
		database.DB.Exec("UPDATE "+table+" SET Id_Salarie_Animateur=NULL WHERE "+idCol+"=?", idParent)
	} else {
		database.DB.Exec("UPDATE "+table+" SET Id_Salarie_Animateur=? WHERE "+idCol+"=?", body.IdAnimateur, idParent)
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Délégation mise à jour"})
}
