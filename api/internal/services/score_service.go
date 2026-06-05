package services

import (
	"database/sql"
	"errors"
	"math"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

// ScoreService assemble la vue de gamification d'un particulier. Le score et les
// badges sont DÉRIVÉS (lecture pure) : contrairement à l'ancien handler, un GET
// n'écrit RIEN (le cache Particuliers.Score, jamais relu, n'est plus alimenté en
// effet de bord — un GET doit être sans effet).
type ScoreService struct {
	repo repository.ScoreRepo
}

func NewScoreService() *ScoreService {
	return &ScoreService{}
}

// HistoriqueDTO : une entrée d'historique. Clés JSON préservées à l'identique de
// l'ancienne réponse (le front les lit déjà : icon/color/action/points/detail).
type HistoriqueDTO struct {
	Action string `json:"action"`
	Points string `json:"points"`
	Detail string `json:"detail"`
	Icon   string `json:"icon"`
	Color  string `json:"color"`
}

// BadgeDTO : un palier de badge servi par l'API (le front cesse de coder ces
// seuils en PHP — la vérité est désormais serveur).
type BadgeDTO struct {
	Label    string `json:"label"`
	Icon     string `json:"icon"`
	Color    string `json:"color"`
	Bg       string `json:"bg"`
	Min      int    `json:"min"`
	Max      int    `json:"max"`
	Debloque bool   `json:"debloque"`
}

// ScoreDTO : réponse complète de /api/score/{id}. score + historique sont le
// contrat existant ; les champs badge_* sont additifs (n'altèrent pas le front
// actuel, mais lui permettent d'arrêter de calculer les paliers lui-même).
type ScoreDTO struct {
	Score             int             `json:"score"`
	ScoreMax          int             `json:"score_max"`
	Pct               int             `json:"pct"`
	Historique        []HistoriqueDTO `json:"historique"`
	BadgeActuel       BadgeDTO        `json:"badge_actuel"`
	BadgeSuivant      *BadgeDTO       `json:"badge_suivant"`
	PointsVersSuivant int             `json:"points_vers_suivant"`
	Badges            []BadgeDTO      `json:"badges"`
}

// ScoreDuParticulier calcule la vue de gamification de l'utilisateur visé. Si
// l'utilisateur n'est pas un particulier, on renvoie une vue « zéro » (score 0,
// historique vide, badges au palier initial) — pas une erreur : un pro ou un
// admin consultant son propre score voit 0, comme avant.
func (s *ScoreService) ScoreDuParticulier(idUtilisateur int) (ScoreDTO, error) {
	var activite domain.ActiviteParticulier
	idParticulier, err := s.repo.ResoudreParticulier(database.DB, idUtilisateur)
	if err == nil {
		if activite, err = s.repo.Activite(database.DB, idParticulier, idUtilisateur); err != nil {
			return ScoreDTO{}, err
		}
	} else if !errors.Is(err, sql.ErrNoRows) {
		return ScoreDTO{}, err
	}
	// (ErrNoRows -> activite reste zéro -> score 0, vue cohérente.)

	score, lignes := domain.CalculerScore(activite)
	return s.assembler(score, lignes), nil
}

func (s *ScoreService) assembler(score int, lignes []domain.LigneHistorique) ScoreDTO {
	hist := make([]HistoriqueDTO, 0, len(lignes))
	for _, l := range lignes {
		hist = append(hist, HistoriqueDTO{
			Action: l.Action, Points: l.Points, Detail: l.Detail, Icon: l.Icone, Color: l.Couleur,
		})
	}

	actuel, suivant, tous := domain.BadgesPour(score)
	badges := make([]BadgeDTO, 0, len(tous))
	for _, b := range tous {
		badges = append(badges, versBadgeDTO(b.PalierBadge, b.Debloque))
	}

	pointsVersSuivant := 0
	var suivantDTO *BadgeDTO
	if suivant != nil {
		d := versBadgeDTO(*suivant, false)
		suivantDTO = &d
		if pv := suivant.Min - score; pv > 0 {
			pointsVersSuivant = pv
		}
	}

	pct := int(math.Round(float64(score) / float64(domain.ScoreMax) * 100))
	if pct > 100 {
		pct = 100
	}

	return ScoreDTO{
		Score:             score,
		ScoreMax:          domain.ScoreMax,
		Pct:               pct,
		Historique:        hist,
		BadgeActuel:       versBadgeDTO(actuel, true),
		BadgeSuivant:      suivantDTO,
		PointsVersSuivant: pointsVersSuivant,
		Badges:            badges,
	}
}

func versBadgeDTO(p domain.PalierBadge, debloque bool) BadgeDTO {
	return BadgeDTO{
		Label: p.Label, Icon: p.Icone, Color: p.Couleur, Bg: p.Bg,
		Min: p.Min, Max: p.Max, Debloque: debloque,
	}
}
