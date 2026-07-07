package domain

import "strings"

const ()

func ValiderCreationEvenement(titre, date, lieu string, capacite int, prix float64) error {
	if strings.TrimSpace(titre) == "" {
		return Invalide("Le titre de l'événement est obligatoire")
	}
	if err := ValiderDateProgrammation(date); err != nil {
		return err
	}
	if capacite < 0 {
		return Invalide("La capacité ne peut être négative")
	}
	if prix < 0 {
		return Invalide("Le tarif ne peut être négatif")
	}
	_ = lieu
	return nil
}

func ValiderCreationFormation(titre, date string, places int, prix float64) error {
	if strings.TrimSpace(titre) == "" {
		return Invalide("Le titre de la formation est obligatoire")
	}
	if err := ValiderDateProgrammation(date); err != nil {
		return err
	}
	if places < 0 {
		return Invalide("Le nombre de places ne peut être négatif")
	}
	if prix < 0 {
		return Invalide("Le tarif ne peut être négatif")
	}
	return nil
}

func ValiderCreationAtelier(theme, date, lieu string) error {
	if strings.TrimSpace(theme) == "" {
		return Invalide("Le thème de l'atelier est obligatoire")
	}
	if err := ValiderDateProgrammation(date); err != nil {
		return err
	}
	_ = lieu
	return nil
}
