package services

import (
	"crypto/rand"
	"database/sql"
	"errors"
	"strconv"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ConteneurService struct {
	repo     repository.ConteneurRepo
	barcodes repository.CodeBarreRepo
}

func NewConteneurService() *ConteneurService { return &ConteneurService{} }

const nbTentativesCode = 6

func (s *ConteneurService) resoudreParticulier(q repository.Querier, idUtilisateur int) (int, error) {
	idPart, err := s.repo.IdParticulier(q, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Forbidden("Action réservée aux particuliers")
	}
	if err != nil {
		return 0, err
	}
	return idPart, nil
}

func (s *ConteneurService) resoudreAdministrateur(q repository.Querier, idUtilisateur int) (int, error) {
	idAdmin, err := s.repo.IdAdministrateur(q, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return 0, domain.Forbidden("Action réservée aux administrateurs")
	}
	if err != nil {
		return 0, err
	}
	return idAdmin, nil
}

type CreationDepotInput struct {
	TypeObjet   string
	Description string
	EtatUsure   string
	IdConteneur int
	DateDepot   string
	Destination string
	PrixVente   float64
	PhotoUrl    string
}

func (s *ConteneurService) CreerDemande(idUtilisateur int, in CreationDepotInput) (int64, error) {
	if err := domain.ValiderCreationDepot(in.TypeObjet, in.Destination, in.PrixVente, in.DateDepot); err != nil {
		return 0, err
	}
	if in.IdConteneur <= 0 {
		return 0, domain.Invalide("Un conteneur de dépôt doit être choisi")
	}

	var newID int64
	err := withTx(func(tx *sql.Tx) error {
		idPart, err := s.resoudreParticulier(tx, idUtilisateur)
		if err != nil {
			return err
		}
		statut, err := s.repo.ConteneurStatut(tx, in.IdConteneur)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Conteneur introuvable")
		}
		if err != nil {
			return err
		}
		if statut != domain.StatutConteneurDisponible {
			return domain.Conflit("Ce conteneur n'accepte pas de nouvelles demandes")
		}
		id, err := s.repo.CreerDemande(tx, repository.DemandeCreation{
			TypeObjet: in.TypeObjet, Description: in.Description, EtatUsure: in.EtatUsure,
			IdConteneur: in.IdConteneur, DateDepot: in.DateDepot, Destination: in.Destination,
			PrixVente: in.PrixVente, PhotoUrl: in.PhotoUrl, IdParticulier: idPart,
		})
		if err != nil {
			return err
		}
		newID = id
		return nil
	})
	return newID, err
}

func (s *ConteneurService) ValiderDemande(idDemande int) (string, error) {
	var code string
	err := withTxIso(sql.LevelReadCommitted, func(tx *sql.Tx) error {
		snap, err := s.repo.DemandePourMAJ(tx, idDemande)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Demande introuvable")
		}
		if err != nil {
			return err
		}
		if err := snap.PeutValider(); err != nil {
			return err
		}
		if snap.IdConteneur == 0 {
			return domain.Conflit("Aucun conteneur n'est associé à cette demande")
		}

		boxes, err := s.repo.BoxesDuConteneurPourMAJ(tx, snap.IdConteneur)
		if err != nil {
			return err
		}
		tailleRequise := domain.TailleObjetRequise(snap.Type)
		idBox, ok := domain.ChoisirBox(boxes, tailleRequise)
		if !ok {
			return domain.Complet("Conteneur plein : aucun UpcycleBox ne peut accueillir ce dépôt")
		}

		code, err = s.assignerCodeUnique(tx, idDemande, idBox)
		if err != nil {
			return err
		}
		idObjet, err := s.repo.CreerObjetEnStock(tx, repository.ObjetCreation{
			Type: snap.Type, IdConteneur: snap.IdConteneur,
			IdParticulier: snap.Proprietaire, IdBox: idBox, IdDemande: idDemande,
		})
		if err != nil {
			return err
		}

		return s.assignerCodeBarreUnique(tx, idObjet, idBox)
	})
	if err != nil {
		return "", err
	}
	return code, nil
}

