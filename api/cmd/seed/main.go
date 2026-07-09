package main

import (
	"database/sql"
	"fmt"
	"log"
	"math/rand"
	"os"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"golang.org/x/crypto/bcrypt"
)

const motDePasseDemo = "Upcycle2026!"

var rng = rand.New(rand.NewSource(42))

var prenoms = []string{"Marie", "Thomas", "Camille", "Nicolas", "Julie", "Alexandre", "Sophie", "Julien", "Emma", "Lucas",
	"Léa", "Hugo", "Chloé", "Maxime", "Manon", "Antoine", "Sarah", "Romain", "Laura", "Pierre",
	"Mathilde", "Kevin", "Pauline", "Florian", "Clara", "Adrien", "Elodie", "Quentin", "Anaïs", "Guillaume",
	"Charlotte", "Benjamin", "Amandine", "Vincent", "Justine", "Sébastien", "Morgane", "David", "Océane", "Fabien",
	"Nadia", "Karim", "Fatou", "Mehdi", "Ines", "Yanis", "Awa", "Rachid", "Lina", "Omar"}

var noms = []string{"Martin", "Bernard", "Dubois", "Thomas", "Robert", "Richard", "Petit", "Durand", "Leroy", "Moreau",
	"Simon", "Laurent", "Lefebvre", "Michel", "Garcia", "Fournier", "Lambert", "Rousseau", "Vincent", "Muller",
	"Faure", "Andre", "Mercier", "Blanc", "Guerin", "Boyer", "Garnier", "Chevalier", "Francois", "Legrand",
	"Gauthier", "Perrin", "Robin", "Clement", "Morin", "Nicolas", "Henry", "Roussel", "Mathieu", "Gautier",
	"Benali", "Diallo", "Traore", "Nguyen", "Costa", "Fernandez", "Lopez", "Silva", "Haddad", "Cohen"}

var villes = [][2]string{{"Paris", "75011"}, {"Paris", "75015"}, {"Paris", "75020"}, {"Montreuil", "93100"}, {"Boulogne-Billancourt", "92100"},
	{"Lyon", "69003"}, {"Lyon", "69007"}, {"Villeurbanne", "69100"}, {"Marseille", "13006"}, {"Lille", "59000"},
	{"Nantes", "44000"}, {"Bordeaux", "33000"}, {"Toulouse", "31000"}, {"Rennes", "35000"}, {"Strasbourg", "67000"},
	{"Montpellier", "34000"}, {"Grenoble", "38000"}, {"Angers", "49000"}, {"Dijon", "21000"}, {"Tours", "37000"}}

var categoriesObjet = []string{"Mobilier", "Textile", "Électroménager", "Décoration", "Vélo", "Jouets", "Livres", "Vaisselle"}
var etatsAnnonce = []string{"neuf", "bon", "usage", "abime"}
var typesObjet = []string{"chaise", "table basse", "commode", "fauteuil", "lampe", "vélo", "grille-pain", "cafetière",
	"étagère", "miroir", "tapis", "veste en cuir", "manteau", "service de table", "cadre photo", "bureau",
	"tabouret", "valise vintage", "machine à coudre", "platine vinyle"}
var adjectifs = []string{"vintage", "en bon état", "à retaper", "des années 70", "en chêne massif", "scandinave",
	"industriel", "peu servi", "de famille", "original", "fait main", "à customiser"}

var typesPro = []string{"menuiserie", "couture", "electronique", "metallerie", "decoration", "reparation_velo", "tapisserie", "ceramique"}
var entreprisesSuffixe = []string{"Atelier", "Créations", "& Co", "Rénov", "Studio", "Fabrique", "Récup'", "Design"}

var titresServices = []string{"Rempaillage de chaise", "Retapissage de fauteuil", "Réparation de vélo", "Restauration de meuble",
	"Customisation textile", "Réparation petit électroménager", "Relooking de commode", "Création sur mesure à partir de récup",
	"Affûtage et entretien d'outils", "Réparation de luminaire", "Couture et retouches", "Restauration de cadre ancien"}

var categoriesService = []string{"Réparation", "Transformation", "Restauration", "Upcycling"}

var categoriesFormation = []string{"Couture", "Menuiserie", "Upcycling", "Réparation", "Zéro déchet", "Débutant"}
var titresFormation = []string{"Initiation à la couture récup", "Restaurer un meuble ancien", "Réparer son électroménager",
	"Upcycling textile : les bases", "Menuiserie de récupération", "Créer ses produits ménagers", "Rempaillage traditionnel",
	"Teinture végétale sur tissu", "Réparation de vélo niveau 1", "Mosaïque à partir de vaisselle cassée"}

var categoriesEvenement = []string{"atelier", "marche", "conference", "exposition", "communautaire"}
var titresEvenement = []string{"Repair Café du quartier", "Marché de la seconde main", "Atelier upcycling parent-enfant",
	"Conférence zéro déchet", "Exposition Matières Revalorisées", "Troc party textile", "Atelier réparation vélo",
	"Rencontre des artisans upcycleurs", "Collecte solidaire d'objets", "Portes ouvertes de l'atelier"}

var messagesPool = []string{"Bonjour, est-ce toujours disponible ?", "Oui, tout à fait !", "Est-ce que le prix est négociable ?",
	"Je peux passer le récupérer ce week-end.", "Parfait, samedi matin ça vous va ?", "Pouvez-vous m'envoyer plus de photos ?",
	"Il y a quelques rayures mais rien de grave.", "Très bien, je vous le réserve.", "Merci beaucoup, à samedi !",
	"Quelle est la hauteur exacte ?", "Environ 80 cm.", "Le mécanisme fonctionne parfaitement.",
	"Je vous confirme la remise en main propre.", "C'est noté, merci !", "Super, bonne journée !"}

var avisPool = []string{"Travail soigné, je recommande vivement.", "Très bon contact et objet conforme à la description.",
	"Atelier passionnant, animatrice au top.", "Formation claire et concrète, j'ai déjà tout mis en pratique.",
	"Réparation impeccable et rapide.", "Bonne ambiance, très instructif.", "Le résultat dépasse mes attentes.",
	"Un peu court mais très dense, parfait pour débuter.", "Artisan sérieux, délais respectés.", "Expérience conviviale, à refaire !"}

