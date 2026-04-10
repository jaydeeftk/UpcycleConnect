package routes

import (
	"net/http"
	"upcycleconnect/internal/handlers"
)

func NewRouter() http.Handler {
	mux := http.NewServeMux()

	// Health
	mux.HandleFunc("/api/health", handlers.Health)

	// Auth
	mux.HandleFunc("/api/auth/login", handlers.Login)
	mux.HandleFunc("/api/auth/register", handlers.Register)
	mux.HandleFunc("/api/auth/tutoriel", handlers.UpdateTutoriel)

	// Front — services, formations, evenements
	mux.HandleFunc("/api/services", handlers.GetServices)
	mux.HandleFunc("/api/formations", handlers.GetFormations)
	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)

	// Front — annonces
	mux.HandleFunc("/api/annonces", handlers.GetAnnonces)
	mux.HandleFunc("/api/annonces/create", handlers.CreateAnnonce)
	mux.HandleFunc("/api/annonces/user/", handlers.GetAnnoncesUser)

	// Front — conteneurs
	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/conteneurs/demandes", handlers.CreateDemandeConteneur)
	mux.HandleFunc("/api/conteneurs/user/", handlers.GetDemandesConteneurUser)

	// Front — conseils + forum
	mux.HandleFunc("/api/conseils", handlers.GetConseils)
	mux.HandleFunc("/api/conseils/", handlers.GetConseil)
	mux.HandleFunc("/api/forum/sujets", handlers.ForumSujetsHandler)
	mux.HandleFunc("/api/forum/sujets/", handlers.ForumSujetDispatch)

	// Front — paiements, demandes, score, planning
	mux.HandleFunc("/api/paiements/", handlers.GetPaiements)
	mux.HandleFunc("/api/demandes/create", handlers.CreateDemande)
	mux.HandleFunc("/api/demandes/", handlers.GetDemandes)
	mux.HandleFunc("/api/score/", handlers.GetScore)
	mux.HandleFunc("/api/planning/", handlers.GetPlanning)

	// Admin — dashboard + parametres
	mux.HandleFunc("/api/admin/dashboard", handlers.AdminDashboard)
	mux.HandleFunc("/api/admin/parametres", handlers.AdminGetParametres)

	// Admin — utilisateurs
	mux.HandleFunc("/api/admin/utilisateurs", handlers.AdminGetUtilisateurs)
	mux.HandleFunc("/api/admin/utilisateurs/", handlers.AdminUtilisateursDispatch)

	// Admin — annonces
	mux.HandleFunc("/api/admin/annonces", handlers.AdminGetAnnonces)
	mux.HandleFunc("/api/admin/annonces/", handlers.AdminAnnoncesDispatch)

	// Admin — evenements
	mux.HandleFunc("/api/admin/evenements", handlers.AdminGetEvenements)
	mux.HandleFunc("/api/admin/evenements/", handlers.AdminEvenementsDispatch)

	// Admin — messages
	mux.HandleFunc("/api/admin/messages", handlers.AdminGetMessages)

	// Admin — categories
	mux.HandleFunc("/api/admin/categories", handlers.AdminGetCategories)
	mux.HandleFunc("/api/admin/categories/", handlers.AdminDeleteCategorie)

	// Admin — conteneurs
	mux.HandleFunc("/api/admin/conteneurs/demandes", handlers.AdminGetDemandesConteneurs)
	mux.HandleFunc("/api/admin/conteneurs/demandes/", handlers.AdminDemandeConteneurAction)

	// Admin — formations
	mux.HandleFunc("/api/admin/formations", handlers.AdminGetFormations)
	mux.HandleFunc("/api/admin/formations/", handlers.AdminDeleteFormation)

	// Admin — contrats, factures
	mux.HandleFunc("/api/admin/contrats", handlers.AdminGetContrats)
	mux.HandleFunc("/api/admin/factures", handlers.AdminGetFactures)

	// Admin — notifications
	mux.HandleFunc("/api/admin/notifications/send", handlers.AdminSendNotification)
	mux.HandleFunc("/api/admin/notifications", handlers.AdminGetNotifications)

	return mux
}
