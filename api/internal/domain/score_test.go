package domain

import "testing"

func TestCalculerScore_Bareme(t *testing.T) {
	a := ActiviteParticulier{Annonces: 2, Evenements: 1, Sujets: 3, Depots: 1, Formations: 2}
	total, hist := CalculerScore(a)
	if total != 190 {
		t.Fatalf("score attendu 190, obtenu %d", total)
	}
	if len(hist) != 5 {
		t.Fatalf("5 lignes d'historique attendues, obtenu %d", len(hist))
	}
	if hist[0].Action != "Annonces validées" || hist[0].Points != "+60" || hist[0].Detail != "2 annonce(s) × 30 pts" {
		t.Fatalf("première ligne inattendue: %+v", hist[0])
	}
	if hist[3].Points != "+50" || hist[3].Detail != "1 dépôt(s) × 50 pts" {
		t.Fatalf("ligne dépôt inattendue: %+v", hist[3])
	}
}

func TestCalculerScore_LignesNullesAbsentes(t *testing.T) {
	total, hist := CalculerScore(ActiviteParticulier{})
	if total != 0 {
		t.Fatalf("score attendu 0, obtenu %d", total)
	}
	if len(hist) != 0 {
		t.Fatalf("historique attendu vide, obtenu %d lignes", len(hist))
	}
	_, hist2 := CalculerScore(ActiviteParticulier{Depots: 1})
	if len(hist2) != 1 || hist2[0].Action != "Dépôts en conteneur validés" {
		t.Fatalf("attendu 1 ligne dépôt, obtenu %+v", hist2)
	}
}

func TestBadgesPour(t *testing.T) {
	cas := []struct {
		score       int
		wantActuel  string
		wantSuivant string
		wantDeb     int
	}{
		{0, "Éco-Débutant", "Recycleur Actif", 1},
		{99, "Éco-Débutant", "Recycleur Actif", 1},
		{100, "Recycleur Actif", "Éco-Engagé", 2},
		{299, "Recycleur Actif", "Éco-Engagé", 2},
		{300, "Éco-Engagé", "Phénix Vert", 3},
		{599, "Éco-Engagé", "Phénix Vert", 3},
		{600, "Phénix Vert", "", 4},
		{1000, "Phénix Vert", "", 4},
	}
	for _, c := range cas {
		actuel, suivant, tous := BadgesPour(c.score)
		if actuel.Label != c.wantActuel {
			t.Fatalf("score %d : badge actuel attendu %q, obtenu %q", c.score, c.wantActuel, actuel.Label)
		}
		if c.wantSuivant == "" && suivant != nil {
			t.Fatalf("score %d : badge suivant attendu nil, obtenu %q", c.score, suivant.Label)
		}
		if c.wantSuivant != "" && (suivant == nil || suivant.Label != c.wantSuivant) {
			t.Fatalf("score %d : badge suivant attendu %q, obtenu %v", c.score, c.wantSuivant, suivant)
		}
		deb := 0
		for _, b := range tous {
			if b.Debloque {
				deb++
			}
		}
		if deb != c.wantDeb {
			t.Fatalf("score %d : %d paliers débloqués attendus, obtenu %d", c.score, c.wantDeb, deb)
		}
	}
}
