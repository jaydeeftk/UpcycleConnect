package routes

import (
	"net/http"
	"strings"
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
	mux.HandleFunc("/api/auth/verify", handlers.VerifyPassword)
	mux.HandleFunc("/api/ws", handlers.ServeWS)

	mux.HandleFunc("/api/services", handlers.GetServices)
	mux.HandleFunc("/api/services/", handlers.GetService)
	mux.HandleFunc("/api/formations", handlers.GetFormations)
	mux.HandleFunc("/api/formations/", handlers.GetFormation)
	mux.HandleFunc("/api/evenements", handlers.GetEvenements)
	mux.HandleFunc("/api/evenements/", handlers.GetEvenement)
	mux.HandleFunc("/api/annonces", handlers.GetAnnonces)
	mux.HandleFunc("/api/annonces/", handlers.GetAnnonceDispatch)

	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/messages", handlers.GetMessages)
	mux.HandleFunc("/api/messages/upload", handlers.UploadMessageAttachment)
	mux.HandleFunc("/api/conteneurs/demandes", handlers.CreateDemandeConteneur)
	mux.HandleFunc("/api/conteneurs/user/", handlers.GetDemandesConteneurUser)

	mux.HandleFunc("/api/conseils", handlers.GetConseils)
	mux.HandleFunc("/api/conseils/", handlers.GetConseil)
	mux.HandleFunc("/api/forum/sujets", handlers.ForumSujetsHandler)
	mux.HandleFunc("/api/forum/sujets/", handlers.ForumSujetDispatch)

	mux.HandleFunc("/api/demandes/", middleware.JWTAuth(handlers.GetDemandes))
	mux.HandleFunc("/api/demandes/create", middleware.JWTAuth(handlers.CreateDemande))

	mux.HandleFunc("/api/score/", handlers.GetScore)
	mux.HandleFunc("/api/planning/", handlers.GetPlanning)
	mux.HandleFunc("/api/historique/", handlers.GetHistorique)
	mux.HandleFunc("/api/paiements/checkout", handlers.CreateCheckoutSession)
	mux.HandleFunc("/api/paiements/success", handlers.PaiementSuccess)
	mux.HandleFunc("/api/paiements/", handlers.GetPaiementsUser)

	mux.HandleFunc("/api/parametres", handlers.AdminGetParametres)

	mux.HandleFunc("/api/admin/dashboard", middleware.AdminOnly(handlers.AdminDashboard))
	mux.HandleFunc("/api/admin/utilisateurs", middleware.AdminOnly(handlers.AdminGetUtilisateurs))
	mux.HandleFunc("/api/admin/utilisateurs/", middleware.AdminOnly(handlers.AdminUtilisateurAction))
	mux.HandleFunc("/api/admin/annonces", middleware.AdminOnly(handlers.AdminGetAnnonces))
	mux.HandleFunc("/api/admin/annonces/", middleware.AdminOnly(handlers.AdminAnnonceAction))

	mux.HandleFunc("/api/admin/evenements", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateEvenement(w, r)
		} else {
			handlers.AdminGetEvenements(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/evenements/", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodDelete {
			handlers.AdminDeleteEvenement(w, r)
		} else {
			handlers.AdminCreateEvenement(w, r)
		}
	}))

	mux.HandleFunc("/api/admin/formations", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminFormationAction(w, r)
		} else {
			handlers.AdminGetFormations(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/formations/", middleware.AdminOnly(handlers.AdminFormationAction))

	mux.HandleFunc("/api/admin/conteneurs", middleware.AdminOnly(handlers.AdminGetConteneurs))
	mux.HandleFunc("/api/admin/conteneurs/demandes/", middleware.AdminOnly(handlers.AdminDemandeConteneurAction))
	mux.HandleFunc("/api/admin/conteneurs/", middleware.AdminOnly(handlers.AdminConteneurAction))

	mux.HandleFunc("/api/admin/categories", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateCategorie(w, r)
		} else {
			handlers.AdminGetCategories(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/categories/", middleware.AdminOnly(handlers.AdminDeleteCategorie))

	mux.HandleFunc("/api/admin/parametres", middleware.AdminOnly(handlers.AdminGetParametres))
	mux.HandleFunc("/api/admin/parametres/", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPut {
			handlers.AdminUpdateParametres(w, r)
		} else {
			handlers.AdminGetParametres(w, r)
		}
	}))

	mux.HandleFunc("/api/admin/notifications", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminNotificationAction(w, r)
		} else {
			handlers.AdminGetNotifications(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/notifications/", middleware.AdminOnly(handlers.AdminNotificationAction))

	mux.HandleFunc("/api/admin/contrats", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateContrat(w, r)
		} else {
			handlers.AdminGetContrats(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/contrats/", middleware.AdminOnly(handlers.AdminContratAction))

	mux.HandleFunc("/api/admin/abonnements", middleware.AdminOnly(handlers.AdminGetAbonnements))
	mux.HandleFunc("/api/admin/abonnements/", middleware.AdminOnly(handlers.AdminAbonnementAction))

	mux.HandleFunc("/api/admin/factures", middleware.AdminOnly(handlers.AdminGetFactures))
	mux.HandleFunc("/api/admin/factures/", middleware.AdminOnly(handlers.AdminGetFacture))

	mux.HandleFunc("/api/admin/messages", middleware.AdminOnly(handlers.AdminGetMessages))
	mux.HandleFunc("/api/admin/messages/", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminReplyMessage(w, r)
		} else {
			handlers.AdminGetMessages(w, r)
		}
	}))

	mux.HandleFunc("/api/admin/demandes", middleware.AdminOnly(handlers.AdminGetDemandes))
	mux.HandleFunc("/api/admin/demandes/", middleware.AdminOnly(handlers.AdminDemandeAction))

	mux.HandleFunc("/api/admin/finances", middleware.AdminOnly(handlers.AdminGetFinances))

	mux.HandleFunc("/api/admin/services", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateService(w, r)
		} else {
			handlers.AdminGetServices(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/services/", middleware.AdminOnly(handlers.AdminServiceAction))

	mux.HandleFunc("/api/admin/conseils", middleware.AdminOnly(handlers.AdminGetConseils))
	mux.HandleFunc("/api/admin/conseils/", middleware.AdminOnly(handlers.AdminConseilAction))

	mux.HandleFunc("/api/admin/forum", middleware.AdminOnly(handlers.AdminGetForumSujets))
	mux.HandleFunc("/api/admin/forum/sujets/", middleware.AdminOnly(handlers.AdminForumSujetAction))
	mux.HandleFunc("/api/admin/forum/reponses/", middleware.AdminOnly(handlers.AdminForumReponseAction))

	mux.HandleFunc("/api/admin/planning", middleware.AdminOnly(handlers.AdminGetPlanning))

	mux.HandleFunc("/api/admin/salaries/list", middleware.AdminOnly(handlers.AdminGetSalariesList))
	mux.HandleFunc("/api/messages/user/", handlers.GetUserMessages)

	mux.HandleFunc("/api/visites", handlers.RecordVisite)
	mux.HandleFunc("/api/admin/visites", middleware.AdminOnly(handlers.AdminGetVisites))

	mux.HandleFunc("/api/salaries/profil", middleware.SalarieOnly(handlers.SalarieGetProfile))
	mux.HandleFunc("/api/salaries/formations", middleware.SalarieOnly(handlers.SalarieFormationsHandler))
	mux.HandleFunc("/api/salaries/formations/", middleware.SalarieOnly(handlers.SalarieFormationAction))
	mux.HandleFunc("/api/salaries/conseils", middleware.SalarieOnly(handlers.SalarieConseils))
	mux.HandleFunc("/api/salaries/conseils/", middleware.SalarieOnly(handlers.SalarieConseilAction))

	mux.HandleFunc("/api/professionnels/profil", middleware.ProfessionnelOnly(handlers.ProfessionnelGetProfile))
	mux.HandleFunc("/api/professionnels/projets", middleware.ProfessionnelOnly(handlers.ProfessionnelProjetsHandler))
	mux.HandleFunc("/api/professionnels/projets/", middleware.ProfessionnelOnly(func(w http.ResponseWriter, r *http.Request) {
		path := strings.TrimPrefix(r.URL.Path, "/api/professionnels/projets/")
		if strings.Contains(path, "/etapes") {
			handlers.ProfessionnelEtapeAction(w, r)
		} else {
			handlers.ProfessionnelProjetAction(w, r)
		}
	}))
	mux.HandleFunc("/api/professionnels/favoris", middleware.ProfessionnelOnly(handlers.ProfessionnelFavorisHandler))
	mux.HandleFunc("/api/professionnels/favoris/", middleware.ProfessionnelOnly(handlers.ProfessionnelFavoriAction))
	mux.HandleFunc("/api/professionnels/contrats", middleware.ProfessionnelOnly(handlers.ProfessionnelGetContrats))

	return corsMiddleware(mux)
}