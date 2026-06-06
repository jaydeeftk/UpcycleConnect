package handlers

import (
	"strconv"
	"strings"
)

func idDepuisChemin(path, prefix string) (int, error) {
	rest := strings.Trim(strings.TrimPrefix(path, prefix), "/")
	seg := strings.SplitN(rest, "/", 2)[0]
	return strconv.Atoi(seg)
}

func segmentsApres(path, prefix string) []string {
	rest := strings.Trim(strings.TrimPrefix(path, prefix), "/")
	if rest == "" {
		return nil
	}
	return strings.Split(rest, "/")
}
