package handlers

import (
	"encoding/json"
	"io"
	"math"
	"net/http"
	"os"
	"strconv"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"github.com/stripe/stripe-go/v76/webhook"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
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

	userID := middleware.GetUserID(r)
	if userID == 0 {
		httpx.JSONError(w, http.StatusUnauthorized, "Authentification requise")
		return
	}

	var body struct {
		Type   string `json:"type"`
		IdItem int    `json:"id_item"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}

	checkout, err := facturationSvc.PreparerCheckout(body.Type, body.IdItem)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}

	appURL := os.Getenv("APP_URL")
	if appURL == "" {
		appURL = "https://upcycleconnect.tech"
	}
	userStr := strconv.Itoa(userID)
	itemStr := strconv.Itoa(body.IdItem)

	params := &stripe.CheckoutSessionParams{
		PaymentMethodTypes: stripe.StringSlice([]string{"card"}),
		ClientReferenceID:  stripe.String(userStr),
		LineItems: []*stripe.CheckoutSessionLineItemParams{
			{
				PriceData: &stripe.CheckoutSessionLineItemPriceDataParams{
					Currency: stripe.String("eur"),
					ProductData: &stripe.CheckoutSessionLineItemPriceDataProductDataParams{
						Name: stripe.String(checkout.Titre),
					},
					UnitAmount: stripe.Int64(int64(math.Round(checkout.Montant * 100))),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?session_id={CHECKOUT_SESSION_ID}"),
		CancelURL:  stripe.String(appURL + "/catalogue/" + body.Type + "s"),
	}
	params.AddMetadata("type", body.Type)
	params.AddMetadata("item_id", itemStr)
	params.AddMetadata("user_id", userStr)

	s, err := session.New(params)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}

	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"checkout_url": s.URL})
}

// PaiementSuccess est purement cosmétique : la page de retour Stripe LIT la
// commande déjà persistée par le webhook (source de vérité). Elle ne déclenche
// aucune écriture — si le client ferme l'onglet, le webhook persiste quand même.
func PaiementSuccess(w http.ResponseWriter, r *http.Request) {
	sessionID := r.URL.Query().Get("session_id")
	if sessionID == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Session de paiement manquante")
		return
	}
	commande, err := facturationSvc.CommandeParReference(sessionID)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, commande)
}

// StripeWebhook est l'unique source de vérité de la persistance d'un paiement.
// Stripe appelle cet endpoint sur checkout.session.completed : la signature est
// vérifiée, puis la commande est enregistrée de façon idempotente et atomique
// (Facture + Ligne_Facture + Paiement + Historique dans une seule transaction).
func StripeWebhook(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	secret := os.Getenv("STRIPE_WEBHOOK_SECRET")
	if secret == "" {
		httpx.JSONError(w, http.StatusInternalServerError, "Webhook Stripe non configuré")
		return
	}
	payload, err := io.ReadAll(http.MaxBytesReader(w, r.Body, 65536))
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Corps de requête illisible")
		return
	}
	event, err := webhook.ConstructEventWithOptions(payload, r.Header.Get("Stripe-Signature"), secret,
		webhook.ConstructEventOptions{IgnoreAPIVersionMismatch: true})
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Signature webhook invalide")
		return
	}
	if event.Type == "checkout.session.completed" {
		var s stripe.CheckoutSession
		if err := json.Unmarshal(event.Data.Raw, &s); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données de session illisibles")
			return
		}
		if s.PaymentStatus == stripe.CheckoutSessionPaymentStatusPaid {
			typ := s.Metadata["type"]
			itemID, _ := strconv.Atoi(s.Metadata["item_id"])
			userID, _ := strconv.Atoi(s.Metadata["user_id"])
			if userID == 0 {
				userID, _ = strconv.Atoi(s.ClientReferenceID)
			}
			if userID != 0 && itemID != 0 && typ != "" {
				if err := facturationSvc.EnregistrerPaiementItem(userID, typ, itemID, s.ID); err != nil {
					httpx.JSONServerError(w, err)
					return
				}
			}
		}
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"received": true})
}

func GetPaiementsUser(w http.ResponseWriter, r *http.Request) {
	id, err := idDepuisChemin(r.URL.Path, "/api/paiements/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}
	liste, err := facturationSvc.PaiementsDeLUtilisateur(id)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}