var conseilsPool = [][2]string{
	{"Trier avant de déposer", "Séparez textiles, bois et électroménager avant dépôt : le tri en amont accélère la valorisation."},
	{"Nettoyer les textiles", "Lavez les vêtements avant de les déposer, ils repartiront plus vite vers une seconde vie."},
	{"Tester l'électroménager", "Indiquez si l'appareil s'allume : cela aide les réparateurs à prioriser."},
	{"Démonter sans casser", "Gardez la visserie dans un sachet scotché au meuble démonté."},
	{"Photographier sous bonne lumière", "Une photo nette en lumière naturelle double les chances de réservation."},
	{"Peindre sans poncer", "Sur mélaminé, une sous-couche d'accroche évite un ponçage fastidieux."},
	{"Chiner malin", "Les pieds de table se récupèrent facilement pour créer des bancs."},
	{"Entretenir le cuir", "Un lait hydratant redonne vie à un fauteuil en cuir craquelé."},
	{"Réutiliser les bocaux", "Stérilisés, ils servent au vrac comme aux confitures."},
}

var sujetsForum = []string{"Comment retapisser un fauteuil club ?", "Quelle peinture pour du mélaminé ?",
	"Restaurer une malle ancienne rouillée", "Machine à coudre qui saute des points, des idées ?",
	"Où trouver du bois de palette propre ?", "Customiser une commode Ikea", "Réparer un grille-pain qui ne chauffe plus",
	"Teinture naturelle qui ne dégorge pas ?", "Fixer des pieds épingle sur un plateau chêne", "Vernis ou huile pour un plan de travail récup ?"}

func fdate(t time.Time) string { return t.Format("2006-01-02 15:04:05") }

var accents = strings.NewReplacer("é", "e", "è", "e", "ê", "e", "ë", "e", "ï", "i", "î", "i", "ç", "c", "à", "a", "ô", "o")

func slugMail(s string) string { return accents.Replace(strings.ToLower(s)) }

func bulk(tx *sql.Tx, table string, cols []string, rows [][]interface{}) {
	if len(rows) == 0 {
		return
	}
	const lot = 300
	ph := "(" + strings.TrimSuffix(strings.Repeat("?,", len(cols)), ",") + ")"
	for i := 0; i < len(rows); i += lot {
		fin := i + lot
		if fin > len(rows) {
			fin = len(rows)
		}
		paquet := rows[i:fin]
		var args []interface{}
		for _, r := range paquet {
			args = append(args, r...)
		}
		q := "INSERT INTO `" + table + "` (`" + strings.Join(cols, "`,`") + "`) VALUES " +
			strings.TrimSuffix(strings.Repeat(ph+",", len(paquet)), ",")
		if _, err := tx.Exec(q, args...); err != nil {
			log.Fatalf("%s: %v", table, err)
		}
	}
	fmt.Printf("  %-24s %5d lignes\n", table, len(rows))
}

