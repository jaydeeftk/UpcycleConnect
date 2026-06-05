package domain

// Statuts d'un code-barres (chk_codes_barres_statut). Un code naît 'active' à la
// matérialisation de l'objet en box, puis passe 'utilise' (terminal) au moment où
// le professionnel récupère l'objet. Aucun retour arrière : un code consommé ne
// peut plus servir.
//
//	active --(récupération de l'objet)--> utilise
const (
	StatutCodeBarreActive  = "active"
	StatutCodeBarreUtilise = "utilise"
)

// CodeBarreSnapshot : état d'un code-barres ET de l'objet qu'il désigne, lus
// ensemble sous FOR UPDATE avant une récupération par scan. Il porte de quoi
// rejouer EXACTEMENT les mêmes gardes que la récupération par identifiant (état
// de l'objet + propriété du pro), sans dupliquer la règle métier : le service
// reconstruit un ObjetSnapshot via Objet().
type CodeBarreSnapshot struct {
	ID                int    // Id_Codes_Barres
	Statut            string // statut du code-barres lui-même
	IdObjet           int    // objet désigné par ce code
	StatutObjet       string // statut courant de cet objet
	IdProprietairePro int    // pro ayant réservé l'objet (0 si aucun)
}

// PeutServirARecuperer : un code-barres n'autorise une récupération que tant qu'il
// est actif. Un code déjà consommé -> ErrEtatInvalide (409).
func (c CodeBarreSnapshot) PeutServirARecuperer() error {
	if c.Statut != StatutCodeBarreActive {
		return EtatInvalide("Ce code-barres a déjà été utilisé")
	}
	return nil
}

// Objet reconstruit l'instantané de l'objet désigné par ce code, pour appliquer
// les gardes de récupération (PeutRecuperer + AppartientAuPro) sans réécrire la
// règle ailleurs : la récupération par scan et par identifiant partagent la même
// machine à états.
func (c CodeBarreSnapshot) Objet() ObjetSnapshot {
	return ObjetSnapshot{ID: c.IdObjet, Statut: c.StatutObjet, IdProprietairePro: c.IdProprietairePro}
}
