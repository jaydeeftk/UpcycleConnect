package domain

import "strings"

// Statuts d'une demande de dépôt en conteneur (machine à états, chk_demandes_statut).
//
//	en_attente --valider--> validee --deposer--> deposee
//	    \--refuser--> refusee
const (
	StatutDemandeEnAttente = "en_attente"
	StatutDemandeValidee   = "validee"
	StatutDemandeRefusee   = "refusee"
	StatutDemandeDeposee   = "deposee"
)

// Destination d'un objet déposé (don ou mise en vente).
const (
	DestinationDon   = "don"
	DestinationVente = "vente"
)

// Statuts d'un objet en conteneur (chk_objets_statut). Au sens de l'occupation
// dérivée d'une box, en_stock ET reserve_pro occupent une place (l'objet réservé
// reste physiquement dans la box jusqu'à son retrait par le pro) ; seul recupere
// libère la place. Cf. machine à états de récupération dans objet.go.
const (
	StatutObjetEnStock    = "en_stock"
	StatutObjetReservePro = "reserve_pro"
	StatutObjetRecupere   = "recupere"
)

// StatutBoxDisponible : une box ne peut accueillir un dépôt que si elle est disponible.
const StatutBoxDisponible = "disponible"

// StatutConteneurDisponible : un conteneur n'accepte de nouvelles demandes que disponible.
const StatutConteneurDisponible = "disponible"

// ValiderCreationDepot vérifie les invariants d'une demande de dépôt AVANT
// insertion : type d'objet présent et cohérence destination<->prix.
func ValiderCreationDepot(typeObjet, destination string, prix float64) error {
	if strings.TrimSpace(typeObjet) == "" {
		return Invalide("Le type d'objet est obligatoire")
	}
	switch destination {
	case DestinationDon:
		if prix != 0 {
			return Invalide("Un don ne peut pas avoir de prix de vente")
		}
	case DestinationVente:
		if prix <= 0 {
			return Invalide("Une mise en vente exige un prix strictement positif")
		}
	default:
		return Invalide("Destination invalide (attendu : don ou vente)")
	}
	return nil
}

// DemandeSnapshot = état courant d'une demande, lu sous FOR UPDATE avant transition.
// Type porte le Type_objet : il devient le Type de l'Objet matérialisé en box au
// moment de la validation.
type DemandeSnapshot struct {
	Statut       string
	Proprietaire int // Id_Particuliers auteur de la demande
	IdConteneur  int
	Type         string
}

// PeutValider : seule une demande en attente peut être validée.
func (d DemandeSnapshot) PeutValider() error {
	if d.Statut != StatutDemandeEnAttente {
		return EtatInvalide("Seule une demande en attente peut être validée")
	}
	return nil
}

// PeutRefuser : seule une demande en attente peut être refusée.
func (d DemandeSnapshot) PeutRefuser() error {
	if d.Statut != StatutDemandeEnAttente {
		return EtatInvalide("Seule une demande en attente peut être refusée")
	}
	return nil
}

// PeutDeposer : le dépôt physique n'est possible qu'une fois la demande validée.
func (d DemandeSnapshot) PeutDeposer() error {
	if d.Statut != StatutDemandeValidee {
		return EtatInvalide("Le dépôt n'est possible qu'après validation")
	}
	return nil
}

// ActionsDemandeAdmin dérive, depuis le seul statut, les transitions qu'un admin
// peut déclencher. C'est la SOURCE DE VÉRITÉ des boutons : le front n'affiche que
// ce que cette fonction renvoie, jamais une action que le serveur refuserait.
//
//	en_attente -> valider | refuser
//	validee    -> deposer
//	refusee / deposee -> (terminal, aucune action)
func ActionsDemandeAdmin(statut string) []string {
	switch statut {
	case StatutDemandeEnAttente:
		return []string{"valider", "refuser"}
	case StatutDemandeValidee:
		return []string{"deposer"}
	default:
		return []string{}
	}
}

// BoxSnapshot = capacité et occupation DÉRIVÉE d'une box, lues sous FOR UPDATE.
// L'occupation n'est jamais stockée : elle est recomptée à chaque décision.
type BoxSnapshot struct {
	ID         int
	Capacite   int
	Statut     string
	Occupation int // COUNT des objets présents (en_stock + reserve_pro) dans la box
}

// PeutAccueillir : box disponible avec au moins une place libre.
func (b BoxSnapshot) PeutAccueillir() bool {
	return b.Statut == StatutBoxDisponible && b.Occupation < b.Capacite
}

// ChoisirBox renvoie l'identifiant de la première box capable d'accueillir un
// objet, ou (0,false) si le conteneur est plein. La sélection se fait sur des
// snapshots lus FOR UPDATE : deux validations concurrentes ne peuvent donc pas
// sur-remplir la même box.
func ChoisirBox(boxes []BoxSnapshot) (int, bool) {
	for _, b := range boxes {
		if b.PeutAccueillir() {
			return b.ID, true
		}
	}
	return 0, false
}

// TauxRemplissage : occupation rapportée à la capacité, bornée à [0,100] pour l'UI.
func TauxRemplissage(occupation, capacite int) int {
	if capacite <= 0 {
		return 0
	}
	taux := occupation * 100 / capacite
	if taux > 100 {
		return 100
	}
	if taux < 0 {
		return 0
	}
	return taux
}