func (s *ConteneurService) assignerCodeUnique(tx *sql.Tx, idDemande, idBox int) (string, error) {
	for i := 0; i < nbTentativesCode; i++ {
		code := genererCodeAcces()
		err := s.repo.AssignerCodeEtValider(tx, idDemande, code, idBox)
		if err == nil {
			return code, nil
		}
		if s.repo.EstViolationUnicite(err) {
			continue
		}
		return "", err
	}
	return "", domain.Conflit("Impossible de générer un code d'accès unique, réessayez")
}

func (s *ConteneurService) assignerCodeBarreUnique(tx *sql.Tx, idObjet, idBox int) error {
	for i := 0; i < nbTentativesCode; i++ {
		err := s.barcodes.Creer(tx, idObjet, genererCodeBarre(), idBox)
		if err == nil {
			return nil
		}
		if s.barcodes.EstViolationUnicite(err) {
			continue
		}
		return err
	}
	return domain.Conflit("Impossible de générer un code-barres unique, réessayez")
}

func (s *ConteneurService) RefuserDemande(idDemande int) error {
	return s.transitionDemande(idDemande, domain.DemandeSnapshot.PeutRefuser, domain.StatutDemandeRefusee)
}

func (s *ConteneurService) MarquerDeposee(idDemande int) error {
	return s.transitionDemande(idDemande, domain.DemandeSnapshot.PeutDeposer, domain.StatutDemandeDeposee)
}

func (s *ConteneurService) transitionDemande(
	idDemande int,
	garde func(domain.DemandeSnapshot) error,
	cible string,
) error {
	return withTx(func(tx *sql.Tx) error {
		snap, err := s.repo.DemandePourMAJ(tx, idDemande)
		if errors.Is(err, sql.ErrNoRows) {
			return domain.Introuvable("Demande introuvable")
		}
		if err != nil {
			return err
		}
		if err := garde(snap); err != nil {
			return err
		}
		return s.repo.MajStatutDemande(tx, idDemande, cible)
	})
}

type DemandeDTO struct {
	ID           int    `json:"id"`
	TypeObjet    string `json:"type_objet"`
	Description  string `json:"description"`
	EtatUsure    string `json:"etat_usure"`
	Statut       string `json:"statut"`
	CodeAcces    string `json:"code_acces"`
	Date         string `json:"date"`
	CodeBarre    string `json:"code_barre"`
	IdBox        int    `json:"id_box"`
	BoxReference string `json:"box_reference"`
	BoxTaille    string `json:"box_taille"`
}

func (s *ConteneurService) DemandesDeLUtilisateur(idUtilisateur int) ([]DemandeDTO, error) {
	idPart, err := s.repo.IdParticulier(database.DB, idUtilisateur)
	if errors.Is(err, sql.ErrNoRows) {
		return []DemandeDTO{}, nil
	}
	if err != nil {
		return nil, err
	}
	rows, err := s.repo.MesDemandes(database.DB, idPart)
	if err != nil {
		return nil, err
	}
	out := make([]DemandeDTO, 0, len(rows))
	for _, d := range rows {
		out = append(out, DemandeDTO{
			ID: d.ID, TypeObjet: d.TypeObjet, Description: d.Description,
			EtatUsure: d.EtatUsure, Statut: d.Statut, CodeAcces: d.CodeAcces, Date: d.Date,
			CodeBarre: d.CodeBarre, IdBox: d.IdBox, BoxReference: d.BoxReference, BoxTaille: d.BoxTaille,
		})
	}
	return out, nil
}

