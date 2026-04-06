package routes

import (
	"net/http"

	"upcycleconnect/internal/handlers"
)

func NewRouter() http.Handler {

	mux := http.NewServeMux()

	mux.HandleFunc("/api/health", handlers.Health)

	mux.HandleFunc("/api/services", handlers.GetServices)
	mux.HandleFunc("/api/formations", handlers.GetFormations)
	

	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)

	mux.HandleFunc("/api/auth/login", handlers.Login)
	mux.HandleFunc("/api/auth/register", handlers.Register)
	mux.HandleFunc("/api/auth/tutoriel", handlers.UpdateTutoriel)

	mux.HandleFunc("/api/paiements/", handlers.GetPaiements)

	mux.HandleFunc("/api/demandes/", handlers.GetDemandes)
	mux.HandleFunc("/api/demandes/create", handlers.CreateDemande)

	mux.HandleFunc("/api/admin/dashboard", handlers.AdminDashboard)
	mux.HandleFunc("/api/admin/utilisateurs", handlers.AdminGetUtilisateurs)
	mux.HandleFunc("/api/admin/utilisateurs/", handlers.AdminUtilisateurAction)
	mux.HandleFunc("/api/admin/evenements", handlers.AdminGetEvenements)
	mux.HandleFunc("/api/admin/evenements/", handlers.AdminCreateEvenement)
	mux.HandleFunc("/api/admin/messages", handlers.AdminGetMessages)
	mux.HandleFunc("/api/annonces", handlers.GetAnnonces)
	mux.HandleFunc("/api/annonces/create", handlers.CreateAnnonce)
	mux.HandleFunc("/api/annonces/user/", handlers.GetAnnoncesUser)
	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/conteneurs/demandes", handlers.CreateDemandeConteneur)
	mux.HandleFunc("/api/conteneurs/user/", handlers.GetDemandesConteneurUser)
	mux.HandleFunc("/api/admin/categories", handlers.AdminGetCategories)
	mux.HandleFunc("/api/admin/categories/", handlers.AdminDeleteCategorie)

	mux.HandleFunc("/api/admin/utilisateurs/delete/", handlers.AdminDeleteUtilisateur)

	return mux
}