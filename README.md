# Klaimy - Plateforme SaaS de Facturation Intelligente pour l'Afrique

Klaimy est une plateforme SaaS complète destinée à la facturation intelligente, l'encaissement digital et le e-recouvrement pour les entreprises africaines.

## Stack Technique

- **Backend**: PHP 8.3+ (Architecture MVC)
- **Frontend**: HTML5, CSS3, JavaScript Vanilla, Bootstrap 5
- **Base de données**: MySQL 8
- **Serveur**: Apache

## Fonctionnalités

- Facturation professionnelle conforme OHADA
- Devis avec conversion en factures
- Bons de livraison
- Paiement Mobile Money (MTN, Orange) et Carte via CinetPay
- E-recouvrement automatisé (relances J-7 à J+30)
- QR Code de paiement sur chaque facture
- Messagerie interne (équipe + clients)
- Dashboard avec graphiques et KPIs
- Gestion clients, produits, stocks
- Multi-devises (XAF, XOF, USD, EUR, GBP)
- Multi-utilisateurs avec rôles et permissions
- Double authentification (2FA par email)
- Panel Super Admin
- Plans d'abonnement (Gratuit → Enterprise)

## Installation

### Prérequis

- PHP 8.3+
- MySQL 8
- Apache avec mod_rewrite
- Extensions PHP: pdo_mysql, mbstring, xml, curl, gd, zip, intl, bcmath

### Setup

```bash
# 1. Cloner le projet
git clone https://github.com/stptsbrg/klaimy.git
cd klaimy

# 2. Copier la config
cp .env.example .env

# 3. Configurer .env (base de données, etc.)

# 4. Créer la base de données
mysql -u root < database/schema.sql

# 5. Configurer Apache (DocumentRoot → /chemin/vers/klaimy/public)

# 6. Ou utiliser le serveur PHP intégré pour le développement
php -S localhost:8080 -t public
```

### Configuration Apache (VHost)

```apache
<VirtualHost *:80>
    ServerName klaimy.local
    DocumentRoot /var/www/klaimy/public
    <Directory /var/www/klaimy/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Structure du projet

```
klaimy/
├── app/
│   ├── Config/         # Configuration et routes
│   ├── Controllers/    # Contrôleurs MVC
│   ├── Core/          # Framework (App, Router, Controller, Model, Database)
│   ├── Helpers/       # Fonctions utilitaires
│   ├── Middleware/     # Auth, CSRF, Admin
│   ├── Models/        # Modèles de données
│   └── Views/         # Templates PHP
│       ├── layouts/   # Layouts (app, auth, admin)
│       ├── auth/      # Pages d'authentification
│       ├── dashboard/ # Tableau de bord
│       ├── clients/   # Gestion clients
│       ├── products/  # Produits & services
│       ├── quotes/    # Devis
│       ├── invoices/  # Factures
│       ├── payments/  # Paiements
│       ├── messages/  # Messagerie
│       ├── notifications/
│       ├── settings/  # Paramètres
│       └── admin/     # Panel admin
├── database/
│   └── schema.sql     # Script SQL complet
├── public/
│   ├── index.php      # Point d'entrée
│   ├── .htaccess      # Réécriture URL + sécurité
│   ├── css/
│   ├── js/
│   └── uploads/
└── storage/
    ├── logs/
    ├── cache/
    └── sessions/
```

## Comptes par défaut

**Super Admin**: admin@klaimy.com (mot de passe à définir dans le script SQL)

## Plans d'abonnement

| Plan | Prix/mois | Factures | Utilisateurs |
|------|-----------|----------|-------------|
| Gratuit | 0 FCFA | 10/mois | 1 |
| Starter | 2 500 FCFA | 100/mois | 1 |
| Pro | 7 500 FCFA | Illimité | 2 |
| Business | 15 000 FCFA | Illimité | 10 |
| Enterprise | 50 000 FCFA | Illimité | Illimité |

## Hébergement

Compatible avec OVH Cloud (mutualisé ou VPS) avec Apache + PHP + MySQL.

## Sécurité

- Protection CSRF sur tous les formulaires
- Protection XSS (htmlspecialchars sur tous les outputs)
- Protection SQL Injection (requêtes préparées PDO)
- Protection Brute Force (verrouillage après 5 tentatives)
- Mots de passe hashés avec Argon2ID
- Sessions sécurisées avec régénération d'ID
- Headers de sécurité (X-Frame-Options, X-XSS-Protection, etc.)

## Licence

Propriétaire - Klaimy
