package routes

import (
	"net/http"

	"upcycleconnect/internal/handlers"
)

func NewRouter() http.Handler {

	mux := http.NewServeMux()

	mux.HandleFunc("/api/health", handlers.Health)

	mux.HandleFunc("/api/services", handlers.GetServices)

	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)

	mux.HandleFunc("/api/auth/login", handlers.Login)
	mux.HandleFunc("/api/auth/register", handlers.Register)

	mux.HandleFunc("/api/paiements/", handlers.GetPaiements)

	mux.HandleFunc("/api/demandes/", handlers.GetDemandes)
	mux.HandleFunc("/api/demandes/create", handlers.CreateDemande)

	mux.HandleFunc("/api/admin/dashboard", handlers.AdminDashboard)
	mux.HandleFunc("/api/admin/utilisateurs", handlers.AdminGetUtilisateurs)
	mux.HandleFunc("/api/admin/utilisateurs/", handlers.AdminUtilisateurAction)
	mux.HandleFunc("/api/admin/evenements", handlers.AdminGetEvenements)
	mux.HandleFunc("/api/admin/evenements/", handlers.AdminCreateEvenement)
	mux.HandleFunc("/api/admin/messages", handlers.AdminGetMessages)
	mux.HandleFunc("/api/admin/annonces", handlers.AdminGetAnnonces)
	mux.HandleFunc("/api/admin/categories", handlers.AdminGetCategories)
	mux.HandleFunc("/api/admin/categories/", handlers.AdminDeleteCategorie)
	mux.HandleFunc("/api/admin/utilisateurs/delete/", handlers.AdminDeleteUtilisateur)

	mux.HandleFunc("/api/salaries/conseils/create", handlers.CreateConseil)
	mux.HandleFunc("/api/salaries/conseils/", handlers.ConseilAction)
	mux.HandleFunc("/api/salaries/conseils", handlers.GetConseils)

	mux.HandleFunc("/api/salaries/planning/evenement/create", handlers.CreateEvenementPlanning)
	mux.HandleFunc("/api/salaries/planning/formation/create", handlers.CreateFormationPlanning)
	mux.HandleFunc("/api/salaries/planning/atelier/create", handlers.CreateAtelierPlanning)
	mux.HandleFunc("/api/salaries/planning/evenement/", handlers.DeleteEvenementPlanning)
	mux.HandleFunc("/api/salaries/planning/formation/", handlers.DeleteFormationPlanning)
	mux.HandleFunc("/api/salaries/planning/atelier/", handlers.DeleteAtelierPlanning)
	mux.HandleFunc("/api/salaries/planning", handlers.GetPlanning)

	return mux
}