type DemandeAdminDTO struct {
	ID                int      `json:"id"`
	TypeObjet         string   `json:"type_objet"`
	Description       string   `json:"description"`
	EtatUsure         string   `json:"etat_usure"`
	Statut            string   `json:"statut"`
	Date              string   `json:"date"`
	PrixVente         float64  `json:"prix_vente"`
	Localisation      string   `json:"localisation"`
	CodeAcces         string   `json:"code_acces"`
	Nom               string   `json:"nom"`
	Prenom            string   `json:"prenom"`
	Email             string   `json:"email"`
	IdConteneur       int      `json:"id_conteneur"`
	ActionsAutorisees []string `json:"allowed_actions"`
}

func (s *ConteneurService) AdminListerDemandes() ([]DemandeAdminDTO, error) {
	rows, err := s.repo.AdminListerDemandes(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]DemandeAdminDTO, 0, len(rows))
	for _, d := range rows {
		out = append(out, DemandeAdminDTO{
			ID: d.ID, TypeObjet: d.TypeObjet, Description: d.Description, EtatUsure: d.EtatUsure,
			Statut: d.Statut, Date: d.Date, PrixVente: d.PrixVente, Localisation: d.Localisation,
			CodeAcces: d.CodeAcces, Nom: d.Nom, Prenom: d.Prenom, Email: d.Email,
			IdConteneur:       d.IdConteneur,
			ActionsAutorisees: domain.ActionsDemandeAdmin(d.Statut),
		})
	}
	return out, nil
}

type ConteneurPublicDTO struct {
	ID           int    `json:"id"`
	Localisation string `json:"localisation"`
	Capacite     int    `json:"capacite"`
	Statut       string `json:"statut"`
}

func (s *ConteneurService) ListerConteneursDisponibles() ([]ConteneurPublicDTO, error) {
	rows, err := s.repo.ListerConteneursDisponibles(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]ConteneurPublicDTO, 0, len(rows))
	for _, c := range rows {
		out = append(out, ConteneurPublicDTO{
			ID: c.ID, Localisation: c.Localisation, Capacite: c.Capacite, Statut: c.Statut,
		})
	}
	return out, nil
}

type BoxAdminDTO struct {
	ID         int      `json:"id"`
	Reference  string   `json:"reference"`
	Taille     string   `json:"taille"`
	Statut     string   `json:"statut"`
	HauteurCm  *float64 `json:"hauteur_cm"`
	LargeurCm  *float64 `json:"largeur_cm"`
	LongueurCm *float64 `json:"longueur_cm"`
}

type ConteneurAdminDTO struct {
	ID           int           `json:"id"`
	Localisation string        `json:"localisation"`
	Capacite     int           `json:"capacite"`
	Statut       string        `json:"statut"`
	NbDemandes   int           `json:"nb_demandes"`
	Occupation   int           `json:"occupation"`
	FillRate     int           `json:"fill_rate"`
	Boxes        []BoxAdminDTO `json:"boxes"`
}

func (s *ConteneurService) AdminListerConteneurs() ([]ConteneurAdminDTO, error) {
	rows, err := s.repo.AdminListerConteneurs(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]ConteneurAdminDTO, 0, len(rows))
	for _, c := range rows {
		boxes := make([]BoxAdminDTO, 0, len(c.Boxes))
		for _, b := range c.Boxes {
			boxes = append(boxes, BoxAdminDTO{
				ID: b.ID, Reference: b.Reference, Taille: b.Taille, Statut: b.Statut,
				HauteurCm: b.HauteurCm, LargeurCm: b.LargeurCm, LongueurCm: b.LongueurCm,
			})
		}
		out = append(out, ConteneurAdminDTO{
			ID: c.ID, Localisation: c.Localisation, Capacite: c.Capacite, Statut: c.Statut,
			NbDemandes: c.NbDemandes, Occupation: c.Occupation,
			FillRate: domain.TauxRemplissage(c.Occupation, c.CapaciteBox),
			Boxes:    boxes,
		})
	}
	return out, nil
}

func (s *ConteneurService) MettreAJourBoxDimensions(idBox int, hauteur, largeur, longueur *float64) error {
	return s.repo.MettreAJourBoxDimensions(database.DB, idBox, hauteur, largeur, longueur)
}

