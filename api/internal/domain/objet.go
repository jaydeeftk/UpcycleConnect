package domain

// Machine à états de la RÉCUPÉRATION PRO d'un objet déposé.
//
// Un objet matérialisé en box (cf. conteneur.go) suit ce cycle de vie côté
// professionnel :
//
//	en_stock --reserver(pro)------> reserve_pro --recuperer(pro propriétaire)--> recupere
//	                                reserve_pro --annuler(pro propriétaire)----> en_stock
//
// reserve_pro pose l'identité du professionnel (Id_Professionnels) sur l'objet :
// c'est la PROPRIÉTÉ métier qui garde les transitions suivantes (recuperer /
// annuler sont réservées au pro qui a réservé). recupere est terminal.
//
// Invariant d'occupation (cf. conteneur.go) : un objet reserve_pro est encore
// PHYSIQUEMENT dans sa box tant que le pro n'est pas venu le chercher ; il occupe
// donc toujours une place. Seul recupere libère la place. La réservation est ainsi
// neutre pour l'occupation, ce qui exclut tout sur-remplissage au moment d'annuler.

// ObjetSnapshot = état d'un objet lu sous FOR UPDATE avant une transition de
// récupération. IdProprietairePro porte le Id_Professionnels ayant réservé l'objet
// (0 si aucun) : il sert à la fois à la garde de propriété et aux allowed_actions.
type ObjetSnapshot struct {
	ID                int
	Statut            string
	IdProprietairePro int
}

// PeutReserver : seul un objet en_stock peut être réservé. Deux pros qui tentent
// de réserver le même objet : le premier le passe en reserve_pro, le second — qui
// relit l'état sous le même verrou — échoue ici (409), pas de double réservation.
func (o ObjetSnapshot) PeutReserver() error {
	if o.Statut != StatutObjetEnStock {
		return EtatInvalide("Cet objet n'est plus disponible à la réservation")
	}
	return nil
}

// PeutRecuperer : seul un objet réservé peut être récupéré (garde d'ÉTAT). La
// garde de PROPRIÉTÉ (le bon pro) est appliquée séparément par le service, avec
// l'identité issue du JWT — l'état seul ne suffit pas à autoriser.
func (o ObjetSnapshot) PeutRecuperer() error {
	if o.Statut != StatutObjetReservePro {
		return EtatInvalide("Seul un objet réservé peut être récupéré")
	}
	return nil
}

// PeutAnnulerReservation : seule une réservation active (reserve_pro) peut être
// annulée pour remettre l'objet en stock.
func (o ObjetSnapshot) PeutAnnulerReservation() error {
	if o.Statut != StatutObjetReservePro {
		return EtatInvalide("Seule une réservation active peut être annulée")
	}
	return nil
}

// AppartientAuPro : l'objet est-il réservé par CE professionnel ? C'est la garde
// de propriété métier des transitions reserve_pro -> {recupere, en_stock}.
func (o ObjetSnapshot) AppartientAuPro(idPro int) bool {
	return idPro > 0 && o.IdProprietairePro == idPro
}

// ActionsObjetPro dérive, depuis l'état + la propriété, les actions qu'un
// professionnel donné peut déclencher sur un objet. C'est la SOURCE DE VÉRITÉ des
// boutons : le front n'affiche que ce que le serveur autoriserait réellement.
//
//	en_stock                          -> ["reserver"]
//	reserve_pro (par CE pro)          -> ["recuperer","annuler"]
//	reserve_pro (par un AUTRE pro)    -> []
//	recupere                          -> []   (terminal)
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
