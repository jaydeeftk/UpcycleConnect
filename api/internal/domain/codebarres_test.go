package domain

import (
	"errors"
	"testing"
)

func TestCodeBarreSnapshot_PeutServirARecuperer(t *testing.T) {
	cas := []struct {
		nom     string
		statut  string
		attendu error
	}{
		{"actif -> OK", StatutCodeBarreActive, nil},
		{"déjà utilisé -> 409", StatutCodeBarreUtilise, ErrEtatInvalide},
		{"statut inconnu -> 409", "bidon", ErrEtatInvalide},
		{"statut vide -> 409", "", ErrEtatInvalide},
	}
	for _, c := range cas {
		t.Run(c.nom, func(t *testing.T) {
			err := CodeBarreSnapshot{Statut: c.statut}.PeutServirARecuperer()
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

func TestCodeBarreSnapshot_Objet(t *testing.T) {
	cb := CodeBarreSnapshot{IdObjet: 42, StatutObjet: StatutObjetReservePro, IdProprietairePro: 7}
	o := cb.Objet()
	if o.ID != 42 || o.Statut != StatutObjetReservePro || o.IdProprietairePro != 7 {
		t.Fatalf("instantané objet incohérent: %+v", o)
	}
	if err := o.PeutRecuperer(); err != nil {
		t.Fatalf("objet reserve_pro devrait être récupérable: %v", err)
	}
	if !o.AppartientAuPro(7) {
		t.Fatalf("objet devrait appartenir au pro 7")
	}
	if o.AppartientAuPro(8) {
		t.Fatalf("objet ne devrait pas appartenir au pro 8")
	}

	enStock := CodeBarreSnapshot{StatutObjet: StatutObjetEnStock}.Objet()
	if err := enStock.PeutRecuperer(); !errors.Is(err, ErrEtatInvalide) {
		t.Fatalf("objet en_stock ne devrait pas être récupérable, obtenu %v", err)
	}
}
