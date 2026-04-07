package handlers

import (
	"encoding/json"
	"net/http"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func AdminGetParametres(w http.ResponseWriter, r *http.Request) {
	rows, err := database.DB.Query("SELECT Cle, Valeur FROM Parametres")
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur BDD: "+err.Error())
		return
	}
	defer rows.Close()

	parametres := make(map[string]string)
	for rows.Next() {
		var cle, valeur string
		if err := rows.Scan(&cle, &valeur); err == nil {
			parametres[cle] = valeur
		}
	}

	httpx.JSONOK(w, http.StatusOK, parametres)
}

func AdminUpdateParametres(w http.ResponseWriter, r *http.Request) {
	var body map[string]string

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	for cle, valeur := range body {
		_, err := database.DB.Exec("UPDATE Parametres SET Valeur = ? WHERE Cle = ?", valeur, cle)
		if err != nil {
			httpx.JSONError(w, http.StatusInternalServerError, "Erreur lors de la mise à jour de "+cle)
			return
		}
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Paramètres mis à jour"})
}
