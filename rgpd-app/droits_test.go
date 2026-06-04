package main

import "testing"

// TestTypesDroit verrouille l'énumération des droits acceptés (art. 15 à
// 21 RGPD). La validation applicative rejette toute valeur hors ENUM avant
// d'atteindre la base (défense en profondeur) : ce test documente et fige
// ce contrat.
func TestTypesDroit(t *testing.T) {
	valides := []string{
		"acces",         // art. 15
		"rectification", // art. 16
		"effacement",    // art. 17
		"portabilite",   // art. 20
		"limitation",    // art. 18
		"opposition",    // art. 21
	}
	for _, d := range valides {
		if !typesDroit[d] {
			t.Errorf("le droit %q devrait être accepté", d)
		}
	}

	invalides := []string{
		"",              // vide
		"Acces",         // casse non normalisée
		"suppression",   // synonyme non normalisé
		"acces ",        // espace parasite
		"droit_inconnu", // hors périmètre
	}
	for _, d := range invalides {
		if typesDroit[d] {
			t.Errorf("le droit %q devrait être rejeté", d)
		}
	}

	if len(typesDroit) != len(valides) {
		t.Errorf("typesDroit contient %d entrées, attendu %d", len(typesDroit), len(valides))
	}
}
