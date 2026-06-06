package domain

const (
	StatutCodeBarreActive  = "active"
	StatutCodeBarreUtilise = "utilise"
)

type CodeBarreSnapshot struct {
	ID                int
	Statut            string
	IdObjet           int
	StatutObjet       string
	IdProprietairePro int
}

func (c CodeBarreSnapshot) PeutServirARecuperer() error {
	if c.Statut != StatutCodeBarreActive {
		return EtatInvalide("Ce code-barres a déjà été utilisé")
	}
	return nil
}

func (c CodeBarreSnapshot) Objet() ObjetSnapshot {
	return ObjetSnapshot{ID: c.IdObjet, Statut: c.StatutObjet, IdProprietairePro: c.IdProprietairePro}
}
