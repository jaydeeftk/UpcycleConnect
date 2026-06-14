package main

import "testing"

func TestTypesDroit(t *testing.T) {
	valides := []string{
		"acces",
		"rectification",
		"effacement",
		"portabilite",
		"limitation",
		"opposition",
	}
	for _, d := range valides {
		if !typesDroit[d] {
			t.Errorf("le droit %q devrait être accepté", d)
		}
	}

	invalides := []string{
		"",
		"Acces",
		"suppression",
		"acces ",
		"droit_inconnu",
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
