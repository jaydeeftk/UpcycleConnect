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

// ConteneurService porte les cas d'usage du vertical Demande / Conteneur / Box :
// dépôt d'une demande, modération (valider/refuser/déposer), et administration des
// conteneurs. L'IDENTITÉ vient toujours de l'utilisateur AUTHENTIFIÉ (jamais du
// corps/URL). Chaque transition verrouille l'agrégat (FOR UPDATE) et délègue la
// décision au domaine : état source + capacité vérifiés AVANT toute mutation,
// sinon erreur métier typée (403/409/422), jamais 500. L'occupation des box est
// DÉRIVÉE (comptage d'objets en_stock), jamais stockée.
type ConteneurService struct {
	repo repository.ConteneurRepo
}

func NewConteneurService() *ConteneurService { return &ConteneurService{} }

// nbTentativesCode : nombre d'essais de génération d'un Code_acces unique avant
// d'abandonner. Une collision sur 36^8 codes est quasi impossible ; la boucle
// existe pour ne JAMAIS renvoyer un 500 sur la contrainte uq_demande_code_acces.
const nbTentativesCode = 6

// resoudreParticulier mappe l'utilisateur authentifié vers son Id_Particuliers.
// Absence de ligne => compte non particulier : 403 métier.
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

// resoudreAdministrateur mappe l'utilisateur authentifié vers son
// Id_Administrateurs (NOT NULL exigé pour créer un conteneur). Le middleware garde
// déjà le rôle ; cette résolution est la défense en profondeur côté donnée.
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

// CreationDepotInput : intention de dépôt reçue du handler (sans identité ni
// statut — tous deux dérivés côté serveur).
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

// CreerDemande valide les invariants (type d'objet, cohérence destination↔prix),
// vérifie que le conteneur cible existe et accepte des dépôts (disponible), PUIS
// insère la demande sous l'identité du particulier authentifié, au statut
// en_attente (modération).
func (s *ConteneurService) CreerDemande(idUtilisateur int, in CreationDepotInput) (int64, error) {
	if err := domain.ValiderCreationDepot(in.TypeObjet, in.Destination, in.PrixVente); err != nil {
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

// ValiderDemande : transition ADMIN en_attente -> validee. C'est l'acte qui RÉSERVE
// physiquement une place : on choisit une box avec de la place dans le conteneur
// de la demande (occupation dérivée lue FOR UPDATE), on matérialise un objet
// 'en_stock' dans cette box, et on attribue un Code_acces unique. Sans place :
// 409. La transaction tourne en READ COMMITTED pour que la décision de capacité
// voie les insertions committées des validations concurrentes (anti sur-remplissage).
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
		idBox, ok := domain.ChoisirBox(boxes)
		if !ok {
			return domain.Complet("Conteneur plein : aucune box ne peut accueillir ce dépôt")
		}

		code, err = s.assignerCodeUnique(tx, idDemande)
		if err != nil {
			return err
		}
		return s.repo.CreerObjetEnStock(tx, repository.ObjetCreation{
			Type: snap.Type, IdConteneur: snap.IdConteneur,
			IdParticulier: snap.Proprietaire, IdBox: idBox,
		})
	})
	if err != nil {
		return "", err
	}
	return code, nil
}

// assignerCodeUnique génère un Code_acces et l'attribue, en regénérant en cas de
// collision sur la contrainte d'unicité (jamais de 500 sur uq_demande_code_acces).
func (s *ConteneurService) assignerCodeUnique(tx *sql.Tx, idDemande int) (string, error) {
	for i := 0; i < nbTentativesCode; i++ {
		code := genererCodeAcces()
		err := s.repo.AssignerCodeEtValider(tx, idDemande, code)
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

// RefuserDemande : transition ADMIN en_attente -> refusee (aucun objet n'a encore
// été matérialisé, rien à libérer).
func (s *ConteneurService) RefuserDemande(idDemande int) error {
	return s.transitionDemande(idDemande, domain.DemandeSnapshot.PeutRefuser, domain.StatutDemandeRefusee)
}

// MarquerDeposee : transition ADMIN validee -> deposee (le particulier a
// physiquement déposé l'objet ; l'objet reste en_stock).
func (s *ConteneurService) MarquerDeposee(idDemande int) error {
	return s.transitionDemande(idDemande, domain.DemandeSnapshot.PeutDeposer, domain.StatutDemandeDeposee)
}

// transitionDemande factorise les transitions de statut SANS effet de box : verrou
// FOR UPDATE, garde d'état du domaine, écriture.
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

// DemandeDTO : ligne de la file privée du propriétaire.
type DemandeDTO struct {
	ID          int    `json:"id"`
	TypeObjet   string `json:"type_objet"`
	Description string `json:"description"`
	EtatUsure   string `json:"etat_usure"`
	Statut      string `json:"statut"`
	CodeAcces   string `json:"code_acces"`
	Date        string `json:"date"`
}

// DemandesDeLUtilisateur : file privée de l'utilisateur (tous statuts). Un compte
// non particulier n'a simplement aucune demande -> liste vide (pas une erreur).
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
		})
	}
	return out, nil
}

