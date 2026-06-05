package domain

import "strings"

// Statuts canoniques d'une annonce — source de vérité unique, verrouillée en
// dernière ligne de défense par chk_annonces_statut (migration 003).
const (
	StatutAnnEnAttente = "en_attente"
	StatutAnnValidee   = "validee"
	StatutAnnRefusee   = "refusee"
	StatutAnnRetiree   = "retiree"
	StatutAnnVendue    = "vendue"
)

// Types d'annonce. Invariant de cohérence prix : un don est gratuit (prix == 0),
// une vente exige un prix strictement positif.
const (
	TypeAnnDon   = "don"
	TypeAnnVente = "vente"
)

// ValiderCreationAnnonce applique les invariants d'ENTRÉE d'un dépôt : titre
// présent, type connu, et cohérence type↔prix. Fonction pure (aucune I/O) :
// c'est ici que vit la règle métier, jamais dans le handler ni le contrôleur PHP.
func ValiderCreationAnnonce(titre, typeAnnonce string, prix float64) error {
	if strings.TrimSpace(titre) == "" {
		return Invalide("Le titre est obligatoire")
	}
	switch typeAnnonce {
	case TypeAnnDon:
		if prix != 0 {
			return Invalide("Un don ne peut pas avoir de prix")
		}
	case TypeAnnVente:
		if prix <= 0 {
			return Invalide("Une vente exige un prix supérieur à 0")
		}
	default:
		return Invalide("Type d'annonce invalide")
	}
	return nil
}

// AnnonceSnapshot — état serveur d'une annonce au moment précis d'une transition
// (lu sous verrou FOR UPDATE pour les écritures). Proprietaire porte l'Id_Particuliers
// du déposant : il fonde le contrôle de PROPRIÉTÉ (une action « propriétaire »
// exige requérant == Proprietaire, vérifié côté service).
type AnnonceSnapshot struct {
	Statut       string
	Type         string
	Prix         float64
	Proprietaire int
}

// PeutValider : transition ADMIN. On ne valide qu'une annonce encore en attente
// (idempotence fermée : revalider une annonce déjà publiée est un 409, pas un no-op).
func (a AnnonceSnapshot) PeutValider() error {
	if a.Statut != StatutAnnEnAttente {
		return EtatInvalide("Seule une annonce en attente peut être validée")
	}
	return nil
}

// PeutRefuser : transition ADMIN, symétrique de la validation.
func (a AnnonceSnapshot) PeutRefuser() error {
	if a.Statut != StatutAnnEnAttente {
		return EtatInvalide("Seule une annonce en attente peut être refusée")
	}
	return nil
}

// PeutRetirer : transition PROPRIÉTAIRE. Une annonce encore active — en attente
// de modération OU publiée — peut être retirée par son auteur. Un état terminal
// (vendue / refusée / déjà retirée) n'est plus retirable.
func (a AnnonceSnapshot) PeutRetirer() error {
	if a.Statut != StatutAnnEnAttente && a.Statut != StatutAnnValidee {
		return EtatInvalide("Cette annonce n'est plus retirable")
	}
	return nil
}

// PeutMarquerVendue : transition PROPRIÉTAIRE depuis une annonce PUBLIÉE. On ne
// peut vendre que ce qui est effectivement en ligne (pas un brouillon en attente).
func (a AnnonceSnapshot) PeutMarquerVendue() error {
	if a.Statut != StatutAnnValidee {
		return EtatInvalide("Seule une annonce publiée peut être marquée vendue")
	}
	return nil
}

// AnnonceVisible décide de l'EXPOSITION d'une fiche. Au public (anonyme, ou tout
// requérant non propriétaire et non admin) on n'expose qu'une annonce publiée ou
// vendue : les états en_attente / refusee / retiree restent privés — ni fuite
// d'existence, ni PII du déposant. Le propriétaire et l'admin voient tout état.
func AnnonceVisible(statut string, estProprietaire, estAdmin bool) bool {
	if estProprietaire || estAdmin {
		return true
	}
	return statut == StatutAnnValidee || statut == StatutAnnVendue
}

// ActionsAnnonce dérive, CÔTÉ SERVEUR, les actions permises sur la fiche pour ce
// requérant — source unique de la règle d'or « le front n'affiche que ce que le
// serveur autorise ». Liste vide pour un visiteur sans droit d'action.
func (a AnnonceSnapshot) ActionsAnnonce(estProprietaire, estAdmin bool) []string {
	actions := []string{}
	if estAdmin && a.Statut == StatutAnnEnAttente {
		actions = append(actions, "valider", "refuser")
	}
	if estProprietaire {
		if a.PeutRetirer() == nil {
			actions = append(actions, "retirer")
		}
		if a.PeutMarquerVendue() == nil {
			actions = append(actions, "vendre")
		}
	}
	return actions
}