type ConteneurInput struct {
	Localisation string
	Capacite     int
	Statut       string
	Hauteur      float64
	Largeur      float64
	Longueur     float64
}

func (s *ConteneurService) CreerConteneur(idUtilisateur int, in ConteneurInput) (int64, error) {
	localisation := in.Localisation
	if localisation == "" {
		return 0, domain.Invalide("La localisation du conteneur est obligatoire")
	}
	if in.Capacite < 1 {
		return 0, domain.Invalide("La capacité doit être au moins 1")
	}
	statut := in.Statut
	if statut == "" {
		statut = domain.StatutConteneurDisponible
	}

	var newID int64
	err := withTx(func(tx *sql.Tx) error {
		idAdmin, err := s.resoudreAdministrateur(tx, idUtilisateur)
		if err != nil {
			return err
		}
		id, err := s.repo.CreerConteneur(tx, localisation, in.Capacite, statut, in.Hauteur, in.Largeur, in.Longueur, idAdmin)
		if err != nil {
			return err
		}
		newID = id
		reference := genererReferenceBox(id)
		return s.repo.CreerBox(tx, reference, in.Capacite, int(id))
	})
	return newID, err
}

func (s *ConteneurService) ModifierConteneur(idConteneur int, in ConteneurInput) error {
	if in.Localisation == "" {
		return domain.Invalide("La localisation du conteneur est obligatoire")
	}
	if in.Capacite < 1 {
		return domain.Invalide("La capacité doit être au moins 1")
	}
	statut := in.Statut
	if statut == "" {
		statut = domain.StatutConteneurDisponible
	}
	return withTx(func(tx *sql.Tx) error {
		n, err := s.repo.ModifierConteneur(tx, idConteneur, in.Localisation, in.Capacite, statut, in.Hauteur, in.Largeur, in.Longueur)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.Introuvable("Conteneur introuvable")
		}
		return s.repo.SyncBoxCapacite(tx, idConteneur, in.Capacite)
	})
}

func (s *ConteneurService) SupprimerConteneur(idConteneur int) error {
	return withTxIso(sql.LevelReadCommitted, func(tx *sql.Tx) error {
		if _, err := s.repo.ConteneurStatutPourMAJ(tx, idConteneur); err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				return domain.Introuvable("Conteneur introuvable")
			}
			return err
		}

		if _, err := s.repo.BoxesDuConteneurPourMAJ(tx, idConteneur); err != nil {
			return err
		}
		nbObjets, err := s.repo.CompterObjetsConteneur(tx, idConteneur)
		if err != nil {
			return err
		}
		if nbObjets > 0 {
			return domain.Conflit("Conteneur non vide : des objets y sont rattachés")
		}
		nbActives, err := s.repo.CompterDemandesActives(tx, idConteneur)
		if err != nil {
			return err
		}
		if nbActives > 0 {
			return domain.Conflit("Des demandes sont en cours sur ce conteneur")
		}
		if err := s.repo.SupprimerBoxesConteneur(tx, idConteneur); err != nil {
			return err
		}
		n, err := s.repo.SupprimerConteneur(tx, idConteneur)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.Introuvable("Conteneur introuvable")
		}
		return nil
	})
}

const alphabetCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"

func genererCodeAcces() string {
	b := make([]byte, 8)
	_, _ = rand.Read(b)
	out := make([]byte, 8)
	for i := range out {
		out[i] = alphabetCode[int(b[i])%len(alphabetCode)]
	}
	return "UC-" + string(out)
}

func genererCodeBarre() string {
	b := make([]byte, 12)
	_, _ = rand.Read(b)
	out := make([]byte, 12)
	for i := range out {
		out[i] = alphabetCode[int(b[i])%len(alphabetCode)]
	}
	return "UCB-" + string(out)
}

func genererReferenceBox(idConteneur int64) string {
	return "BOX-C" + strconv.FormatInt(idConteneur, 10)
}
