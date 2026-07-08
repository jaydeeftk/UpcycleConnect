package handlers

import (
	"sort"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/domain"
)

func validerEtTrierDates(dates []string) ([]string, error) {
	if len(dates) == 0 {
		return nil, domain.Invalide("Au moins une date est requise")
	}
	for _, d := range dates {
		if err := domain.ValiderDateProgrammation(d); err != nil {
			return nil, err
		}
	}
	out := append([]string{}, dates...)
	sort.Strings(out)
	return out, nil
}

func remplacerDatesEvenement(idEvenement int, dates []string) error {
	if _, err := database.DB.Exec("DELETE FROM Evenement_Dates WHERE Id_Evenements = ?", idEvenement); err != nil {
		return err
	}
	for _, d := range dates {
		if _, err := database.DB.Exec("INSERT INTO Evenement_Dates (Id_Evenements, Date_session) VALUES (?, ?)", idEvenement, d); err != nil {
			return err
		}
	}
	return nil
}

func remplacerDatesFormation(idFormation int, dates []string) error {
	if _, err := database.DB.Exec("DELETE FROM Formation_Dates WHERE Id_Formations = ?", idFormation); err != nil {
		return err
	}
	for _, d := range dates {
		if _, err := database.DB.Exec("INSERT INTO Formation_Dates (Id_Formations, Date_session) VALUES (?, ?)", idFormation, d); err != nil {
			return err
		}
	}
	return nil
}
