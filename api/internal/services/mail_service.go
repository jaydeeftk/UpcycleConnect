package services

import (
	"crypto/tls"
	"errors"
	"fmt"
	"html"
	"net/smtp"
	"os"
	"strings"
	"time"
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

// sendMail envoie un email HTML via le SMTP configure (OVH Zimbra : STARTTLS 587).
// Fail-safe : si SMTP_HOST n'est pas defini, renvoie une erreur (l'appelant decide).
// En-tetes RFC completes (From/To/Date/Message-ID) pour la delivrabilite.
func sendMail(targetEmail, subject, htmlBody string) error {
	from := os.Getenv("SMTP_USER")
	password := os.Getenv("SMTP_PASS")
	smtpHost := os.Getenv("SMTP_HOST")
	smtpPort := os.Getenv("SMTP_PORT")
	if smtpHost == "" {
		return errors.New("SMTP non configure")
	}
	// Anti-injection d'en-tete : un sujet ne doit jamais contenir de saut de ligne.
	subject = strings.ReplaceAll(strings.ReplaceAll(subject, "\r", " "), "\n", " ")

	domain := "upcycleconnect.tech"
	if at := strings.LastIndex(from, "@"); at >= 0 && at+1 < len(from) {
		domain = from[at+1:]
	}

	headers := "From: UpcycleConnect <" + from + ">\r\n" +
		"To: " + targetEmail + "\r\n" +
		"Subject: " + subject + "\r\n" +
		"Date: " + time.Now().Format(time.RFC1123Z) + "\r\n" +
		fmt.Sprintf("Message-ID: <%d@%s>\r\n", time.Now().UnixNano(), domain) +
		"MIME-Version: 1.0\r\n" +
		"Content-Type: text/html; charset=\"UTF-8\"\r\n\r\n"
	msg := headers + htmlBody

	tlsConfig := &tls.Config{InsecureSkipVerify: false, ServerName: smtpHost}

	conn, err := smtp.Dial(smtpHost + ":" + smtpPort)
	if err != nil {
		return err
	}
	defer conn.Close()

	if err = conn.StartTLS(tlsConfig); err != nil {
		return err
	}
	if err = conn.Auth(LoginAuth(from, password)); err != nil {
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
	if _, err = w.Write([]byte(msg)); err != nil {
		return err
	}
	return w.Close()
}

// SendVerificationEmail envoie le lien d'activation a l'inscription.
func SendVerificationEmail(targetEmail string, token string) error {
	verifyLink := fmt.Sprintf("%s/verify?token=%s", os.Getenv("APP_URL"), token)
	body := fmt.Sprintf(`
		<div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 520px;">
			<h2 style="color: #2d3748;">Bienvenue sur UpcycleConnect !</h2>
			<p>Merci de nous rejoindre. Pour activer votre compte, cliquez sur le bouton ci-dessous :</p>
			<div style="margin: 25px 0;">
				<a href="%s" style="background-color: #48bb78; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Activer mon compte</a>
			</div>
			<p style="font-size: 0.8em; color: #718096;">Si le bouton ne s'affiche pas, utilisez ce lien : <br>%s</p>
		</div>`, verifyLink, verifyLink)
	return sendMail(targetEmail, "Activez votre compte UpcycleConnect", body)
}

// SendPasswordResetEmail envoie le lien de reinitialisation du mot de passe.
func SendPasswordResetEmail(targetEmail string, token string) error {
	resetLink := fmt.Sprintf("%s/reset-password?token=%s", os.Getenv("APP_URL"), token)
	body := fmt.Sprintf(`
		<div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 520px;">
			<h2 style="color: #2d3748;">Reinitialisation de votre mot de passe</h2>
			<p>Vous avez demande a reinitialiser votre mot de passe UpcycleConnect. Cliquez sur le bouton ci-dessous pour en choisir un nouveau :</p>
			<div style="margin: 25px 0;">
				<a href="%s" style="background-color: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Reinitialiser mon mot de passe</a>
			</div>
			<p style="font-size: 0.8em; color: #718096;">Si le bouton ne s'affiche pas, utilisez ce lien : <br>%s</p>
			<p style="font-size: 0.8em; color: #718096;">Si vous n'etes pas a l'origine de cette demande, ignorez simplement cet email : votre mot de passe reste inchange.</p>
		</div>`, resetLink, resetLink)
	return sendMail(targetEmail, "Reinitialisation de votre mot de passe UpcycleConnect", body)
}

// SendGenericEmail envoie un message libre (titre + contenu) dans le gabarit UpcycleConnect.
// Utilise pour les notifications envoyees depuis l'espace admin. Le contenu est echappe.
func SendGenericEmail(targetEmail string, subject string, message string) error {
	body := fmt.Sprintf(`
		<div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 520px;">
			<h2 style="color: #2d3748;">%s</h2>
			<p style="white-space: pre-line; color:#4a5568;">%s</p>
			<hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
			<p style="font-size: 0.8em; color: #718096;">UpcycleConnect — https://upcycleconnect.tech</p>
		</div>`, html.EscapeString(subject), html.EscapeString(message))
	return sendMail(targetEmail, subject, body)
}

// SendWelcomeEmail confirme l'activation du compte (envoye apres clic sur le lien).
func SendWelcomeEmail(targetEmail string, prenom string) error {
	appURL := os.Getenv("APP_URL")
	body := fmt.Sprintf(`
		<div style="font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 520px;">
			<h2 style="color: #2d3748;">Bienvenue %s !</h2>
			<p>Votre compte UpcycleConnect est desormais actif. Vous pouvez vous connecter, deposer vos objets, trouver des prestataires et participer a la communaute.</p>
			<div style="margin: 25px 0;">
				<a href="%s/login" style="background-color: #48bb78; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Me connecter</a>
			</div>
		</div>`, html.EscapeString(prenom), appURL)
	return sendMail(targetEmail, "Bienvenue sur UpcycleConnect", body)
}
