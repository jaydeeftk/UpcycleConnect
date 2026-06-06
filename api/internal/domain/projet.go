package domain

const (
	StatutProjetEnCours = "en_cours"
	StatutProjetPause   = "pause"
	StatutProjetTermine = "termine"
)

type ProjetSnapshot struct {
	ID                int
	Statut            string
	IdProprietairePro int
}

func (p ProjetSnapshot) AppartientAuPro(idPro int) bool {
	return idPro > 0 && p.IdProprietairePro == idPro
}

func (p ProjetSnapshot) PeutSuspendre() error {
	if p.Statut != StatutProjetEnCours {
		return EtatInvalide("Seul un projet en cours peut être mis en pause")
	}
	return nil
}

func (p ProjetSnapshot) PeutReprendre() error {
	if p.Statut != StatutProjetPause {
		return EtatInvalide("Seul un projet en pause peut être repris")
	}
	return nil
}

func (p ProjetSnapshot) PeutTerminer() error {
	if p.Statut != StatutProjetEnCours && p.Statut != StatutProjetPause {
		return EtatInvalide("Ce projet est déjà terminé")
	}
	return nil
}

func (p ProjetSnapshot) PeutRouvrir() error {
	if p.Statut != StatutProjetTermine {
		return EtatInvalide("Seul un projet terminé peut être rouvert")
	}
	return nil
}

func (p ProjetSnapshot) PeutModifierContenu() error {
	if p.Statut == StatutProjetTermine {
		return EtatInvalide("Un projet terminé est figé : rouvrez-le pour le modifier")
	}
	return nil
}

func StatutProjetValide(statut string) bool {
	switch statut {
	case StatutProjetEnCours, StatutProjetPause, StatutProjetTermine:
		return true
	default:
		return false
	}
}

func ActionsProjetPro(statut string) []string {
	switch statut {
	case StatutProjetEnCours:
		return []string{"suspendre", "terminer", "modifier", "ajouter_etape", "supprimer"}
	case StatutProjetPause:
		return []string{"reprendre", "terminer", "modifier", "ajouter_etape", "supprimer"}
	case StatutProjetTermine:
		return []string{"rouvrir", "supprimer"}
	default:
		return []string{}
	}
}
