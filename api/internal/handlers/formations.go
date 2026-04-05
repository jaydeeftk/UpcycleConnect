package handlers

import (
    "encoding/json"
    "fmt"
    "net/http"
    "upcycleconnect/internal/database"
)

func GetFormations(w http.ResponseWriter, r *http.Request) {
    query := "SELECT Id_Formations, Titre, Description, Prix, Duree, Statut, COALESCE(Date_formation, ''), COALESCE(Places_total, 0), COALESCE(Places_dispo, 0), COALESCE(Localisation, ''), COALESCE(Categorie, '') FROM Formations WHERE 1=1"
    args := []interface{}{}

    categorie := r.URL.Query().Get("categorie")
    prixMax := r.URL.Query().Get("prix_max")
    date := r.URL.Query().Get("date")
    places := r.URL.Query().Get("places")
    tri := r.URL.Query().Get("tri")

    fmt.Println("TRI RECU:", tri)

    if categorie != "" {
        query += " AND Categorie = ?"
        args = append(args, categorie)
    }
    if prixMax != "" {
        query += " AND Prix <= ?"
        args = append(args, prixMax)
    }
    if date != "" {
        query += " AND Date_formation >= ?"
        args = append(args, date)
    }
    if places != "" {
        query += fmt.Sprintf(" AND Places_dispo >= %s", places)
    }

    switch tri {
    case "prix_asc":
        query += " ORDER BY Prix ASC"
    case "prix_desc":
        query += " ORDER BY Prix DESC"
    case "places":
        query += " ORDER BY Places_dispo DESC"
    default:
        query += " ORDER BY Date_formation ASC"
    }

    fmt.Println("QUERY:", query)

    rows, err := database.DB.Query(query, args...)
    if err != nil {
        http.Error(w, err.Error(), http.StatusInternalServerError)
        return
    }
    defer rows.Close()

    var formations []map[string]interface{}
    for rows.Next() {
        var id, duree, placesTotal, placesDispo int
        var titre, description, statut, dateF, localisation, categorie string
        var prix float64
        if err := rows.Scan(&id, &titre, &description, &prix, &duree, &statut, &dateF, &placesTotal, &placesDispo, &localisation, &categorie); err != nil {
            http.Error(w, err.Error(), http.StatusInternalServerError)
            return
        }
        formations = append(formations, map[string]interface{}{
            "id":           id,
            "titre":        titre,
            "description":  description,
            "prix":         prix,
            "duree":        duree,
            "statut":       statut,
            "date":         dateF,
            "places_total": placesTotal,
            "places_dispo": placesDispo,
            "localisation": localisation,
            "categorie":    categorie,
        })
    }

    w.Header().Set("Content-Type", "application/json")
    json.NewEncoder(w).Encode(formations)
}