package handlers

import (
	"encoding/json"
	"math"
	"net/http"
	"os"
	"strconv"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/middleware"
)

// CreateCheckoutSession ouvre une session de paiement Stripe. L'IDENTITÉ vient du
// JWT (sub), jamais du corps : le client ne désigne QUE l'article (type + id). Le
// prix et l'intitulé sont recalculés en base (anti-falsification). L'utilisateur et
// l'article sont scellés dans les métadonnées de session, relus à la confirmation.
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

	// Montant (TTC) et intitulé déterminés SERVEUR. Un article gratuit -> 422.
	checkout, err := facturationSvc.PreparerCheckout(body.Type, body.IdItem)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}

	appURL := os.Getenv("APP_URL")
	if appURL == "" {
		appURL = "https://95.216.77.54.nip.io"
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

// PaiementSuccess rapproche un paiement après la redirection Stripe. L'endpoint
// n'est pas authentifié (le navigateur y arrive depuis Stripe), donc on ne fait
// confiance qu'à ce que Stripe scelle : on RÉCUPÈRE la session côté serveur,
// on EXIGE payment_status == "paid", puis on relit l'identité et l'article dans
// les métadonnées posées à la création (jamais dans l'URL côté client).
// L'enregistrement (facture + ligne + paiement) est idempotent sur l'id de
// session. Aucun chemin ne produit de 500 métier.
func PaiementSuccess(w http.ResponseWriter, r *http.Request) {
	sessionID := r.URL.Query().Get("session_id")
	if sessionID == "" {
		httpx.JSONError(w, http.StatusBadRequest, "Session de paiement manquante")
		return
	}

	stripe.Key = os.Getenv("STRIPE_SECRET_KEY")
	if stripe.Key == "" {
		httpx.JSONError(w, http.StatusInternalServerError, "Stripe non configuré")
		return
	}

	s, err := session.Get(sessionID, nil)
	if err != nil {
		httpx.JSONError(w, http.StatusBadGateway, "Session de paiement introuvable")
		return
	}
	if s.PaymentStatus != stripe.CheckoutSessionPaymentStatusPaid {
		httpx.JSONError(w, http.StatusPaymentRequired, "Paiement non confirmé")
		return
	}

	typ := s.Metadata["type"]
	itemID, _ := strconv.Atoi(s.Metadata["item_id"])
	userID, _ := strconv.Atoi(s.Metadata["user_id"])
	if userID == 0 {
		// Repli : compat sessions ne portant l'utilisateur que dans ClientReferenceID.
		userID, _ = strconv.Atoi(s.ClientReferenceID)
	}
	if userID == 0 || itemID == 0 || typ == "" {
		httpx.JSONError(w, http.StatusUnprocessableEntity, "Session de paiement incomplète")
		return
	}

	if err := facturationSvc.EnregistrerPaiementItem(userID, typ, itemID, s.ID); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Paiement confirmé"})
}

// GetPaiementsUser : historique des paiements d'un utilisateur. La route est
// gardée par OwnerFromPath (l'id final doit être celui de l'appelant, sauf admin) ;
// l'identifiant sert donc à cibler l'historique, l'autorisation étant déjà tranchée.
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
