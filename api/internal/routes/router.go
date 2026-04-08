package routes

import (
	"net/http"

	"upcycleconnect/internal/handlers"
	"upcycleconnect/internal/middleware"
)

func corsMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusOK)
			return
		}
		next.ServeHTTP(w, r)
	})
}

func NewRouter() http.Handler {
	mux := http.NewServeMux()

	mux.HandleFunc("/api/health", handlers.Health)
	mux.HandleFunc("/api/auth/login", handlers.Login)
	mux.HandleFunc("/api/auth/register", handlers.Register)
	mux.HandleFunc("/api/auth/tutoriel", handlers.UpdateTutoriel)
	mux.HandleFunc("/api/parametres", handlers.AdminGetParametres)

	mux.HandleFunc("/api/services", handlers.GetServices)
	mux.HandleFunc("/api/services/", handlers.GetService)
	mux.HandleFunc("/api/formations", handlers.GetFormations)
	mux.HandleFunc("/api/formations/", handlers.GetFormation)
	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)
	mux.HandleFunc("/api/annonces", handlers.GetAnnonces)
	mux.HandleFunc("/api/annonces/", handlers.GetAnnonceDispatch)

	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/conteneurs/demandes", handlers.CreateDemandeConteneur)
	mux.HandleFunc("/api/conteneurs/user/", handlers.GetDemandesConteneurUser)

	mux.HandleFunc("/api/conseils", handlers.GetConseils)
	mux.HandleFunc("/api/conseils/", handlers.GetConseil)
	mux.HandleFunc("/api/forum/sujets", handlers.ForumSujetsHandler)
	mux.HandleFunc("/api/forum/sujets/", handlers.ForumSujetDispatch)

	mux.HandleFunc("/api/demandes/", middleware.JWTAuth(handlers.GetDemandes))
	mux.HandleFunc("/api/demandes/create", middleware.JWTAuth(handlers.CreateDemande))

	mux.HandleFunc("/api/admin/dashboard", middleware.AdminOnly(handlers.AdminDashboard))

	mux.HandleFunc("/api/admin/utilisateurs", middleware.AdminOnly(handlers.AdminGetUtilisateurs))
	mux.HandleFunc("/api/admin/utilisateurs/", middleware.AdminOnly(handlers.AdminUtilisateurAction))

	mux.HandleFunc("/api/admin/annonces", middleware.AdminOnly(handlers.AdminGetAnnonces))
	mux.HandleFunc("/api/admin/annonces/", middleware.AdminOnly(handlers.AdminAnnonceAction))

	mux.HandleFunc("/api/admin/evenements", middleware.AdminOnly(handlers.AdminGetEvenements))
	mux.HandleFunc("/api/admin/evenements/", middleware.AdminOnly(handlers.AdminCreateEvenement))

	mux.HandleFunc("/api/admin/formations", middleware.AdminOnly(handlers.AdminGetFormations))
	mux.HandleFunc("/api/admin/formations/", middleware.AdminOnly(handlers.AdminFormationAction))

	mux.HandleFunc("/api/admin/conteneurs", middleware.AdminOnly(handlers.AdminGetConteneurs))
	mux.HandleFunc("/api/admin/conteneurs/", middleware.AdminOnly(handlers.AdminConteneurAction))

	mux.HandleFunc("/api/admin/categories/", middleware.AdminOnly(handlers.AdminDeleteCategorie))
	mux.HandleFunc("/api/admin/categories", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		switch r.Method {
		case http.MethodGet:
			handlers.AdminGetCategories(w, r)
		case http.MethodPost:
			handlers.AdminCreateCategorie(w, r)
		}
	}))

	mux.HandleFunc("/api/admin/parametres/", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.URL.Path != "/api/admin/parametres/" && r.URL.Path != "/api/admin/parametres" {
			http.NotFound(w, r)
			return
		}
		switch r.Method {
		case http.MethodGet:
			handlers.AdminGetParametres(w, r)
		case http.MethodPut:
			handlers.AdminUpdateParametres(w, r)
		default:
			http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		}
	}))

	mux.HandleFunc("/api/admin/notifications", middleware.AdminOnly(handlers.AdminGetNotifications))
	mux.HandleFunc("/api/admin/notifications/", middleware.AdminOnly(handlers.AdminNotificationAction))
	mux.HandleFunc("/api/admin/contrats", middleware.AdminOnly(handlers.AdminGetContrats))
	mux.HandleFunc("/api/admin/contrats/", middleware.AdminOnly(handlers.AdminContratAction))
	mux.HandleFunc("/api/admin/abonnements", middleware.AdminOnly(handlers.AdminGetAbonnements))
	mux.HandleFunc("/api/admin/abonnements/", middleware.AdminOnly(handlers.AdminAbonnementAction))
	mux.HandleFunc("/api/admin/factures", middleware.AdminOnly(handlers.AdminGetFactures))
	mux.HandleFunc("/api/admin/factures/", middleware.AdminOnly(handlers.AdminGetFacture))
	mux.HandleFunc("/api/admin/messages", middleware.AdminOnly(handlers.AdminGetMessages))

	mux.HandleFunc("/api/admin/demandes", middleware.AdminOnly(handlers.AdminGetDemandes))
	mux.HandleFunc("/api/admin/demandes/", middleware.AdminOnly(handlers.AdminDemandeAction))

	mux.HandleFunc("/api/score/", handlers.GetScore)
	mux.HandleFunc("/api/planning/", handlers.GetPlanning)
	mux.HandleFunc("/api/paiements/checkout", handlers.CreateCheckoutSession)
	mux.HandleFunc("/api/paiements/success", handlers.PaiementSuccess)

	return corsMiddleware(mux)
}