package services

import (
	"crypto/tls"
	"errors"
	"fmt"
	"net/smtp"
	"os"
)

type loginAuth struct {
	username, password string
}

func LoginAuth(username, password string) smtp.Auth {
	return &loginAuth{username, password}
}

func (a *loginAuth) Start(server *smtp.ServerInfo) (string, []byte, error) {
	return "LOGIN", []byte{}, nil
}

func (a *loginAuth) Next(fromServer []byte, more bool) ([]byte, error) {
	if more {
		switch string(fromServer) {
		case "Username:", "user:":
			return []byte(a.username), nil
		case "Password:", "pass:":
			return []byte(a.password), nil
		default:
			return nil, errors.New("Serveur SMTP inconnu")
		}
	}
	return nil, nil
}

func SendVerificationEmail(targetEmail string, token string) error {
	from := os.Getenv("SMTP_USER")
	password := os.Getenv("SMTP_PASS")
	smtpHost := os.Getenv("SMTP_HOST")
	smtpPort := os.Getenv("SMTP_PORT")
	appURL := os.Getenv("APP_URL")

	verifyLink := fmt.Sprintf("%s/verify?token=%s", appURL, token)

	subject := "Subject: Activez votre compte UpcycleConnect\n"
	mime := "MIME-version: 1.0;\nContent-Type: text/html; charset=\"UTF-8\";\n\n"
	body := fmt.Sprintf(`
		<div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd;">
			<h2 style="color: #2d3748;">Bienvenue sur UpcycleConnect !</h2>
			<p>Merci de nous rejoindre. Pour activer votre compte, cliquez sur le bouton ci-dessous :</p>
			<div style="margin: 25px 0;">
				<a href="%s" style="background-color: #48bb78; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Activer mon compte</a>
			</div>
			<p style="font-size: 0.8em; color: #718096;">Si le bouton ne s'affiche pas, utilisez ce lien : <br>%s</p>
		</div>`, verifyLink, verifyLink)

	tlsConfig := &tls.Config{
		InsecureSkipVerify: false,
		ServerName:         smtpHost,
	}

	conn, err := smtp.Dial(smtpHost + ":" + smtpPort)
	if err != nil {
		return err
	}
	defer conn.Close()

	if err = conn.StartTLS(tlsConfig); err != nil {
		return err
	}

	auth := LoginAuth(from, password)
	if err = conn.Auth(auth); err != nil {
		return fmt.Errorf("Erreur Auth: %v", err)
	}

	if err = conn.Mail(from); err != nil {
		return err
	}
	if err = conn.Rcpt(targetEmail); err != nil {
		return err
	}

	w, err := conn.Data()
	if err != nil {
		return err
	}
	_, err = w.Write([]byte(subject + mime + body))
	if err != nil {
		return err
	}

	return w.Close()
}
