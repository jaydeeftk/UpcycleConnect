package handlers

import (
	"encoding/json"
	"net/http"
	"os"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"upcycleconnect/internal/httpx"
)

func CreateCheckoutSession(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}

	stripe.Key = os.Getenv("STRIPE_SECRET_KEY")

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
		SuccessURL: stripe.String("http://localhost/paiement/success?type=" + body.Type + "&id=" + string(rune(body.IdItem))),
		CancelURL:  stripe.String("http://localhost/catalogue/" + body.Type + "s"),
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