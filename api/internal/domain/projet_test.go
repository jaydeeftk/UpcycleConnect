package domain

import (
	"errors"
	"reflect"
	"testing"
)

func TestProjetSnapshot_Transitions(t *testing.T) {
	cas := []struct {
		nom     string
		statut  string
		garde   func(ProjetSnapshot) error
		wantErr error
	}{
		{"suspendre depuis en_cours", StatutProjetEnCours, ProjetSnapshot.PeutSuspendre, nil},
		{"suspendre depuis pause", StatutProjetPause, ProjetSnapshot.PeutSuspendre, ErrEtatInvalide},
		{"suspendre depuis termine", StatutProjetTermine, ProjetSnapshot.PeutSuspendre, ErrEtatInvalide},
		{"reprendre depuis pause", StatutProjetPause, ProjetSnapshot.PeutReprendre, nil},
		{"reprendre depuis en_cours", StatutProjetEnCours, ProjetSnapshot.PeutReprendre, ErrEtatInvalide},
		{"reprendre depuis termine", StatutProjetTermine, ProjetSnapshot.PeutReprendre, ErrEtatInvalide},
		{"terminer depuis en_cours", StatutProjetEnCours, ProjetSnapshot.PeutTerminer, nil},
		{"terminer depuis pause", StatutProjetPause, ProjetSnapshot.PeutTerminer, nil},
		{"terminer depuis termine", StatutProjetTermine, ProjetSnapshot.PeutTerminer, ErrEtatInvalide},
		{"rouvrir depuis termine", StatutProjetTermine, ProjetSnapshot.PeutRouvrir, nil},
		{"rouvrir depuis en_cours", StatutProjetEnCours, ProjetSnapshot.PeutRouvrir, ErrEtatInvalide},
		{"rouvrir depuis pause", StatutProjetPause, ProjetSnapshot.PeutRouvrir, ErrEtatInvalide},
		{"modifier depuis en_cours", StatutProjetEnCours, ProjetSnapshot.PeutModifierContenu, nil},
		{"modifier depuis pause", StatutProjetPause, ProjetSnapshot.PeutModifierContenu, nil},
		{"modifier depuis termine", StatutProjetTermine, ProjetSnapshot.PeutModifierContenu, ErrEtatInvalide},
	}
	for _, c := range cas {
		t.Run(c.nom, func(t *testing.T) {
			err := c.garde(ProjetSnapshot{Statut: c.statut})
			if c.wantErr == nil && err != nil {
				t.Fatalf("attendu autorisé, obtenu %v", err)
			}
			if c.wantErr != nil && !errors.Is(err, c.wantErr) {
				t.Fatalf("attendu %v, obtenu %v", c.wantErr, err)
			}
		})
	}
}

func TestProjetSnapshot_AppartientAuPro(t *testing.T) {
	p := ProjetSnapshot{IdProprietairePro: 7}
	if !p.AppartientAuPro(7) {
		t.Fatal("le propriétaire 7 devrait être reconnu")
	}
	if p.AppartientAuPro(9) {
		t.Fatal("un autre pro ne doit pas être propriétaire")
	}
	if p.AppartientAuPro(0) {
		t.Fatal("idPro=0 ne doit jamais être propriétaire")
	}
	if (ProjetSnapshot{IdProprietairePro: 0}).AppartientAuPro(0) {
		t.Fatal("0 == 0 ne doit pas valoir propriété")
	}
}

func TestStatutProjetValide(t *testing.T) {
	for _, s := range []string{StatutProjetEnCours, StatutProjetPause, StatutProjetTermine} {
		if !StatutProjetValide(s) {
			t.Fatalf("%q devrait être valide", s)
		}
	}
	for _, s := range []string{"", "EN_COURS", "fini", "supprime", "archive"} {
		if StatutProjetValide(s) {
			t.Fatalf("%q ne devrait pas être valide", s)
		}
	}
}

func TestActionsProjetPro(t *testing.T) {
	cas := map[string][]string{
		StatutProjetEnCours: {"suspendre", "terminer", "modifier", "ajouter_etape", "supprimer"},
		StatutProjetPause:   {"reprendre", "terminer", "modifier", "ajouter_etape", "supprimer"},
		StatutProjetTermine: {"rouvrir", "supprimer"},
		"inconnu":           {},
	}
	for statut, want := range cas {
		if got := ActionsProjetPro(statut); !reflect.DeepEqual(got, want) {
			t.Fatalf("statut %q : attendu %v, obtenu %v", statut, want, got)
		}
	}
}
