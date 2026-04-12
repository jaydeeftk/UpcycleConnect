package websockets

import (
	"sync"

	"github.com/gorilla/websocket"
)

type Client struct {
	UserID int
	Role   string
	Conn   *websocket.Conn
	Send   chan []byte
}

type Envelope struct {
	ToUserID int
	ToAdmins bool
	Payload  []byte
}

type Hub struct {
	users      map[int]*Client
	admins     map[int]*Client
	Register   chan *Client
	Unregister chan *Client
	route      chan *Envelope
	mu         sync.RWMutex
}

var GlobalHub = NewHub()

func NewHub() *Hub {
	return &Hub{
		users:      make(map[int]*Client),
		admins:     make(map[int]*Client),
		Register:   make(chan *Client, 32),
		Unregister: make(chan *Client, 32),
		route:      make(chan *Envelope, 512),
	}
}

func (h *Hub) Run() {
	for {
		select {
		case c := <-h.Register:
			h.mu.Lock()
			if c.Role == "admin" {
				h.admins[c.UserID] = c
			} else {
				h.users[c.UserID] = c
			}
			h.mu.Unlock()

		case c := <-h.Unregister:
			h.mu.Lock()
			if c.Role == "admin" {
				if existing, ok := h.admins[c.UserID]; ok && existing == c {
					delete(h.admins, c.UserID)
					safeClose(c.Send)
				}
			} else {
				if existing, ok := h.users[c.UserID]; ok && existing == c {
					delete(h.users, c.UserID)
					safeClose(c.Send)
				}
			}
			h.mu.Unlock()

		case env := <-h.route:
			h.mu.RLock()
			if env.ToAdmins {
				for _, admin := range h.admins {
					safeSend(admin.Send, env.Payload)
				}
			} else if env.ToUserID > 0 {
				if target, ok := h.users[env.ToUserID]; ok {
					safeSend(target.Send, env.Payload)
				}
			}
			h.mu.RUnlock()
		}
	}
}

func (h *Hub) SendToUser(userID int, payload []byte) {
	cp := make([]byte, len(payload))
	copy(cp, payload)
	h.route <- &Envelope{ToUserID: userID, Payload: cp}
}

func (h *Hub) SendToAdmins(payload []byte) {
	cp := make([]byte, len(payload))
	copy(cp, payload)
	h.route <- &Envelope{ToAdmins: true, Payload: cp}
}

func safeSend(ch chan []byte, payload []byte) {
	select {
	case ch <- payload:
	default:
	}
}

func safeClose(ch chan []byte) {
	select {
	case <-ch:
	default:
	}
	close(ch)
}
