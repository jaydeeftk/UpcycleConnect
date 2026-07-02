package handlers

import (
	"math"
	"net/http"
	"os"
	"strconv"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

func ProfessionnelAbonnementHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	dto, err := facturationSvc.ProAbonnementActuel(idPro)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, dto)
}

func ProfessionnelAbonnementResilier(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	if err := facturationSvc.ProResilierAbonnement(idPro); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Abonnement résilié"})
}

// ProfessionnelAbonnementCheckout cree une session Stripe pour l'abonnement
// Premium (prix fixe). L'abonnement n'est reellement cree qu'a la confirmation
// du paiement, via le webhook Stripe (voir StripeWebhook).
func ProfessionnelAbonnementCheckout(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	actuel, err := facturationSvc.ProAbonnementActuel(idPro)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	if actuel != nil && (actuel.Statut == "actif" || actuel.Statut == "suspendu") {
		httpx.JSONError(w, http.StatusConflict, "Vous avez déjà un abonnement en cours")
		return
	}

	stripe.Key = os.Getenv("STRIPE_SECRET_KEY")
	if stripe.Key == "" {
		httpx.JSONError(w, http.StatusInternalServerError, "Stripe non configuré")
		return
	}
	appURL := os.Getenv("APP_URL")
	if appURL == "" {
		appURL = "https://upcycleconnect.tech"
	}

	params := &stripe.CheckoutSessionParams{
		PaymentMethodTypes: stripe.StringSlice([]string{"card"}),
		LineItems: []*stripe.CheckoutSessionLineItemParams{
			{
				PriceData: &stripe.CheckoutSessionLineItemPriceDataParams{
					Currency: stripe.String("eur"),
					ProductData: &stripe.CheckoutSessionLineItemPriceDataProductDataParams{
						Name: stripe.String("Abonnement UpcycleConnect Premium"),
					},
					UnitAmount: stripe.Int64(int64(math.Round(services.PrixAbonnementPremium * 100))),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?session_id={CHECKOUT_SESSION_ID}"),
		CancelURL:  stripe.String(appURL + "/professionnel/abonnement"),
	}
	params.AddMetadata("pro_action", "abonnement_premium")
	params.AddMetadata("id_pro", strconv.Itoa(idPro))

	s, err := session.New(params)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"checkout_url": s.URL})
}