// DemandeAdminDTO : ligne de modération + actions DÉRIVÉES côté serveur. Le front
// n'affiche que les boutons listés dans allowed_actions.
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
			ActionsAutorisees: domain.ActionsDemandeAdmin(d.Statut),
		})
	}
	return out, nil
}

// ConteneurPublicDTO : ligne de la liste publique (choix d'un point de dépôt).
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

// ConteneurAdminDTO : ligne back-office. FillRate est le taux de remplissage RÉEL,
// dérivé de l'occupation des box (objets en_stock) rapportée à leur capacité
// cumulée — fini le taux calculé à partir d'un simple comptage de demandes.
type ConteneurAdminDTO struct {
	ID           int    `json:"id"`
	Localisation string `json:"localisation"`
	Capacite     int    `json:"capacite"`
	Statut       string `json:"statut"`
	NbDemandes   int    `json:"nb_demandes"`
	Occupation   int    `json:"occupation"`
	FillRate     int    `json:"fill_rate"`
}

func (s *ConteneurService) AdminListerConteneurs() ([]ConteneurAdminDTO, error) {
	rows, err := s.repo.AdminListerConteneurs(database.DB)
	if err != nil {
		return nil, err
	}
	out := make([]ConteneurAdminDTO, 0, len(rows))
	for _, c := range rows {
		out = append(out, ConteneurAdminDTO{
			ID: c.ID, Localisation: c.Localisation, Capacite: c.Capacite, Statut: c.Statut,
			NbDemandes: c.NbDemandes, Occupation: c.Occupation,
			FillRate: domain.TauxRemplissage(c.Occupation, c.CapaciteBox),
		})
	}
	return out, nil
}

// ConteneurInput : intention de création/édition d'un conteneur (back-office).
type ConteneurInput struct {
	Localisation string
	Capacite     int
	Statut       string
}

// CreerConteneur insère un conteneur ET sa box, en une transaction. La box est
// indispensable : sans elle, le modèle d'occupation n'a aucune place où loger un
// objet et toute validation de dépôt échouerait. Identité admin = JWT (sub).
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
		id, err := s.repo.CreerConteneur(tx, localisation, in.Capacite, statut, idAdmin)
		if err != nil {
			return err
		}
		newID = id
		reference := genererReferenceBox(id)
		return s.repo.CreerBox(tx, reference, in.Capacite, int(id))
	})
	return newID, err
}

// ModifierConteneur met à jour le conteneur et synchronise la capacité de ses box
// (sinon l'UI mentirait sur la capacité réelle de dépôt). 404 si inexistant.
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
		n, err := s.repo.ModifierConteneur(tx, idConteneur, in.Localisation, in.Capacite, statut)
		if err != nil {
			return err
		}
		if n == 0 {
			return domain.Introuvable("Conteneur introuvable")
		}
		return s.repo.SyncBoxCapacite(tx, idConteneur, in.Capacite)
	})
}

// SupprimerConteneur supprime un conteneur ET ses box, mais SEULEMENT s'il est
// vide (aucun objet rattaché) et sans demande en cours — sinon 409 explicite
// (jamais un échec FK silencieux). READ COMMITTED + verrou des box sérialisent la
// suppression face à une validation concurrente.
func (s *ConteneurService) SupprimerConteneur(idConteneur int) error {
	return withTxIso(sql.LevelReadCommitted, func(tx *sql.Tx) error {
		if _, err := s.repo.ConteneurStatutPourMAJ(tx, idConteneur); err != nil {
			if errors.Is(err, sql.ErrNoRows) {
				return domain.Introuvable("Conteneur introuvable")
			}
			return err
		}
		// Verrou des box : bloque toute validation concurrente jusqu'au commit.
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

// alphabetCode : sans voyelles ni caractères ambigus (0/O, 1/I) déjà exclus par
// construction — on garde lettres et chiffres pour un code lisible et copiable.
const alphabetCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"

// genererCodeAcces produit un Code_acces « UC-XXXXXXXX » à partir d'entropie
// cryptographique. La génération vit dans le service (effet de bord aléatoire),
// pas dans le domaine pur.
func genererCodeAcces() string {
	b := make([]byte, 8)
	_, _ = rand.Read(b)
	out := make([]byte, 8)
	for i := range out {
		out[i] = alphabetCode[int(b[i])%len(alphabetCode)]
	}
	return "UC-" + string(out)
}

// genererReferenceBox : référence lisible d'une box, alignée sur la convention de
// seed (BOX-C<idConteneur>).
func genererReferenceBox(idConteneur int64) string {
	return "BOX-C" + strconv.FormatInt(idConteneur, 10)
}
