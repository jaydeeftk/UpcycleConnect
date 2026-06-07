// Package notifier encapsule l'envoi de notifications push via OneSignal.
package notifier

import (
	"bytes"
	"encoding/json"
	"net/http"
	"os"
	"strconv"
	"strings"
	"time"
)

// SendPush envoie une notification push aux utilisateurs cibles, identifiés par
// leur External ID (= Id_Utilisateurs, défini côté navigateur via OneSignal.login).
//
// No-op silencieux si ONESIGNAL_APP_ID ou ONESIGNAL_API_KEY ne sont pas
// configurés : l'application fonctionne normalement sans push (la notification
// in-app reste enregistrée en base par l'appelant). À appeler en goroutine :
// l'envoi ne doit jamais bloquer ni faire échouer le flux métier.
func SendPush(userIDs []int, contenu string) {
	appID := os.Getenv("ONESIGNAL_APP_ID")
	apiKey := os.Getenv("ONESIGNAL_API_KEY")
	if appID == "" || apiKey == "" || contenu == "" || len(userIDs) == 0 {
		return
	}

	ids := make([]string, 0, len(userIDs))
	for _, id := range userIDs {
		if id > 0 {
			ids = append(ids, strconv.Itoa(id))
		}
	}
	if len(ids) == 0 {
		return
	}

	payload := map[string]interface{}{
		"app_id":                        appID,
		"include_external_user_ids":     ids,
		"channel_for_external_user_ids": "push",
		"contents":                      map[string]string{"en": contenu, "fr": contenu},
	}
	body, err := json.Marshal(payload)
	if err != nil {
		return
	}

	req, err := http.NewRequest(http.MethodPost, "https://onesignal.com/api/v1/notifications", bytes.NewReader(body))
	if err != nil {
		return
	}
	req.Header.Set("Content-Type", "application/json")
	// Clé nouveau format (os_v2_...) -> "Key" ; clé legacy -> "Basic".
	if strings.HasPrefix(apiKey, "os_v2_") {
		req.Header.Set("Authorization", "Key "+apiKey)
	} else {
		req.Header.Set("Authorization", "Basic "+apiKey)
	}

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		return
	}
	_ = resp.Body.Close()
}
