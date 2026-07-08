package domain

type ObjetSnapshot struct {
	ID                int
	Statut            string
	IdProprietairePro int
}

func (o ObjetSnapshot) PeutReserver() error {
	if o.Statut != StatutObjetEnStock {
		return EtatInvalide("Cet objet n'est plus disponible à la réservation")
	}
	return nil
}

func (o ObjetSnapshot) PeutRecuperer() error {
	if o.Statut != StatutObjetReservePro {
		return EtatInvalide("Seul un objet réservé peut être récupéré")
	}
	return nil
}

func (o ObjetSnapshot) PeutAnnulerReservation() error {
	if o.Statut != StatutObjetReservePro {
		return EtatInvalide("Seule une réservation active peut être annulée")
	}
	return nil
}

func (o ObjetSnapshot) AppartientAuPro(idPro int) bool {
	return idPro > 0 && o.IdProprietairePro == idPro
}

func ActionsObjetPro(statut string, idProprietairePro, idProConsultant int) []string {
	switch statut {
	case StatutObjetEnStock:
		return []string{"reserver"}
	case StatutObjetReservePro:
		if idProConsultant > 0 && idProprietairePro == idProConsultant {
			return []string{"recuperer", "annuler"}
		}
		return []string{}
	default:
		return []string{}
	}
}

func (o ObjetSnapshot) PeutRecupererParticulier() error {
	if o.Statut != StatutObjetEnStock {
		return EtatInvalide("Cet objet n'est plus disponible à la récupération")
	}
	return nil
}

func ActionsObjetParticulier(statut string) []string {
	if statut == StatutObjetEnStock {
		return []string{"recuperer"}
	}
	return []string{}
}
