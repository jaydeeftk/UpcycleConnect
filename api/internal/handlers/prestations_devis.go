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
	"upcycleconnect/internal/services"
)

var devisSvc = services.NewDevisService()

func GetDemandesPrestationsOuvertes(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	liste, err := devisSvc.ListerDemandesOuvertesPourPro(idPro)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func DevisHandler(w http.ResponseWriter, r *http.Request) {
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
		IdDemande int     `json:"id_demande"`
		Prix      float64 `json:"prix"`
		Message   string  `json:"message"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	id, err := devisSvc.ProposerDevis(idPro, body.IdDemande, body.Prix, body.Message)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"id": id})
}

func DevisAction(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/prestations/devis/")
	if len(segs) != 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idDevis, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch segs[1] {
	case "retirer":
		_, idPro, ok := getProfessionnelFromContext(r)
		if !ok {
			httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
			return
		}
		if err := devisSvc.RetirerDevis(idPro, idDevis); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Devis retiré"})

	case "checkout":
		creerCheckoutDevis(w, r, idDevis)

	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}

func DemandesPrestationsAction(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/prestations/demandes/")
	if len(segs) != 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idDemande, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch {
	case segs[1] == "devis" && r.Method == http.MethodGet:
		liste, err := devisSvc.ListerDevisPourDemande(userID, idDemande)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)

	case segs[1] == "etapes" && r.Method == http.MethodGet:
		liste, err := projetSvc.ListerEtapesPourParticulier(userID, idDemande)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)

	case segs[1] == "annuler" && r.Method == http.MethodPost:
		if err := devisSvc.AnnulerDemande(userID, idDemande); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Demande annulée"})

	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}

func creerCheckoutDevis(w http.ResponseWriter, r *http.Request, idDevis int) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	prix, nomObjet, err := devisSvc.PreparerAcceptation(userID, idDevis)
	if err != nil {
		httpx.WriteError(w, err)
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
						Name: stripe.String("Prestation — " + nomObjet),
					},
					UnitAmount: stripe.Int64(int64(math.Round(prix * 100))),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?session_id={CHECKOUT_SESSION_ID}"),
		CancelURL:  stripe.String(appURL + "/mes-prestations"),
	}
	params.AddMetadata("pro_action", "devis_presta")
	params.AddMetadata("id_devis", strconv.Itoa(idDevis))
	params.AddMetadata("id_utilisateur", strconv.Itoa(userID))

	s, err := session.New(params)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"checkout_url": s.URL})
}
