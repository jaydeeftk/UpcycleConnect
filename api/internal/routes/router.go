package routes

import (
	"net/http"
	"os"
	"strings"
	"upcycleconnect/internal/handlers"
	"upcycleconnect/internal/middleware"
)

func corsMiddleware(next http.Handler) http.Handler {
	allowedOrigin := os.Getenv("APP_URL")
	if allowedOrigin == "" {
		allowedOrigin = "https://95.216.77.54.nip.io"
	}
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", allowedOrigin)
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
	mux.HandleFunc("/api/auth/tutoriel", middleware.JWTAuth(handlers.UpdateTutoriel))
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

	mux.HandleFunc("/api/stats", handlers.PublicStats)

	mux.HandleFunc("/api/conteneurs", handlers.GetConteneurs)
	mux.HandleFunc("/api/messages", middleware.JWTAuth(handlers.CreateUserMessage))
	mux.HandleFunc("/api/messages/upload", middleware.JWTAuth(handlers.UploadMessageAttachment))
	mux.HandleFunc("/api/conteneurs/demandes", middleware.JWTAuth(handlers.CreateDemandeConteneur))
	mux.HandleFunc("/api/conteneurs/user/", middleware.OwnerFromPath(handlers.GetDemandesConteneurUser))

	mux.HandleFunc("/api/conseils", handlers.GetConseils)
	mux.HandleFunc("/api/conseils/", handlers.GetConseil)

	mux.HandleFunc("/api/forum/sujets", middleware.OptionalJWT(handlers.ForumSujetsHandler))
	mux.HandleFunc("/api/forum/sujets/", middleware.OptionalJWT(handlers.ForumSujetDispatch))

	mux.HandleFunc("/api/score/", middleware.OwnerFromPath(handlers.GetScore))
	mux.HandleFunc("/api/planning/", middleware.OwnerFromPath(handlers.GetPlanning))
	mux.HandleFunc("/api/historique/", middleware.OwnerFromPath(handlers.GetHistorique))
	mux.HandleFunc("/api/paiements/checkout", middleware.JWTAuth(handlers.CreateCheckoutSession))
	mux.HandleFunc("/api/paiements/success", handlers.PaiementSuccess)
	mux.HandleFunc("/api/paiements/", middleware.OwnerFromPath(handlers.GetPaiementsUser))

	mux.HandleFunc("/api/factures/", middleware.JWTAuth(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.GenerateFacturePDF(w, r)
		} else {
			handlers.ServeFacturePDF(w, r)
		}
	}))

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

	mux.HandleFunc("/api/notifications", middleware.JWTAuth(handlers.MesNotifications))
	mux.HandleFunc("/api/notifications/", middleware.JWTAuth(handlers.MarquerNotificationLue))

	mux.HandleFunc("/api/admin/contrats", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateContrat(w, r)
		} else {
			handlers.AdminGetContrats(w, r)
		}
	}))
	mux.HandleFunc("/api/admin/contrats/", middleware.AdminOnly(handlers.AdminContratAction))

	mux.HandleFunc("/api/admin/abonnements", middleware.AdminOnly(func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodPost {
			handlers.AdminCreateAbonnement(w, r)
		} else {
			handlers.AdminGetAbonnements(w, r)
		}
	}))
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
	mux.HandleFunc("/api/messages/user/", middleware.OwnerFromPath(handlers.GetUserMessages))

	mux.HandleFunc("/api/visites", handlers.RecordVisite)
	mux.HandleFunc("/api/admin/visites", middleware.AdminOnly(handlers.AdminGetVisites))

	mux.HandleFunc("/api/salaries/profil", middleware.SalarieOnly(handlers.SalarieGetProfile))
	mux.HandleFunc("/api/salaries/formations", middleware.SalarieOnly(handlers.SalarieFormationsHandler))
	mux.HandleFunc("/api/salaries/formations/", middleware.SalarieOnly(handlers.SalarieFormationAction))
	mux.HandleFunc("/api/salaries/conseils", middleware.SalarieOnly(handlers.SalarieConseils))
	mux.HandleFunc("/api/salaries/conseils/", middleware.SalarieOnly(handlers.SalarieConseilAction))
	mux.HandleFunc("/api/salaries/forum", middleware.SalarieOnly(handlers.SalarieGetForumSujets))
	mux.HandleFunc("/api/salaries/forum/sujets/", middleware.SalarieOnly(handlers.SalarieForumSujetAction))
	mux.HandleFunc("/api/salaries/forum/reponses/", middleware.SalarieOnly(handlers.SalarieForumReponseAction))

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
	mux.HandleFunc("/api/professionnels/contrats/", middleware.ProfessionnelOnly(handlers.ProfessionnelContratAction))
	mux.HandleFunc("/api/professionnels/impact", middleware.ProfessionnelOnly(handlers.ProfessionnelImpact))
	mux.HandleFunc("/api/professionnels/impact/pdf", middleware.ProfessionnelOnly(handlers.ProfessionnelImpactPDF))

	mux.HandleFunc("/api/professionnels/objets", middleware.ProfessionnelOnly(handlers.ProfessionnelObjetsHandler))

	mux.HandleFunc("/api/professionnels/objets/recuperer-par-code", middleware.ProfessionnelOnly(handlers.ProfessionnelRecupererParCode))
	mux.HandleFunc("/api/professionnels/objets/", middleware.ProfessionnelOnly(handlers.ProfessionnelObjetAction))

	mux.HandleFunc("/api/salaries/evenements", middleware.SalarieOnly(handlers.GetEvenementsSalarie))
	mux.HandleFunc("/api/salaries/evenements/", middleware.SalarieOnly(handlers.EvenementSalarieAction))
	mux.HandleFunc("/api/salaries/ateliers", middleware.SalarieOnly(handlers.GetAteliers))
	mux.HandleFunc("/api/salaries/ateliers/", middleware.SalarieOnly(handlers.AtelierAction))

	mux.HandleFunc("/api/salaries/planning", middleware.SalarieOnly(handlers.GetEvenementsSalarie))

	mux.HandleFunc("/api/salaries/planning/evenement/", middleware.SalarieOnly(func(w http.ResponseWriter, r *http.Request) {
		path := strings.TrimPrefix(r.URL.Path, "/api/salaries/planning/evenement/")
		if r.Method == http.MethodPost && path == "create" {
			handlers.CreateEvenement(w, r)
			return
		}
		handlers.DeleteEvenement(w, r)
	}))

	mux.HandleFunc("/api/salaries/planning/formation/", middleware.SalarieOnly(func(w http.ResponseWriter, r *http.Request) {
		path := strings.TrimPrefix(r.URL.Path, "/api/salaries/planning/formation/")
		if r.Method == http.MethodPost && path == "create" {
			handlers.SalarieFormationsHandler(w, r)
			return
		}
		handlers.SalarieFormationAction(w, r)
	}))

	mux.HandleFunc("/api/salaries/planning/atelier/", middleware.SalarieOnly(func(w http.ResponseWriter, r *http.Request) {
		path := strings.TrimPrefix(r.URL.Path, "/api/salaries/planning/atelier/")
		if r.Method == http.MethodPost && path == "create" {
			handlers.CreateAtelier(w, r)
			return
		}
		handlers.DeleteAtelier(w, r)
	}))

	return corsMiddleware(mux)
}
