package routes

import (
	"net/http"
	"upcycleconnect/internal/handlers"
	"upcycleconnect/internal/middleware"
)

func NewRouter() http.Handler {
	mux := http.NewServeMux()

	mux.HandleFunc("/api/health", handlers.Health)
	mux.HandleFunc("/api/auth/login", handlers.Login)
	mux.HandleFunc("/api/auth/register", handlers.Register)
	mux.HandleFunc("/api/auth/tutoriel", handlers.UpdateTutoriel)

	mux.HandleFunc("/api/services", handlers.GetServices)
	mux.HandleFunc("/api/formations", handlers.GetFormations)
	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)
	mux.HandleFunc("/api/annonces", handlers.GetAnnonces)
	mux.HandleFunc("/api/annonces/create", handlers.CreateAnnonce)
	mux.HandleFunc("/api/annonces/user/", handlers.GetAnnoncesUser)
	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/conteneurs/demandes", handlers.CreateDemandeConteneur)
	mux.HandleFunc("/api/conteneurs/user/", handlers.GetDemandesConteneurUser)

	mux.HandleFunc("/api/paiements/", middleware.JWTAuth(handlers.GetPaiements))
	mux.HandleFunc("/api/demandes/", middleware.JWTAuth(handlers.GetDemandes))

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
	mux.HandleFunc("/api/admin/categories", middleware.AdminOnly(handlers.AdminGetCategories))
	mux.HandleFunc("/api/admin/categories/", middleware.AdminOnly(handlers.AdminDeleteCategorie))
	mux.HandleFunc("/api/admin/messages", middleware.AdminOnly(handlers.AdminGetMessages))

	mux.HandleFunc("/api/conseils", handlers.GetConseils)
	mux.HandleFunc("/api/conseils/", handlers.GetConseil)
	mux.HandleFunc("/api/forum/sujets", handlers.ForumSujetsHandler)
	mux.HandleFunc("/api/forum/sujets/", handlers.ForumSujetDispatch)

	return mux
}
