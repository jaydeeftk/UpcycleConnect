package services

import (
	"database/sql"
	"errors"
	"math"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
	"upcycleconnect/internal/repository"
)

type ScoreService struct {
	repo repository.ScoreRepo
}

func NewScoreService() *ScoreService {
	return &ScoreService{}
}

type HistoriqueDTO struct {
	Action string `json:"action"`
	Points string `json:"points"`
	Detail string `json:"detail"`
	Icon   string `json:"icon"`
	Color  string `json:"color"`
}

type BadgeDTO struct {
	Label    string `json:"label"`
	Icon     string `json:"icon"`
	Color    string `json:"color"`
	Bg       string `json:"bg"`
	Min      int    `json:"min"`
	Max      int    `json:"max"`
	Debloque bool   `json:"debloque"`
}

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

	score, lignes := domain.CalculerScore(activite)
	return s.assembler(score, lignes), nil
}

func (s *ScoreService) assembler(score int, lignes []domain.LigneHistorique) ScoreDTO {
	hist := make([]HistoriqueDTO, 0, len(lignes))
	for _, l := range lignes {
		hist = append(hist, HistoriqueDTO{Action: l.Action, Points: l.Points, Detail: l.Detail, Icon: l.Icone, Color: l.Couleur})
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
	return BadgeDTO{Label: p.Label, Icon: p.Icone, Color: p.Couleur, Bg: p.Bg, Min: p.Min, Max: p.Max, Debloque: debloque}
}
