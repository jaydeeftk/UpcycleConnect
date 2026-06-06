package domain

import (
	"errors"
	"testing"
	"time"
)

func TestCalculerTTC(t *testing.T) {
	cases := []struct {
		ht, tva, want float64
	}{
		{100, 20, 120},
		{0, 20, 0},
		{99.99, 20, 119.99},
		{33.33, 5.5, 35.16},
		{1000, 0, 1000},
		{49.90, 20, 59.88},
	}
	for _, c := range cases {
		if got := CalculerTTC(c.ht, c.tva); got != c.want {
			t.Errorf("CalculerTTC(%.2f, %.2f) = %.2f, want %.2f", c.ht, c.tva, got, c.want)
		}
	}
}

func TestValiderMontantsFacture(t *testing.T) {
	cases := []struct {
		name         string
		ht, tva, ttc float64
		wantErr      bool
	}{
		{"cohérent 20%", 100, 20, 120, false},
		{"cohérent TVA réduite", 33.33, 5.5, 35.16, false},
		{"TTC trop bas (fraude)", 100, 20, 100, true},
		{"TTC trop haut", 100, 20, 130, true},
		{"HT négatif", -1, 20, -1.2, true},
		{"TVA hors borne", 100, 150, 250, true},
		{"écart d'un centime rejeté", 100, 20, 120.02, true},
	}
	for _, c := range cases {
		err := ValiderMontantsFacture(c.ht, c.tva, c.ttc)
		if c.wantErr && err == nil {
			t.Errorf("%s: attendu une erreur, nil", c.name)
		}
		if !c.wantErr && err != nil {
			t.Errorf("%s: attendu nil, %v", c.name, err)
		}
		if c.wantErr && err != nil && !errors.Is(err, ErrInvalide) {
			t.Errorf("%s: attendu ErrInvalide (422), %v", c.name, err)
		}
	}
}

func TestValiderCommission(t *testing.T) {
	cases := []struct {
		name                string
		taux, base, montant float64
		wantErr             bool
	}{
		{"10% de 200", 10, 200, 20, false},
		{"5% de 99.90", 5, 99.90, 5.00, false},
		{"montant gonflé", 10, 200, 50, true},
		{"taux hors borne", 120, 200, 240, true},
		{"base négative", 10, -5, -0.5, true},
	}
	for _, c := range cases {
		err := ValiderCommission(c.taux, c.base, c.montant)
		if c.wantErr != (err != nil) {
			t.Errorf("%s: wantErr=%v, err=%v", c.name, c.wantErr, err)
		}
	}
}

func TestTransitionContrat(t *testing.T) {
	cases := []struct {
		statut, action, want string
		wantErr              bool
	}{
		{StatutContratBrouillon, "activer", StatutContratActif, false},
		{StatutContratActif, "suspendre", StatutContratSuspendu, false},
		{StatutContratSuspendu, "reactiver", StatutContratActif, false},
		{StatutContratActif, "resilier", StatutContratResilie, false},
		{StatutContratSuspendu, "expirer", StatutContratExpire, false},

		{StatutContratBrouillon, "suspendre", "", true},
		{StatutContratResilie, "activer", "", true},
		{StatutContratExpire, "reactiver", "", true},
		{StatutContratActif, "activer", "", true},
		{StatutContratActif, "n_importe_quoi", "", true},
	}
	for _, c := range cases {
		got, err := TransitionContrat(c.statut, c.action)
		if c.wantErr != (err != nil) {
			t.Errorf("TransitionContrat(%q,%q): wantErr=%v, err=%v", c.statut, c.action, c.wantErr, err)
		}
		if got != c.want {
			t.Errorf("TransitionContrat(%q,%q) = %q, want %q", c.statut, c.action, got, c.want)
		}
	}
}

func TestActionsContratAdminCoherentesAvecTransition(t *testing.T) {
	toutes := []string{"activer", "suspendre", "reactiver", "resilier", "expirer"}
	statuts := []string{
		StatutContratBrouillon, StatutContratActif, StatutContratSuspendu,
		StatutContratResilie, StatutContratExpire,
	}
	for _, st := range statuts {
		autorisees := map[string]bool{}
		for _, a := range ActionsContratAdmin(st) {
			autorisees[a] = true
		}
		for _, a := range toutes {
			_, err := TransitionContrat(st, a)
			licite := err == nil
			if licite != autorisees[a] {
				t.Errorf("statut %q action %q : transition licite=%v mais exposée=%v", st, a, licite, autorisees[a])
			}
		}
	}
}

func TestExigePaiement(t *testing.T) {
	cases := []struct {
		name    string
		prix    float64
		aPaye   bool
		wantErr bool
	}{
		{"gratuit non payé : ok", 0, false, false},
		{"gratuit payé : ok", 0, true, false},
		{"payant non payé : 402", 50, false, true},
		{"payant payé : ok", 50, true, false},
		{"prix négatif traité comme gratuit", -5, false, false},
	}
	for _, c := range cases {
		err := ExigePaiement(c.prix, c.aPaye)
		if c.wantErr != (err != nil) {
			t.Errorf("%s: wantErr=%v, err=%v", c.name, c.wantErr, err)
		}
		if c.wantErr && !errors.Is(err, ErrPaiementRequis) {
			t.Errorf("%s: attendu ErrPaiementRequis (402), %v", c.name, err)
		}
	}
}

func TestValiderContrat(t *testing.T) {
	d1 := time.Date(2026, 1, 1, 0, 0, 0, 0, time.UTC)
	d2 := time.Date(2026, 12, 31, 0, 0, 0, 0, time.UTC)
	var zero time.Time
	cases := []struct {
		name       string
		typ        string
		debut, fin time.Time
		idPro      int
		wantErr    bool
	}{
		{"valide", "maintenance", d1, d2, 7, false},
		{"sans date fin : valide", "maintenance", d1, zero, 7, false},
		{"type vide", "", d1, d2, 7, true},
		{"pro manquant", "maintenance", d1, d2, 0, true},
		{"fin avant début", "maintenance", d2, d1, 7, true},
	}
	for _, c := range cases {
		err := ValiderContrat(c.typ, c.debut, c.fin, c.idPro)
		if c.wantErr != (err != nil) {
			t.Errorf("%s: wantErr=%v, err=%v", c.name, c.wantErr, err)
		}
	}
}
