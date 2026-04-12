package handlers

import (
	"log"
	"net/http"

	"upcycleconnect/internal/websockets"
	"github.com/gorilla/websocket"
)

var upgrader = websocket.Upgrader{
	CheckOrigin: func(r *http.Request) bool { return true },
}

func ServeWS(w http.ResponseWriter, r *http.Request) {
	tokenStr := r.URL.Query().Get("token")
	if tokenStr == "" {
		http.Error(w, "Non autorisé", http.StatusUnauthorized)
		return
	}

	claims, err := parseJWT(tokenStr)
	if err != nil {
		http.Error(w, "Token invalide", http.StatusUnauthorized)
		return
	}

	userID := int(claims["sub"].(float64))
	role := claims["role"].(string)

	conn, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Println("Erreur d'upgrade WS:", err)
		return
	}

	client := &websockets.Client{
		UserID: userID,
		Role:   role,
		Conn:   conn,
		Send:   make(chan []byte, 256),
	}

	websockets.GlobalHub.Register <- client

	go writePump(client)
	go readPump(client)
}

func readPump(client *websockets.Client) {
	defer func() {
		websockets.GlobalHub.Unregister <- client
		client.Conn.Close()
	}()

	for {
		_, message, err := client.Conn.ReadMessage()
		if err != nil {
			if websocket.IsUnexpectedCloseError(err, websocket.CloseGoingAway, websocket.CloseAbnormalClosure) {
				log.Printf("Erreur WS: %v", err)
			}
			break
		}

		websockets.GlobalHub.Broadcast <- message
	}
}

func writePump(client *websockets.Client) {
	defer client.Conn.Close()
	for {
		select {
		case message, ok := <-client.Send:
			if !ok {
				client.Conn.WriteMessage(websocket.CloseMessage, []byte{})
				return
			}

			w, err := client.Conn.NextWriter(websocket.TextMessage)
			if err != nil {
				return
			}
			w.Write(message)

			if err := w.Close(); err != nil {
				return
			}
		}
	}
}
