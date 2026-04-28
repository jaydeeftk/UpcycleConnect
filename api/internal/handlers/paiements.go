package handlers

import (
	"encoding/json"
	"fmt"
	"net/http"
	"os"
	"strings"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"upcycleconnect/internal/database"
	"upcycleconnect/internal/httpx"
)

func CreateCheckoutSession(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	stripe.Key = os.Getenv("STRIPE_SECRET_KEY")
	if stripe.Key == "" {
		httpx.JSONError(w, http.StatusInternalServerError, "Stripe non configuré")
		return
	}

	var body struct {
		IdUtilisateur int     `json:"id_utilisateur"`
		Type          string  `json:"type"`
		IdItem        int     `json:"id_item"`
		Montant       float64 `json:"montant"`
		Titre         string  `json:"titre"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	appURL := os.Getenv("APP_URL")
	if appURL == "" {
		appURL = "http://145.241.169.248"
	}

	idStr := fmt.Sprintf("%d", body.IdItem)

	params := &stripe.CheckoutSessionParams{
		PaymentMethodTypes: stripe.StringSlice([]string{"card"}),
		LineItems: []*stripe.CheckoutSessionLineItemParams{
			{
				PriceData: &stripe.CheckoutSessionLineItemPriceDataParams{
					Currency: stripe.String("eur"),
					ProductData: &stripe.CheckoutSessionLineItemPriceDataProductDataParams{
						Name: stripe.String(body.Titre),
					},
					UnitAmount: stripe.Int64(int64(body.Montant * 100)),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?type=" + body.Type + "&id=" + idStr),
		CancelURL:  stripe.String(appURL + "/catalogue/" + body.Type + "s"),
	}

	s, err := session.New(params)
	if err != nil {
		httpx.JSONError(w, http.StatusInternalServerError, "Erreur Stripe : "+err.Error())
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"checkout_url": s.URL,
	})
}

func PaiementSuccess(w http.ResponseWriter, r *http.Request) {
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{
		"message": "Paiement confirmé",
	})
}

func GetPaiementsUser(w http.ResponseWriter, r *http.Request) {
	parts := strings.Split(strings.Trim(r.URL.Path, "/"), "/")
	idUtilisateur := parts[len(parts)-1]

	rows, err := database.DB.Query(
		`SELECT p.Id_Paiements, COALESCE(p.Montant,0), COALESCE(p.Statut,''), COALESCE(p.Methode,''),
			COALESCE(p.Date_,''), COALESCE(f.Numero_facture,'')
		FROM Paiements p
		LEFT JOIN Factures f ON f.Id_Facture = p.Id_Facture
		WHERE p.Id_Utilisateurs = ?
		ORDER BY p.Id_Paiements DESC`, idUtilisateur,
	)
	if err != nil {
		httpx.JSONOK(w, http.StatusOK, []interface{}{})
		return
	}
	defer rows.Close()

	paiements := []map[string]interface{}{}
	for rows.Next() {
		var id int
		var montant float64
		var statut, methode, date, facture string
		rows.Scan(&id, &montant, &statut, &methode, &date, &facture)
		paiements = append(paiements, map[string]interface{}{
			"id": id, "montant": montant, "statut": statut,
			"methode": methode, "date": date, "facture": facture,
		})
	}
	httpx.JSONOK(w, http.StatusOK, paiements)
}