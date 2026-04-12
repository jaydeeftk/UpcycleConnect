package handlers

import (
	"encoding/json"
	"log"
	"net/http"
	"strings"
	"time"

	"upcycleconnect/internal/database"
	"upcycleconnect/internal/websockets"

	"github.com/gorilla/websocket"
)

var upgrader = websocket.Upgrader{
	CheckOrigin:     func(r *http.Request) bool { return true },
	ReadBufferSize:  1024,
	WriteBufferSize: 1024,
}

type wsIn struct {
	Type     string `json:"type"`
	To       int    `json:"to"`
	Content  string `json:"content"`
	FileURL  string `json:"file_url"`
	FileName string `json:"file_name"`
}

type wsMsg struct {
	Type      string `json:"type"`
	From      int    `json:"from"`
	FromName  string `json:"from_name"`
	IsAdmin   bool   `json:"is_admin"`
	Content   string `json:"content"`
	FileURL   string `json:"file_url"`
	FileName  string `json:"file_name"`
	CreatedAt string `json:"created_at"`
	ToUserID  int    `json:"to_user_id,omitempty"`
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
	role, _ := claims["role"].(string)

	conn, err := upgrader.Upgrade(w, r, nil)
	if err != nil {
		log.Println("WS upgrade:", err)
		return
	}

	client := &websockets.Client{
		UserID: userID,
		Role:   role,
		Conn:   conn,
		Send:   make(chan []byte, 256),
	}

	websockets.GlobalHub.Register <- client
	go wsWrite(client)
	go wsRead(client)
}

func wsRead(c *websockets.Client) {
	defer func() {
		websockets.GlobalHub.Unregister <- c
		c.Conn.Close()
	}()
	c.Conn.SetReadLimit(64 * 1024)

	for {
		_, raw, err := c.Conn.ReadMessage()
		if err != nil {
			if websocket.IsUnexpectedCloseError(err, websocket.CloseGoingAway, websocket.CloseAbnormalClosure) {
				log.Printf("WS read uid=%d: %v", c.UserID, err)
			}
			break
		}
		var msg wsIn
		if err := json.Unmarshal(raw, &msg); err != nil {
			continue
		}
		switch msg.Type {
		case "message":
			handleWsMessage(c, &msg)
		case "typing":
			handleWsTyping(c, &msg)
		}
	}
}

func wsWrite(c *websockets.Client) {
	defer c.Conn.Close()
	for payload := range c.Send {
		c.Conn.SetWriteDeadline(time.Now().Add(10 * time.Second))
		w, err := c.Conn.NextWriter(websocket.TextMessage)
		if err != nil {
			return
		}
		w.Write(payload)
		w.Close()
	}
	c.Conn.WriteMessage(websocket.CloseMessage, []byte{})
}

func handleWsMessage(c *websockets.Client, msg *wsIn) {
	if strings.TrimSpace(msg.Content) == "" && msg.FileURL == "" {
		return
	}

	now := time.Now().Format("2006-01-02 15:04:05")
	fromName := wsUserName(c.UserID)
	isAdmin := c.Role == "admin"

	out := wsMsg{
		Type:      "message",
		From:      c.UserID,
		FromName:  fromName,
		IsAdmin:   isAdmin,
		Content:   msg.Content,
		FileURL:   msg.FileURL,
		FileName:  msg.FileName,
		CreatedAt: now,
	}

	if isAdmin {
		if msg.To == 0 {
			return
		}
		database.DB.Exec(
			"INSERT INTO Messages (Contenu, Date_envoi, Id_Particuliers, Id_Utilisateurs) VALUES (?, NOW(), NULL, ?)",
			msg.Content, msg.To,
		)
		out.ToUserID = msg.To
		payload, _ := json.Marshal(out)
		websockets.GlobalHub.SendToUser(msg.To, payload)
		websockets.GlobalHub.SendToAdmins(payload)
	} else {
		var idPart int
		if err := database.DB.QueryRow(
			"SELECT Id_Particuliers FROM Particuliers WHERE Id_Utilisateurs = ?", c.UserID,
		).Scan(&idPart); err != nil {
			return
		}
		database.DB.Exec(
			"INSERT INTO Messages (Contenu, Date_envoi, Id_Particuliers, Id_Utilisateurs) VALUES (?, NOW(), ?, NULL)",
			msg.Content, idPart,
		)
		out.ToUserID = c.UserID
		payload, _ := json.Marshal(out)
		websockets.GlobalHub.SendToAdmins(payload)
		websockets.GlobalHub.SendToUser(c.UserID, payload)
	}
}

func handleWsTyping(c *websockets.Client, msg *wsIn) {
	payload, _ := json.Marshal(map[string]interface{}{
		"type":     "typing",
		"from":     c.UserID,
		"is_admin": c.Role == "admin",
	})
	if c.Role == "admin" && msg.To > 0 {
		websockets.GlobalHub.SendToUser(msg.To, payload)
	} else if c.Role != "admin" {
		websockets.GlobalHub.SendToAdmins(payload)
	}
}

func wsUserName(userID int) string {
	var nom, prenom string
	database.DB.QueryRow(
		"SELECT COALESCE(Nom,''), COALESCE(Prenom,'') FROM Utilisateurs WHERE Id_Utilisateurs = ?", userID,
	).Scan(&nom, &prenom)
	name := strings.TrimSpace(prenom + " " + nom)
	if name == "" {
		return "Utilisateur"
	}
	return name
}
