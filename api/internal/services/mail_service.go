package services

import (
	"crypto/tls"
	"fmt"
	"net/smtp"
	"os"
)

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
		<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
			<h2 style="color: #2d3748; text-align: center;">Bienvenue sur UpcycleConnect !</h2>
			<p>Merci de rejoindre notre communauté engagée pour l'upcycling. Pour finaliser votre inscription, cliquez sur le bouton ci-dessous :</p>
			<div style="text-align: center; margin: 30px 0;">
				<a href="%s" style="background-color: #48bb78; color: white; padding: 15px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
					Confirmer mon adresse email
				</a>
			</div>
			<p style="color: #718096; font-size: 12px; text-align: center;">
				Si le bouton ne fonctionne pas, copiez ce lien : <br> %s
			</p>
		</div>`, verifyLink, verifyLink)

	auth := smtp.PlainAuth("", from, password, smtpHost)
	tlsConfig := &tls.Config{InsecureSkipVerify: false, ServerName: smtpHost}

	conn, err := smtp.Dial(smtpHost + ":" + smtpPort)
	if err != nil {
		return err
	}

	if err = conn.StartTLS(tlsConfig); err != nil {
		return err
	}

	if err = conn.Auth(auth); err != nil {
		return err
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
	err = w.Close()
	return conn.Quit()
}