func main() {
	dsn := os.Getenv("DB_DSN")
	if dsn == "" {
		log.Fatal("DB_DSN manquant")
	}
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal(err)
	}
	defer db.Close()

	var n int
	if err := db.QueryRow("SELECT COUNT(*) FROM Utilisateurs").Scan(&n); err != nil {
		log.Fatal(err)
	}
	if n > 0 {
		log.Fatalf("Utilisateurs contient déjà %d lignes, abandon", n)
	}

	hash, err := bcrypt.GenerateFromPassword([]byte(motDePasseDemo), bcrypt.DefaultCost)
	if err != nil {
		log.Fatal(err)
	}

	tx, err := db.Begin()
	if err != nil {
		log.Fatal(err)
	}
	defer tx.Rollback()

	maintenant := time.Now()
	debut := maintenant.AddDate(0, -10, 0)
	passe := func() time.Time {
		return debut.Add(time.Duration(rng.Int63n(int64(maintenant.Sub(debut)))))
	}
	futur := func() time.Time {
		return maintenant.AddDate(0, 0, 3+rng.Intn(150)).Add(time.Duration(9+rng.Intn(9)) * time.Hour)
	}

	bulk(tx, "Langue", []string{"Id_Langue", "Nom"}, [][]interface{}{
		{1, "Français"}, {2, "English"}, {3, "Español"}, {4, "Deutsch"},
	})

	type personne struct{ nom, prenom, email string }
	gens := make([]personne, 1001)
	emails := map[string]bool{}
	uniqueEmail := func(base string) string {
		e, i := base, 1
		for emails[e] {
			p := strings.SplitN(base, "@", 2)
			e = fmt.Sprintf("%s%d@%s", p[0], i, p[1])
			i++
		}
		emails[e] = true
		return e
	}
	gens[1] = personne{"PHANOUKOUN", "Jaydee", uniqueEmail("jaydee@upcycleconnect.tech")}
	gens[2] = personne{"YALA", "Sofiane", uniqueEmail("sofiane@upcycleconnect.tech")}
	gens[3] = personne{"BD", "Youssouf", uniqueEmail("youssouf@upcycleconnect.tech")}
	gens[4] = personne{"Dupont", "Marie", uniqueEmail("marie.dupont@upcycleconnect.tech")}
	for i := 5; i <= 18; i++ {
		p, nm := prenoms[rng.Intn(len(prenoms))], noms[rng.Intn(len(noms))]
		gens[i] = personne{nm, p, uniqueEmail(slugMail(p) + "." + slugMail(nm) + "@upcycleconnect.tech")}
	}
	domaines := []string{"gmail.com", "hotmail.fr", "orange.fr", "outlook.fr", "free.fr", "laposte.net"}
	for i := 19; i <= 1000; i++ {
		p, nm := prenoms[rng.Intn(len(prenoms))], noms[rng.Intn(len(noms))]
		gens[i] = personne{nm, p, uniqueEmail(fmt.Sprintf("%s.%s@%s", slugMail(p), slugMail(nm), domaines[rng.Intn(len(domaines))]))}
	}
	var uRows [][]interface{}
	for i := 1; i <= 1000; i++ {
		naissance := time.Date(1960+rng.Intn(45), time.Month(1+rng.Intn(12)), 1+rng.Intn(28), 0, 0, 0, 0, time.UTC)
		uRows = append(uRows, []interface{}{
			i, gens[i].nom, gens[i].prenom,
			fmt.Sprintf("06%08d", rng.Intn(100000000)),
			"actif",
			fmt.Sprintf("%d rue de la %s", 1+rng.Intn(120), []string{"Paix", "République", "Gare", "Mairie", "Fontaine", "Forge"}[rng.Intn(6)]),
			string(hash), gens[i].email, nil, fdate(passe()), fdate(naissance), 1, 1,
		})
	}
	bulk(tx, "Utilisateurs", []string{"Id_Utilisateurs", "Nom", "Prenom", "Telephone", "Statut", "Adresse", "Mot_de_passe", "Email", "Token_confirmation", "Date_Inscription", "Date_naissance", "Id_Langue", "Tutoriel_vu"}, uRows)

	bulk(tx, "Administrateurs", []string{"Id_Administrateurs", "Grade", "Id_Utilisateurs"}, [][]interface{}{
		{1, "superadmin", 1}, {2, "admin", 2}, {3, "admin", 3},
	})

	postes := []string{"Animateur", "Logistique", "Conseiller", "Modérateur", "Coordinateur"}
	var salRows [][]interface{}
	for s := 1; s <= 15; s++ {
		salRows = append(salRows, []interface{}{s, postes[rng.Intn(len(postes))], "Direction", fdate(passe()), 3 + s})
	}
	bulk(tx, "Salaries", []string{"Id_Salaries", "Poste", "Responsable", "Date_Debut_Contrat", "Id_Utilisateurs"}, salRows)

	var proRows [][]interface{}
	for p := 1; p <= 280; p++ {
		u := 18 + p
		ent := fmt.Sprintf("%s %s", entreprisesSuffixe[rng.Intn(len(entreprisesSuffixe))], gens[u].nom)
		proRows = append(proRows, []interface{}{p, 10000000000000 + rng.Int63n(89999999999999), ent, typesPro[rng.Intn(len(typesPro))], nil, u})
	}
	bulk(tx, "Professionnels_artisans", []string{"Id_Professionnels", "Siret", "Nom_Entreprise", "Type", "Id_Abonnement", "Id_Utilisateurs"}, proRows)

	var partRows [][]interface{}
	for p := 1; p <= 700; p++ {
		partRows = append(partRows, []interface{}{p, rng.Intn(500), 298 + p})
	}
	bulk(tx, "Particuliers", []string{"Id_Particuliers", "Score", "Id_Utilisateurs"}, partRows)

	partUser := func(p int) int { return 298 + p }
	proUser := func(p int) int { return 18 + p }

	var aboRows [][]interface{}
	prosPremium := rng.Perm(280)[:30]
	for i, p := range prosPremium {
		statut := "actif"
		if i >= 25 {
			statut = "resilie"
		}
		d := passe()
		aboRows = append(aboRows, []interface{}{
			fmt.Sprintf("sub_seed_%03d", i+1), "premium", 24.99,
			d.Format("2006-01-02"), d.AddDate(1, 0, 0).Format("2006-01-02"), statut,
			p + 1, 5, rng.Intn(6), fmt.Sprintf("cs_abo_seed_%03d", i+1), fmt.Sprintf("sub_stripe_seed_%03d", i+1),
		})
	}
	bulk(tx, "Abonnement", []string{"Id_Abonnement", "Type", "Prix", "Date_Debut", "Date_Fin", "Statut", "Id_Professionnels", "Annonces_Gratuites_Incluses", "Annonces_Gratuites_Utilisees", "Reference_Stripe", "Stripe_Subscription_Id"}, aboRows)
	for i, p := range prosPremium {
		if _, err := tx.Exec("UPDATE Professionnels_artisans SET Id_Abonnement=? WHERE Id_Professionnels=?", fmt.Sprintf("sub_seed_%03d", i+1), p+1); err != nil {
			log.Fatal(err)
		}
	}

	lieux := []string{"Paris 11e — Bastille", "Paris 15e — Vaugirard", "Montreuil — Croix de Chavaux", "Boulogne — Marcel Sembat",
		"Lyon 3e — Part-Dieu", "Lille — Centre", "Nantes — Commerce", "Bordeaux — Chartrons"}
	var contRows [][]interface{}
	for c := 1; c <= 8; c++ {
		statut := "disponible"
		if c == 8 {
			statut = "maintenance"
		}
		contRows = append(contRows, []interface{}{c, lieux[c-1], "64", statut, (c-1)%3 + 1, 250.0, 240.0, 600.0})
	}
	bulk(tx, "Conteneurs", []string{"Id_Conteneurs", "Localisation", "Capacite", "Statut", "Id_Administrateurs", "Hauteur", "Largeur", "Longueur"}, contRows)

	var boxRows [][]interface{}
	boxID := 0
	boxesParConteneur := map[int][]int{}
	for c := 1; c <= 8; c++ {
		for b := 1; b <= 8; b++ {
			boxID++
			taille, h, l, lo := "standard", 60.0, 60.0, 80.0
			if b > 6 {
				taille, h, l, lo = "encombrant", 120.0, 100.0, 180.0
			}
			boxRows = append(boxRows, []interface{}{boxID, fmt.Sprintf("C%02d-B%02d", c, b), 1, "disponible", c, taille, h, l, lo})
			boxesParConteneur[c] = append(boxesParConteneur[c], boxID)
		}
	}
	bulk(tx, "Box", []string{"Id_Box", "Reference", "Capacite", "Statut", "Id_Conteneurs", "Taille", "Hauteur_cm", "Largeur_cm", "Longueur_cm"}, boxRows)

	type annonce struct {
		id, part, pro, acheteur int
		statut, typeAnn         string
	}
	annonces := make([]annonce, 0, 3000)
	var annRows [][]interface{}
	statutsAnn := func() string {
		r := rng.Intn(100)
		switch {
		case r < 50:
			return "validee"
		case r < 65:
			return "vendue"
		case r < 75:
			return "reservee"
		case r < 90:
			return "en_attente"
		case r < 95:
			return "refusee"
		default:
			return "retiree"
		}
	}
	annoncesParPart := map[int][]int{}
	for i := 1; i <= 3000; i++ {
		a := annonce{id: i, statut: statutsAnn()}
		if rng.Intn(100) < 85 {
			a.part = 1 + rng.Intn(700)
			annoncesParPart[a.part] = append(annoncesParPart[a.part], i)
		} else {
			a.pro = 1 + rng.Intn(280)
		}
		a.typeAnn = "don"
		prix := 0.0
		if rng.Intn(100) < 45 {
			a.typeAnn = "vente"
			prix = float64(5+rng.Intn(245)) + 0.99
		}
		proprio := 0
		if a.part > 0 {
			proprio = partUser(a.part)
		} else {
			proprio = proUser(a.pro)
		}
		var acheteur interface{}
		if a.statut == "vendue" || a.statut == "reservee" {
			for {
				a.acheteur = 299 + rng.Intn(700)
				if a.acheteur != proprio {
					break
				}
			}
			acheteur = a.acheteur
		}
		objet := typesObjet[rng.Intn(len(typesObjet))]
		titre := strings.Title(objet) + " " + adjectifs[rng.Intn(len(adjectifs))]
		v := villes[rng.Intn(len(villes))]
		var idPart, idPro interface{}
		if a.part > 0 {
			idPart = a.part
		}
		if a.pro > 0 {
			idPro = a.pro
		}
		annRows = append(annRows, []interface{}{
			i, fdate(passe()), "À récupérer sur place, " + v[0] + ".", a.statut, idPart,
			titre, "Je me sépare de ce "+objet+" "+adjectifs[rng.Intn(len(adjectifs))]+". Idéal pour un projet upcycling.",
			categoriesObjet[rng.Intn(len(categoriesObjet))], etatsAnnonce[rng.Intn(len(etatsAnnonce))],
			a.typeAnn, prix, v[0], v[1], acheteur, idPro, nil,
		})
		annonces = append(annonces, a)
	}
	bulk(tx, "Annonces", []string{"Id_Annonces", "Date_publication", "Contenu", "Statut", "Id_Particuliers", "Titre", "Description", "Categorie", "Etat", "Type_annonce", "Prix", "Ville", "Code_postal", "Id_Acheteur_Utilisateur", "Id_Professionnels", "Photo_url"}, annRows)

	type demande struct{ id, part, box, cont int }
	var demandes []demande
	var demRows [][]interface{}
	codes := map[string]bool{}
	for i := 1; i <= 350; i++ {
		part := 1 + rng.Intn(700)
		statut := "validee"
		if i > 200 && i <= 300 {
			statut = "en_attente"
		} else if i > 300 {
			statut = "refusee"
		}
		var code, dateDepot interface{}
		var idBox, idCont interface{}
		d := demande{id: i, part: part}
		if statut == "validee" {
			c := 1 + rng.Intn(7)
			b := boxesParConteneur[c][rng.Intn(len(boxesParConteneur[c]))]
			for {
				cd := fmt.Sprintf("%06d", rng.Intn(1000000))
				if !codes[cd] {
					codes[cd] = true
					code = cd
					break
				}
			}
			idBox, idCont = b, c
			dateDepot = fdate(passe())
			d.box, d.cont = b, c
		}
		var idAnn interface{}
		if la := annoncesParPart[part]; len(la) > 0 && rng.Intn(100) < 40 {
			idAnn = la[rng.Intn(len(la))]
		}
		dest := "don"
		prixV := 0.0
		if rng.Intn(100) < 40 {
			dest = "vente"
			prixV = float64(5 + rng.Intn(120))
		}
		demRows = append(demRows, []interface{}{
			i, typesObjet[rng.Intn(len(typesObjet))], "Objet en état correct, à valoriser.",
			etatsAnnonce[rng.Intn(len(etatsAnnonce))], idCont, dateDepot, dest, prixV, nil,
			statut, code, fdate(passe()), part, idBox, idAnn,
		})
		if statut == "validee" {
			demandes = append(demandes, d)
		}
	}
	bulk(tx, "Demandes_conteneurs", []string{"Id_Demandes_conteneurs", "Type_objet", "Description", "Etat_usure", "Id_Conteneurs", "Date_depot", "Destination", "Prix_vente", "Photo_url", "Statut", "Code_acces", "Date_demande", "Id_Particuliers", "Id_Box", "Id_Annonces"}, demRows)

	var objRows, cbRows [][]interface{}
	statutObj := func() string {
		r := rng.Intn(100)
		switch {
		case r < 55:
			return "en_stock"
		case r < 80:
			return "reserve_pro"
		default:
			return "recupere"
		}
	}
	for i := 1; i <= 5000; i++ {
		st := statutObj()
		var idPro interface{}
		if st != "en_stock" {
			idPro = 1 + rng.Intn(280)
		}
		var cont, part int
		var idBox, idDem interface{}
		if i <= len(demandes) {
			d := demandes[i-1]
			cont, part = d.cont, d.part
			idBox, idDem = d.box, d.id
			cbRows = append(cbRows, []interface{}{i, fmt.Sprintf("OBJ-%06d", i), fdate(passe()), "active", i, d.box})
		} else {
			cont = 1 + rng.Intn(7)
			part = 1 + rng.Intn(700)
			if rng.Intn(100) < 70 {
				idBox = boxesParConteneur[cont][rng.Intn(len(boxesParConteneur[cont]))]
			}
		}
		objRows = append(objRows, []interface{}{
			i, categoriesObjet[rng.Intn(len(categoriesObjet))], fmt.Sprintf("%.1f", 0.5+rng.Float64()*24), st, cont, idPro, part, idBox, idDem,
		})
	}
	bulk(tx, "Objets", []string{"Id_Objets", "Type", "Poids", "Statut", "Id_Conteneurs", "Id_Professionnels", "Id_Particuliers", "Id_Box", "Id_Demandes_conteneurs"}, objRows)
	bulk(tx, "Codes_Barres", []string{"Id_Codes_Barres", "Code", "Date_generation", "Statut", "Id_Objets", "Id_Box"}, cbRows)

	type formation struct {
		id     int
		prix   float64
		actif  bool
		resa   []int
	}
	formations := make([]formation, 0, 100)
	var formRows, formDatesRows [][]interface{}
	idFormDate := 0
	dejaResa := map[string]bool{}
	for f := 1; f <= 100; f++ {
		statut, valid := "actif", "valide"
		switch {
		case f > 90:
			statut, valid = "en_attente", "en_attente"
		case f > 75:
			statut = "cloturee"
		}
		prix := float64(19+rng.Intn(120)) + 0.90
		places := 8 + rng.Intn(18)
		fo := formation{id: f, prix: prix, actif: statut == "actif"}
		if statut != "en_attente" {
			nb := places/2 + rng.Intn(places/2+1)
			for len(fo.resa) < nb {
				p := 1 + rng.Intn(700)
				k := fmt.Sprintf("%d-%d", p, f)
				if !dejaResa[k] {
					dejaResa[k] = true
					fo.resa = append(fo.resa, p)
				}
			}
		}
		var d1 time.Time
		if statut == "cloturee" {
			d1 = passe()
		} else {
			d1 = futur()
		}
		v := villes[rng.Intn(len(villes))]
		formRows = append(formRows, []interface{}{
			f, titresFormation[rng.Intn(len(titresFormation))],
			"Une session pratique en petit groupe pour apprendre les gestes essentiels et repartir avec sa réalisation.",
			prix, 60 + 30*rng.Intn(8), statut, fdate(d1), d1.AddDate(0, 0, rng.Intn(2)).Format("2006-01-02"),
			places, places - len(fo.resa), v[0] + " — atelier partagé", categoriesFormation[rng.Intn(len(categoriesFormation))],
			1 + rng.Intn(15), nil, valid, nil,
			"Acquérir les bases et gagner en autonomie.", "Aucun prérequis, matériel fourni.",
			"Accueil, démonstration, mise en pratique accompagnée, temps d'échange.",
		})
		nbDates := 1 + rng.Intn(3)
		for k := 0; k < nbDates; k++ {
			idFormDate++
			formDatesRows = append(formDatesRows, []interface{}{idFormDate, f, fdate(d1.AddDate(0, 0, 7*k))})
		}
		formations = append(formations, fo)
	}
	bulk(tx, "Formations", []string{"Id_Formations", "Titre", "Description", "Prix", "Duree", "Statut", "Date_formation", "Date_fin", "Places_total", "Places_dispo", "Localisation", "Categorie", "Id_Salaries", "Id_Salarie_Animateur", "Statut_validation", "Motif_refus", "Objectifs", "Prerequis", "Programme"}, formRows)
	bulk(tx, "Formation_Dates", []string{"Id_Formation_Dates", "Id_Formations", "Date_session"}, formDatesRows)

	var resaRows, participerRows [][]interface{}
	for _, fo := range formations {
		for _, p := range fo.resa {
			resaRows = append(resaRows, []interface{}{p, fo.id, fdate(passe())})
			participerRows = append(participerRows, []interface{}{p, fo.id})
		}
	}
	bulk(tx, "Reserver_formation", []string{"Id_Particuliers", "Id_Formations", "Date_reservation"}, resaRows)
	bulk(tx, "Participer", []string{"Id_Particuliers", "Id_Formations"}, participerRows)

	type evenement struct {
		id    int
		prix  float64
		parts []int
	}
	evenements := make([]evenement, 0, 100)
	var evtRows, evtDatesRows, partEvtRows [][]interface{}
	idEvtDate := 0
	dejaPartEvt := map[string]bool{}
	for e := 1; e <= 100; e++ {
		statut := "a_venir"
		r := rng.Intn(100)
		if r >= 55 && r < 90 {
			statut = "termine"
		} else if r >= 90 {
			statut = "annule"
		}
		prix := 0.0
		if rng.Intn(100) < 40 {
			prix = float64(5 + rng.Intn(20))
		}
		cap := 20 + rng.Intn(100)
		ev := evenement{id: e, prix: prix}
		if statut != "annule" {
			nb := 5 + rng.Intn(30)
			if nb > cap {
				nb = cap
			}
			for len(ev.parts) < nb {
				p := 1 + rng.Intn(700)
				k := fmt.Sprintf("%d-%d", p, e)
				if !dejaPartEvt[k] {
					dejaPartEvt[k] = true
					ev.parts = append(ev.parts, p)
				}
			}
		}
		var d1 time.Time
		if statut == "termine" {
			d1 = passe()
		} else {
			d1 = futur()
		}
		v := villes[rng.Intn(len(villes))]
		sal := 1 + rng.Intn(15)
		evtRows = append(evtRows, []interface{}{
			e, fdate(d1), titresEvenement[rng.Intn(len(titresEvenement))],
			"Un moment convivial autour du réemploi, ouvert à toutes et tous.",
			v[0] + " — " + v[1], cap, statut, prix, sal, sal,
			categoriesEvenement[rng.Intn(len(categoriesEvenement))], 2 + rng.Intn(5), "valide", nil,
		})
		idEvtDate++
		evtDatesRows = append(evtDatesRows, []interface{}{idEvtDate, e, fdate(d1)})
		if rng.Intn(100) < 30 {
			idEvtDate++
			evtDatesRows = append(evtDatesRows, []interface{}{idEvtDate, e, fdate(d1.AddDate(0, 0, 1))})
		}
		for _, p := range ev.parts {
			partEvtRows = append(partEvtRows, []interface{}{p, e})
		}
		evenements = append(evenements, ev)
	}
	bulk(tx, "Evenements", []string{"Id_Evenements", "Date_", "Titre", "Description", "Lieu", "Capacite", "Statut", "Prix", "Id_Salaries", "Id_Salarie_Animateur", "Categorie", "Duree", "Statut_validation", "Motif_refus"}, evtRows)
	bulk(tx, "Evenement_Dates", []string{"Id_Evenement_Dates", "Id_Evenements", "Date_session"}, evtDatesRows)
	bulk(tx, "Participer_evenements", []string{"Id_Particuliers", "Id_Evenements"}, partEvtRows)

	type service struct {
		id, pro int
		prix    float64
	}
	services := make([]service, 0, 200)
	var svcRows [][]interface{}
	for s := 1; s <= 200; s++ {
		pro := 1 + rng.Intn(280)
		prix := float64(15+rng.Intn(165)) + 0.99
		svcRows = append(svcRows, []interface{}{
			s, titresServices[rng.Intn(len(titresServices))],
			"Intervention soignée par un artisan local, devis inclus dans la prestation.",
			prix, 30 + 30*rng.Intn(8), categoriesService[rng.Intn(len(categoriesService))], nil, pro, nil,
		})
		services = append(services, service{s, pro, prix})
	}
	bulk(tx, "Services", []string{"Id_Services", "Titre", "Description", "Prix", "Duree", "Categorie", "Id_Salaries", "Id_Professionnels", "archived_at"}, svcRows)

	type commande struct {
		id, svc, user int
		statut        string
		prix          float64
	}
	var commandes []commande
	var cmdRows [][]interface{}
	for c := 1; c <= 300; c++ {
		sv := services[rng.Intn(len(services))]
		user := 299 + rng.Intn(700)
		statut := "payee"
		r := rng.Intn(100)
		switch {
		case r < 30:
			statut = "terminee"
		case r < 50:
			statut = "en_cours"
		case r < 90:
			statut = "payee"
		default:
			statut = "en_attente_paiement"
		}
		var ref interface{}
		if statut != "en_attente_paiement" {
			ref = fmt.Sprintf("cs_seed_%04d", c)
		}
		cmdRows = append(cmdRows, []interface{}{
			c, sv.id, user, strings.Title(typesObjet[rng.Intn(len(typesObjet))]),
			categoriesObjet[rng.Intn(len(categoriesObjet))], "Objet à confier : voir photo et description en messagerie.",
			sv.prix, statut, fdate(passe()), ref, nil,
		})
		commandes = append(commandes, commande{c, sv.id, user, statut, sv.prix})
	}
	bulk(tx, "Commandes_Services", []string{"Id_Commandes_Services", "Id_Services", "Id_Utilisateurs", "Nom_Objet", "Categorie_Objet", "Description_Objet", "Prix", "Statut", "Date_creation", "Reference_Stripe", "Photo_Url"}, cmdRows)

	type demPresta struct {
		id, user int
		statut   string
	}
	var demandesPresta []demPresta
	var dpRows [][]interface{}
	for d := 1; d <= 200; d++ {
		user := 299 + rng.Intn(700)
		statut := "ouverte"
		r := rng.Intn(100)
		switch {
		case r < 30:
			statut = "traitee"
		case r < 40:
			statut = "en_cours"
		case r < 50:
			statut = "annulee"
		}
		v := villes[rng.Intn(len(villes))]
		dpRows = append(dpRows, []interface{}{
			d, strings.Title(typesObjet[rng.Intn(len(typesObjet))]), categoriesObjet[rng.Intn(len(categoriesObjet))],
			typesObjet[rng.Intn(len(typesObjet))], etatsAnnonce[rng.Intn(len(etatsAnnonce))],
			"Je cherche un artisan pour remettre cet objet en état.", v[0], fmt.Sprintf("%d€", 20+10*rng.Intn(15)),
			statut, fdate(passe()), user, nil, nil,
		})
		demandesPresta = append(demandesPresta, demPresta{d, user, statut})
	}
	bulk(tx, "Demandes_prestations", []string{"Id_Demandes_prestations", "Nom_objet", "Categorie", "Type_objet", "Etat", "Description", "Localisation", "Budget", "Statut", "Date_creation", "Id_Utilisateurs", "Id_Professionnels", "Photo_url"}, dpRows)

	var projRows, etapeRows [][]interface{}
	idProjet, idEtape := 0, 0
	nomsEtapes := []string{"Diagnostic", "Démontage", "Ponçage", "Traitement", "Peinture", "Remontage", "Finitions", "Contrôle qualité"}
	ajouterEtapes := func(pj int) {
		nb := 2 + rng.Intn(3)
		for k := 0; k < nb; k++ {
			idEtape++
			etapeRows = append(etapeRows, []interface{}{idEtape, nomsEtapes[(k+rng.Intn(3))%len(nomsEtapes)], "Étape réalisée à l'atelier.", nil, pj})
		}
	}
	for i := 0; i < 40; i++ {
		idProjet++
		st := []string{"en_cours", "termine", "pause"}[rng.Intn(3)]
		projRows = append(projRows, []interface{}{
			idProjet, "Rénovation " + typesObjet[rng.Intn(len(typesObjet))], "Projet interne d'atelier.",
			fdate(passe()), st, 1 + rng.Intn(280), nil, nil,
		})
		if rng.Intn(100) < 60 {
			ajouterEtapes(idProjet)
		}
	}
	svcPro := map[int]int{}
	for _, s := range services {
		svcPro[s.id] = s.pro
	}
	for _, c := range commandes {
		if c.statut == "en_attente_paiement" || rng.Intn(100) < 40 {
			continue
		}
		idProjet++
		st := "en_cours"
		if c.statut == "terminee" {
			st = "termine"
		}
		projRows = append(projRows, []interface{}{
			idProjet, "Prestation — commande #" + fmt.Sprint(c.id), "Suivi de la prestation catalogue.",
			fdate(passe()), st, svcPro[c.svc], nil, c.id,
		})
		if rng.Intn(100) < 70 {
			ajouterEtapes(idProjet)
		}
	}
	bulk(tx, "Projets", []string{"Id_Projets", "Titre", "Description", "Date_Debut", "Statut", "Id_Professionnels", "Id_Demandes_prestations", "Id_Commandes_Services"}, projRows)
	bulk(tx, "Etapes", []string{"Id_Etapes", "Nom", "Description", "Visuel", "Id_Projets"}, etapeRows)

	type conv struct {
		id, acheteur, vendeur int
	}
	var convs []conv
	var convRows [][]interface{}
	idConv := 0
	vuConvAnn := map[string]bool{}
	for _, a := range annonces {
		if idConv >= 1200 {
			break
		}
		proprio := 0
		if a.part > 0 {
			proprio = partUser(a.part)
		} else {
			proprio = proUser(a.pro)
		}
		acheteur := a.acheteur
		if acheteur == 0 {
			if a.statut != "validee" || rng.Intn(100) < 60 {
				continue
			}
			acheteur = 299 + rng.Intn(700)
			if acheteur == proprio {
				continue
			}
		}
		k := fmt.Sprintf("%d-%d", a.id, acheteur)
		if vuConvAnn[k] {
			continue
		}
		vuConvAnn[k] = true
		idConv++
		convRows = append(convRows, []interface{}{idConv, a.id, acheteur, proprio, fdate(passe()), nil, nil})
		convs = append(convs, conv{idConv, acheteur, proprio})
	}
	for _, d := range demandesPresta {
		if d.statut == "annulee" || rng.Intn(100) < 30 {
			continue
		}
		pro := 1 + rng.Intn(280)
		idConv++
		convRows = append(convRows, []interface{}{idConv, nil, d.user, proUser(pro), fdate(passe()), d.id, nil})
		convs = append(convs, conv{idConv, d.user, proUser(pro)})
	}
	for _, c := range commandes {
		if c.statut == "en_attente_paiement" {
			continue
		}
		idConv++
		convRows = append(convRows, []interface{}{idConv, nil, c.user, proUser(svcPro[c.svc]), fdate(passe()), nil, c.id})
		convs = append(convs, conv{idConv, c.user, proUser(svcPro[c.svc])})
	}
	bulk(tx, "Conversations", []string{"Id_Conversations", "Id_Annonces", "Id_Acheteur", "Id_Vendeur", "Date_creation", "Id_Demandes_prestations", "Id_Commandes_Services"}, convRows)

	var msgRows [][]interface{}
	idMsg := 0
	for _, cv := range convs {
		nb := 3 + rng.Intn(6)
		base := passe()
		for m := 0; m < nb; m++ {
			idMsg++
			exp := cv.acheteur
			if m%2 == 1 {
				exp = cv.vendeur
			}
			lu := 1
			if m == nb-1 && rng.Intn(2) == 0 {
				lu = 0
			}
			msgRows = append(msgRows, []interface{}{
				idMsg, cv.id, exp, messagesPool[(m+rng.Intn(4))%len(messagesPool)],
				fdate(base.Add(time.Duration(m*7) * time.Minute)), lu, 0, nil,
			})
		}
	}
	bulk(tx, "Messages_Conversation", []string{"Id_Messages_Conversation", "Id_Conversations", "Id_Expediteur", "Contenu", "Date_envoi", "Lu", "Est_Automatique", "Type_Evenement"}, msgRows)

	type facture struct {
		user     int
		ttc      float64
		typ, lbl string
		idForm   interface{}
		idEvt    interface{}
		idSvc    interface{}
	}
	var pool []facture
	for _, fo := range formations {
		for _, p := range fo.resa {
			if rng.Intn(100) < 60 {
				pool = append(pool, facture{partUser(p), fo.prix, "formation", "Inscription formation", fo.id, nil, nil})
			}
		}
	}
	for _, ev := range evenements {
		if ev.prix <= 0 {
			continue
		}
		for _, p := range ev.parts {
			if rng.Intn(100) < 60 {
				pool = append(pool, facture{partUser(p), ev.prix, "evenement", "Inscription événement", nil, ev.id, nil})
			}
		}
	}
	for _, a := range annonces {
		if a.statut == "vendue" && a.acheteur > 0 && a.typeAnn == "vente" {
			pool = append(pool, facture{a.acheteur, float64(5+rng.Intn(245)) + 0.99, "annonce", "Achat annonce", nil, nil, nil})
		}
	}
	for _, c := range commandes {
		if c.statut != "en_attente_paiement" {
			pool = append(pool, facture{c.user, c.prix, "prestation_catalogue", "Prestation catalogue", nil, nil, c.svc})
		}
	}
	for _, d := range demandesPresta {
		if d.statut == "traitee" || d.statut == "en_cours" {
			pool = append(pool, facture{d.user, float64(30+rng.Intn(200)) + 0.99, "devis_prestation", "Prestation sur devis", nil, nil, nil})
		}
	}
	rng.Shuffle(len(pool), func(i, j int) { pool[i], pool[j] = pool[j], pool[i] })
	if len(pool) > 1600 {
		pool = pool[:1600]
	}
	var facRows, ligneRows, paieRows [][]interface{}
	idLigne, idPaie := 0, 0
	for i, f := range pool {
		idFac := i + 1
		emise := passe()
		ttc := f.ttc
		ht := float64(int(ttc/1.2*100)) / 100
		statutF := "payee"
		r := rng.Intn(100)
		if r >= 90 && r < 96 {
			statutF = "emise"
		} else if r >= 96 {
			statutF = "annulee"
		}
		facRows = append(facRows, []interface{}{
			idFac, fmt.Sprintf("FAC-%s-%06d", emise.Format("20060102"), idFac), fdate(emise), fdate(emise.AddDate(0, 1, 0)),
			ht, 20.00, ttc, statutF, f.typ, nil, f.user,
		})
		idLigne++
		ligneRows = append(ligneRows, []interface{}{idLigne, f.lbl, 1, ht, ht, idFac, f.idForm, f.idEvt, f.idSvc})
		idPaie++
		statutP, methode := "paye", "carte"
		var dateRemb, motifRemb, refRemb interface{}
		switch statutF {
		case "emise":
			statutP = "en_attente"
		case "annulee":
			statutP = "echoue"
		default:
			rr := rng.Intn(100)
			if rr < 5 {
				statutP = "rembourse"
				dateRemb = fdate(emise.AddDate(0, 0, 10))
				motifRemb = "Annulation à la demande du client"
				refRemb = fmt.Sprintf("re_seed_%04d", idPaie)
			} else if rr < 8 {
				statutP = "remboursement_en_cours"
			}
			if rng.Intn(100) < 10 {
				methode = "virement"
			}
		}
		paieRows = append(paieRows, []interface{}{
			idPaie, fdate(emise), ttc, statutP, methode, fmt.Sprintf("cs_live_seed_%05d", idPaie),
			idFac, f.user, dateRemb, motifRemb, refRemb, fmt.Sprintf("pi_seed_%05d", idPaie),
		})
	}
	bulk(tx, "Factures", []string{"Id_Facture", "Numero_facture", "Date_emission", "Date_echeance", "Montant_HT", "TVA", "Montant_TTC", "Statut", "Type", "PDF_URL", "Id_Utilisateurs"}, facRows)
	bulk(tx, "Lignes_Facture", []string{"Id_Ligne", "Description", "Quantite", "Prix_unitaire_HT", "Total_HT", "Id_Facture", "Id_Formations", "Id_Evenements", "Id_Services"}, ligneRows)
	bulk(tx, "Paiements", []string{"Id_Paiements", "Date_", "Montant", "Statut", "Methode", "Reference_stripe", "Id_Facture", "Id_Utilisateurs", "Date_remboursement", "Motif_remboursement", "Ref_refund", "Ref_paiement_intent"}, paieRows)

	var avisRows [][]interface{}
	for i := 1; i <= 800; i++ {
		var idPro, idEvt, idForm interface{}
		part := 1 + rng.Intn(700)
		switch rng.Intn(10) {
		case 0, 1, 2, 3:
			idPro = 1 + rng.Intn(280)
		case 4, 5, 6:
			fo := formations[rng.Intn(len(formations))]
			if len(fo.resa) > 0 {
				part = fo.resa[rng.Intn(len(fo.resa))]
			}
			idForm = fo.id
		default:
			ev := evenements[rng.Intn(len(evenements))]
			if len(ev.parts) > 0 {
				part = ev.parts[rng.Intn(len(ev.parts))]
			}
			idEvt = ev.id
		}
		avisRows = append(avisRows, []interface{}{fdate(passe()), avisPool[rng.Intn(len(avisPool))], part, idPro, idEvt, idForm})
	}
	bulk(tx, "Avis", []string{"Date_du_post", "Contenu", "Id_Particuliers", "Id_Professionnels", "Id_Evenements", "Id_Formations"}, avisRows)

	var favRows [][]interface{}
	vuFav := map[string]bool{}
	for len(favRows) < 600 {
		pro := 1 + rng.Intn(280)
		ann := annonces[rng.Intn(len(annonces))]
		if ann.statut != "validee" {
			continue
		}
		k := fmt.Sprintf("%d-%d", pro, ann.id)
		if vuFav[k] {
			continue
		}
		vuFav[k] = true
		favRows = append(favRows, []interface{}{pro, ann.id})
	}
	bulk(tx, "Favoris", []string{"Id_Professionnels", "Id_Annonces"}, favRows)

	var consRows [][]interface{}
	catConseils := []string{"Tri", "Réparation", "Upcycling", "Zéro déchet"}
	for i := 1; i <= 45; i++ {
		c := conseilsPool[rng.Intn(len(conseilsPool))]
		statut := "valide"
		if i > 40 {
			statut = "en_attente"
		}
		consRows = append(consRows, []interface{}{
			i, fdate(passe()), c[0], c[1], catConseils[rng.Intn(len(catConseils))],
			"recup,astuce", statut, 1 + rng.Intn(15),
		})
	}
	bulk(tx, "Conseils", []string{"Id_Conseils", "Date_d_ajout", "Titre", "Contenu", "Categorie", "Tags", "Statut", "Id_Salaries"}, consRows)

	bulk(tx, "Forum", []string{"Id_Forum"}, [][]interface{}{{1}})
	var sujetRows, repRows [][]interface{}
	idRep := 0
	for s := 1; s <= 60; s++ {
		part := 1 + rng.Intn(700)
		statut := "ouvert"
		r := rng.Intn(100)
		if r >= 60 && r < 85 {
			statut = "resolu"
		} else if r >= 85 {
			statut = "ferme"
		}
		sujetRows = append(sujetRows, []interface{}{
			s, sujetsForum[rng.Intn(len(sujetsForum))], fdate(passe()), 1, part,
			"Je sèche sur ce projet, des retours d'expérience ?", categoriesObjet[rng.Intn(len(categoriesObjet))],
			statut, rng.Intn(400), partUser(part),
		})
		nb := 1 + rng.Intn(5)
		for m := 0; m < nb; m++ {
			idRep++
			auteur := 299 + rng.Intn(700)
			sol := 0
			if statut == "resolu" && m == nb-1 {
				sol = 1
			}
			repRows = append(repRows, []interface{}{
				idRep, messagesPool[rng.Intn(len(messagesPool))], fdate(passe()), s, nil, auteur, sol,
			})
		}
	}
	bulk(tx, "Sujets", []string{"Id_Sujets", "Titre", "Date_Creation", "Id_Forum", "Id_Particuliers", "Contenu", "Categorie", "Statut", "Vues", "Id_Utilisateurs"}, sujetRows)
	bulk(tx, "Reponses", []string{"Id_Reponses", "Contenu", "Date_", "Id_Sujets", "Id_Professionnels", "Id_Utilisateurs", "Est_Solution"}, repRows)

	if err := tx.Commit(); err != nil {
		log.Fatal(err)
	}
	fmt.Println("Seed terminé.")
}
