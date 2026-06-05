package domain

import (
	"errors"
	"reflect"
	"testing"
)

func TestObjetSnapshot_Gardes(t *testing.T) {
	cas := []struct {
		nom     string
		statut  string
		garde   func(ObjetSnapshot) error
		attendu error // nil ou catégorie attendue
	}{
		{"reserver depuis en_stock OK", StatutObjetEnStock, ObjetSnapshot.PeutReserver, nil},
		{"reserver depuis reserve_pro -> 409", StatutObjetReservePro, ObjetSnapshot.PeutReserver, ErrEtatInvalide},
		{"reserver depuis recupere -> 409", StatutObjetRecupere, ObjetSnapshot.PeutReserver, ErrEtatInvalide},
		{"recuperer depuis reserve_pro OK", StatutObjetReservePro, ObjetSnapshot.PeutRecuperer, nil},
		{"recuperer depuis en_stock -> 409", StatutObjetEnStock, ObjetSnapshot.PeutRecuperer, ErrEtatInvalide},
		{"recuperer depuis recupere -> 409", StatutObjetRecupere, ObjetSnapshot.PeutRecuperer, ErrEtatInvalide},
		{"annuler depuis reserve_pro OK", StatutObjetReservePro, ObjetSnapshot.PeutAnnulerReservation, nil},
		{"annuler depuis en_stock -> 409", StatutObjetEnStock, ObjetSnapshot.PeutAnnulerReservation, ErrEtatInvalide},
		{"annuler depuis recupere -> 409", StatutObjetRecupere, ObjetSnapshot.PeutAnnulerReservation, ErrEtatInvalide},
	}
	for _, c := range cas {
		t.Run(c.nom, func(t *testing.T) {
			err := c.garde(ObjetSnapshot{Statut: c.statut})
			if c.attendu == nil {
				if err != nil {
					t.Fatalf("attendu nil, obtenu %v", err)
				}
				return
			}
			if !errors.Is(err, c.attendu) {
				t.Fatalf("attendu catégorie %v, obtenu %v", c.attendu, err)
			}
		})
	}
}

func TestObjetSnapshot_AppartientAuPro(t *testing.T) {
	cas := []struct {
		nom        string
		proprio    int
		consultant int
		attendu    bool
	}{
		{"même pro -> vrai", 7, 7, true},
		{"pro différent -> faux", 7, 9, false},
		{"objet sans propriétaire -> faux", 0, 9, false},
		{"consultant anonyme (0) -> faux", 7, 0, false},
		{"les deux à 0 -> faux", 0, 0, false},
	}
	for _, c := range cas {
		t.Run(c.nom, func(t *testing.T) {
			got := ObjetSnapshot{IdProprietairePro: c.proprio}.AppartientAuPro(c.consultant)
			if got != c.attendu {
				t.Fatalf("attendu %v, obtenu %v", c.attendu, got)
			}
		})
	}
}

func TestActionsObjetPro(t *testing.T) {
	cas := []struct {
		nom        string
		statut     string
		proprio    int
		consultant int
		attendu    []string
	}{
		{"en_stock -> reserver", StatutObjetEnStock, 0, 5, []string{"reserver"}},
		{"reserve par moi -> recuperer+annuler", StatutObjetReservePro, 5, 5, []string{"recuperer", "annuler"}},
		{"reserve par un autre -> aucune", StatutObjetReservePro, 8, 5, []string{}},
		{"reserve mais consultant inconnu -> aucune", StatutObjetReservePro, 8, 0, []string{}},
		{"recupere -> aucune (terminal)", StatutObjetRecupere, 5, 5, []string{}},
		{"statut inconnu -> aucune", "bidon", 0, 5, []string{}},
	}
	for _, c := range cas {
		t.Run(c.nom, func(t *testing.T) {
			got := ActionsObjetPro(c.statut, c.proprio, c.consultant)
			if !reflect.DeepEqual(got, c.attendu) {
				t.Fatalf("attendu %v, obtenu %v", c.attendu, got)
			}
		})
	}
}
