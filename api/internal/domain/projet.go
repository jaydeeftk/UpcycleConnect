package domain

// Machine à états d'un PROJET D'UPCYCLING (côté professionnel).
//
// Un professionnel documente un projet (titre, description, étapes illustrées).
// Le projet suit ce cycle de vie :
//
//	en_cours --suspendre--> pause      (mise en pause temporaire)
//	pause    --reprendre--> en_cours
//	en_cours --terminer---> termine    (projet abouti)
//	pause    --terminer---> termine
//	termine  --rouvrir----> en_cours   (réouverture pour corriger/compléter)
//
// Le vocabulaire {en_cours, pause, termine} est celui réellement présenté par le
// front (formulaire de création) : il n'est pas inventé ici, il est CANONISÉ —
// avant ce vertical Statut était un VARCHAR libre jamais contraint.
//
// PROPRIÉTÉ : un projet appartient à un professionnel (Id_Professionnels). Toute
// transition, toute modification de contenu et la suppression sont réservées au
// pro propriétaire ; l'identité vient du JWT, jamais de l'URL/body.
//
// FIGE : un projet termine est figé — on ne modifie plus son contenu ni ses
// étapes tant qu'il n'est pas rouvert. Cela garantit qu'un livrable « terminé »
// reste cohérent avec ce qui a été publié, sans bloquer une correction (rouvrir).

const (
	StatutProjetEnCours = "en_cours"
	StatutProjetPause   = "pause"
	StatutProjetTermine = "termine"
)

// ProjetSnapshot = état d'un projet lu (sous FOR UPDATE pour une transition)
// avant de décider. IdProprietairePro porte le Id_Professionnels propriétaire :
// il sert à la garde de propriété ET n'apparaît jamais dans le DTO.
type ProjetSnapshot struct {
	ID                int
	Statut            string
	IdProprietairePro int
}

// AppartientAuPro : le projet est-il la propriété de CE professionnel ? Garde de
// propriété commune à toutes les écritures (transitions, contenu, étapes, suppr.).
func (p ProjetSnapshot) AppartientAuPro(idPro int) bool {
	return idPro > 0 && p.IdProprietairePro == idPro
}

// PeutSuspendre : en_cours -> pause. Seul un projet en cours peut être suspendu.
func (p ProjetSnapshot) PeutSuspendre() error {
	if p.Statut != StatutProjetEnCours {
		return EtatInvalide("Seul un projet en cours peut être mis en pause")
	}
	return nil
}

// PeutReprendre : pause -> en_cours. Seul un projet en pause peut être repris.
func (p ProjetSnapshot) PeutReprendre() error {
	if p.Statut != StatutProjetPause {
		return EtatInvalide("Seul un projet en pause peut être repris")
	}
	return nil
}

// PeutTerminer : {en_cours, pause} -> termine. Un projet déjà terminé ne peut pas
// l'être à nouveau (idempotence garde par l'état terminal).
func (p ProjetSnapshot) PeutTerminer() error {
	if p.Statut != StatutProjetEnCours && p.Statut != StatutProjetPause {
		return EtatInvalide("Ce projet est déjà terminé")
	}
	return nil
}

// PeutRouvrir : termine -> en_cours. Seul un projet terminé peut être rouvert.
func (p ProjetSnapshot) PeutRouvrir() error {
	if p.Statut != StatutProjetTermine {
		return EtatInvalide("Seul un projet terminé peut être rouvert")
	}
	return nil
}

// PeutModifierContenu : le contenu (titre/description) et les étapes ne sont
// mutables que tant que le projet n'est pas figé (terminé). Mutualisé entre
// l'édition du projet et l'ajout/suppression d'étapes — même précondition d'état.
func (p ProjetSnapshot) PeutModifierContenu() error {
	if p.Statut == StatutProjetTermine {
		return EtatInvalide("Un projet terminé est figé : rouvrez-le pour le modifier")
	}
	return nil
}

// StatutProjetValide borne le vocabulaire accepté à la création. Tout autre
// valeur est rejetée (422) AVANT l'écriture — doublon applicatif du CHECK SQL
// chk_projets_statut, pour renvoyer un message métier plutôt qu'une erreur 500.
func StatutProjetValide(statut string) bool {
	switch statut {
	case StatutProjetEnCours, StatutProjetPause, StatutProjetTermine:
		return true
	default:
		return false
	}
}

// ActionsProjetPro dérive de l'état les actions qu'un professionnel propriétaire
// peut déclencher sur SON projet. C'est la SOURCE DE VÉRITÉ des boutons : le front
// n'affiche que ça. (La liste ne renvoyant que les projets du pro, toutes les
// lignes lui appartiennent — pas de cas « projet d'un autre ».)
//
//	en_cours -> ["suspendre","terminer","modifier","ajouter_etape","supprimer"]
//	pause    -> ["reprendre","terminer","modifier","ajouter_etape","supprimer"]
//	termine  -> ["rouvrir","supprimer"]                       (figé sauf rouvrir)
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
