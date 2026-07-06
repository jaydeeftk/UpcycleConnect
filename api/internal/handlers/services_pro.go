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

type bodyServiceCatalogue struct {
	Titre       string  `json:"titre"`
	Description string  `json:"description"`
	Prix        float64 `json:"prix"`
	Duree       int     `json:"duree"`
	Categorie   string  `json:"categorie"`
}

func ServicesProHandler(w http.ResponseWriter, r *http.Request) {
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	switch r.Method {
	case http.MethodGet:
		liste, err := serviceCatalogueSvc.ListerPourPro(idPro)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)

	case http.MethodPost:
		var body bodyServiceCatalogue
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		id, err := serviceCatalogueSvc.Creer(idPro, body.Titre, body.Description, body.Prix, body.Duree, body.Categorie)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id": id, "message": "Prestation créée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func ServiceProAction(w http.ResponseWriter, r *http.Request) {
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	idService, err := idDepuisChemin(r.URL.Path, "/api/professionnels/services/")
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch r.Method {
	case http.MethodPut:
		var body bodyServiceCatalogue
		if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
			httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
			return
		}
		if err := serviceCatalogueSvc.Modifier(idPro, idService, body.Titre, body.Description, body.Prix, body.Duree, body.Categorie); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Prestation mise à jour"})

	case http.MethodDelete:
		if err := serviceCatalogueSvc.Supprimer(idPro, idService); err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"message": "Prestation supprimée"})

	default:
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
	}
}

func GetMesCommandesServices(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	liste, err := serviceCatalogueSvc.ListerCommandesUtilisateur(userID)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func GetCommandesServicesPro(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	_, idPro, ok := getProfessionnelFromContext(r)
	if !ok {
		httpx.JSONError(w, http.StatusForbidden, "Profil professionnel introuvable")
		return
	}
	liste, err := serviceCatalogueSvc.ListerCommandesPro(idPro)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, liste)
}

func CommanderService(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		httpx.JSONError(w, http.StatusMethodNotAllowed, "Méthode non autorisée")
		return
	}
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	var body struct {
		IdService        int    `json:"id_service"`
		NomObjet         string `json:"nom_objet"`
		DescriptionObjet string `json:"description_objet"`
		PhotoURL         string `json:"photo_url"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Données invalides")
		return
	}
	idCommande, _, _, err := serviceCatalogueSvc.CreerCommande(userID, body.IdService, body.NomObjet, body.DescriptionObjet, body.PhotoURL)
	if err != nil {
		httpx.WriteError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusCreated, map[string]interface{}{"id_commande": idCommande})
}

func CommandeServiceAction(w http.ResponseWriter, r *http.Request) {
	userID := middleware.GetUserID(r)
	if userID <= 0 {
		httpx.JSONError(w, http.StatusForbidden, "Non authentifié")
		return
	}
	segs := segmentsApres(r.URL.Path, "/api/services/commandes/")
	if len(segs) != 2 {
		httpx.JSONError(w, http.StatusBadRequest, "Chemin invalide")
		return
	}
	idCommande, err := strconv.Atoi(segs[0])
	if err != nil {
		httpx.JSONError(w, http.StatusBadRequest, "Identifiant invalide")
		return
	}

	switch {
	case segs[1] == "etapes" && r.Method == http.MethodGet:
		liste, err := projetSvc.ListerEtapesPourParticulierCommandeService(userID, idCommande)
		if err != nil {
			httpx.WriteError(w, err)
			return
		}
		httpx.JSONOK(w, http.StatusOK, liste)
		return

	case segs[1] == "checkout" && r.Method == http.MethodPost:
		checkoutCommandeServiceStripe(w, userID, idCommande)
		return

	default:
		httpx.JSONError(w, http.StatusNotFound, "Route non trouvée")
	}
}

func checkoutCommandeServiceStripe(w http.ResponseWriter, userID, idCommande int) {
	prix, titre, err := serviceCatalogueSvc.PreparerCheckout(userID, idCommande)
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
						Name: stripe.String("Prestation — " + titre),
					},
					UnitAmount: stripe.Int64(int64(math.Round(prix * 100))),
				},
				Quantity: stripe.Int64(1),
			},
		},
		Mode:       stripe.String(string(stripe.CheckoutSessionModePayment)),
		SuccessURL: stripe.String(appURL + "/paiement/success?session_id={CHECKOUT_SESSION_ID}"),
		CancelURL:  stripe.String(appURL + "/prestations"),
	}
	params.AddMetadata("pro_action", "achat_service")
	params.AddMetadata("id_commande_service", strconv.Itoa(idCommande))

	s, err := session.New(params)
	if err != nil {
		httpx.JSONServerError(w, err)
		return
	}
	httpx.JSONOK(w, http.StatusOK, map[string]interface{}{"checkout_url": s.URL})
}
