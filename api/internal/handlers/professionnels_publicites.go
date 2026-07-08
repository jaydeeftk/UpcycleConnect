package handlers

import (
	"encoding/json"
	"math"
	"net/http"
	"os"
	"strconv"

	"github.com/stripe/stripe-go/v76"
	"github.com/stripe/stripe-go/v76/checkout/session"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/httpx"
	"upcycleconnect/internal/services"
)

var publiciteSvc = services.NewPubliciteService()

func ProfessionnelPublicitesHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	liste, err := publiciteSvc.ListerPourPro(idPro)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func ProfessionnelPubliciteAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/professionnels/publicites/")
	if len(segs) != 2 || segs[1] != "annuler" {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	if err := publiciteSvc.AnnulerPourPro(idPro, segs[0]); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Campagne annulée"})
}

func ProfessionnelPubliciteCheckout(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	var body struct {
		Type        string  `json:"type"`
		Prix        float64 `json:"prix"`
		DateDebut   string  `json:"date_debut"`
		DateFin     string  `json:"date_fin"`
		Description string  `json:"description"`
		IdService   int     `json:"id_service"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	if err := publiciteSvc.ValiderAvantPaiement(services.PubliciteInput{
		Type: body.Type, Prix: body.Prix, DateDebut: body.DateDebut,
		DateFin: body.DateFin, Description: body.Description,
	}); err != nil {
		httpx.WriteError(w, err)
		return
	}
	if body.IdService <= 0 {
		httpx.JSONError(w, http.StatusBadRequest, "Sélectionnez la prestation à mettre en avant")
		return
	}
	idProService, err := serviceCatalogueSvc.IdProDuService(body.IdService)
	if err != nil || idProService != idPro {
		httpx.JSONError(w, http.StatusForbidden, "Cette prestation ne vous appartient pas")
		return
	}

	prixFacture := body.Prix
	if abo, errAbo := facturationSvc.ProAbonnementActuel(idPro); errAbo == nil && abo != nil && abo.Statut == domain.StatutAbonnementActif {
		prixFacture = domain.Round2(prixFacture * (1 - domain.ReductionPubPremium))
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
						Name: stripe.String("Campagne publicitaire — " + body.Type),
					},
					UnitAmount: stripe.Int64(int64(math.Round(prixFacture * 100))),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?session_id={CHECKOUT_SESSION_ID}"),
		CancelURL:  stripe.String(appURL + "/professionnel/publicites"),
	}
	params.AddMetadata("pro_action", "publicite")
	params.AddMetadata("id_pro", strconv.Itoa(idPro))
	params.AddMetadata("pub_type", body.Type)
	params.AddMetadata("pub_prix", strconv.FormatFloat(prixFacture, 'f', 2, 64))
	params.AddMetadata("pub_date_debut", body.DateDebut)
	params.AddMetadata("pub_date_fin", body.DateFin)
	params.AddMetadata("pub_id_service", strconv.Itoa(body.IdService))
	if len(body.Description) > 400 {
		body.Description = body.Description[:400]
	}
	params.AddMetadata("pub_description", body.Description)

	s, err := session.New(params)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"checkout_url": s.URL})
}

func AdminGetPublicites(w http.ResponseWriter, r *http.Request) {
	liste, err := publiciteSvc.ListerTout()
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func AdminPubliciteAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/admin/publicites/")
	if len(segs) != 2 || segs[1] != "annuler" {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	if err := publiciteSvc.Annuler(segs[0]); err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Campagne annulée"})
}
