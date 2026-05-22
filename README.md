# 🏠 E-Loyer - Plateforme de Location Longue Durée au Gabon

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?style=flat-square&logo=tailwind-css)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)

E-Loyer est une application web de location immobilière longue durée (minimum 1 mois) spécifiquement adaptée au marché gabonais, avec intégration des solutions de paiement Mobile Money locales.

## ✨ Fonctionnalités Principales

### 👥 Types d'Utilisateurs

| Type | Description |
|------|-------------|
| **Locataire** | Recherche et réserve des logements, paie via Mobile Money |
| **Propriétaire** | Gère ses biens, valide les réservations, reçoit les paiements |
| **Démarcheur** | Prospecte des biens, gagne 5% de commission |
| **Administrateur** | Valide les annonces, modère la plateforme |

### 🏡 Gestion des Propriétés
- Création d'annonces avec galerie photos
- Types: Appartement, Maison, Studio, Villa, Chambre
- Équipements détaillés (WiFi, Parking, Climatisation, etc.)
- Géolocalisation sur carte
- Système de favoris

### 💰 Paiement Mobile Money
- **Airtel Money**
- **Moov Money**
- **Gabon Telecom Cash**
- Paiement initial (1er mois + caution)
- Paiements mensuels récurrents

### 📊 Dashboards Personnalisés
- Tableau de bord Locataire
- Tableau de bord Propriétaire avec statistiques
- Tableau de bord Démarcheur avec suivi des commissions
- Panel Admin complet

### 💬 Communication
- Messagerie en temps réel
- Notifications
- Système d'avis et notations

## 🚀 Installation

### Prérequis
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js 18+ & NPM

### Étapes d'Installation

```bash
# 1. Cloner le projet
git clone https://github.com/votre-repo/e-loyer.git
cd e-loyer

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier d'environnement
cp .env.example .env

# 4. Générer la clé d'application
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_DATABASE=e_loyer
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Créer la base de données
mysql -u root -e "CREATE DATABASE e_loyer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Exécuter les migrations et seeders
php artisan migrate --seed

# 8. Créer le lien de stockage
php artisan storage:link

# 9. Installer les dépendances frontend
npm install

# 10. Compiler les assets
npm run build

# 11. Lancer le serveur
php artisan serve
```

L'application est accessible sur `http://localhost:8000`

## 🔐 Comptes de Test

| Type | Email | Mot de passe |
|------|-------|--------------|
| Admin | admin@e-loyer.ga | password |
| Propriétaire | jean.moussavou@email.com | password |
| Locataire | francois.ella@email.com | password |
| Démarcheur | albert.bibang@email.com | password |

## 📁 Structure du Projet

```
e-loyer/
├── app/
│   ├── Http/Controllers/       # Contrôleurs
│   │   ├── Admin/              # Admin controllers
│   │   ├── Auth/               # Authentification
│   │   ├── Owner/              # Espace propriétaire
│   │   ├── Prospector/         # Espace démarcheur
│   │   └── Tenant/             # Espace locataire
│   ├── Models/                 # Modèles Eloquent
│   ├── Policies/               # Politiques d'autorisation
│   └── Services/               # Services (Mobile Money)
├── database/
│   ├── migrations/             # Migrations
│   └── seeders/                # Données de test
├── resources/
│   ├── css/                    # Styles (Tailwind)
│   ├── js/                     # Scripts
│   └── views/                  # Vues Blade
│       ├── admin/              # Vues admin
│       ├── auth/               # Authentification
│       ├── bookings/           # Réservations
│       ├── dashboard/          # Dashboards
│       ├── layouts/            # Layouts
│       ├── messages/           # Messagerie
│       ├── properties/         # Propriétés
│       └── components/         # Composants réutilisables
└── routes/
    └── web.php                 # Routes web
```

## 🎨 Identité Visuelle

Couleurs inspirées du drapeau gabonais :
- **Vert** : `#009639` (couleur principale)
- **Jaune** : `#FCD116` (accents)
- **Bleu** : `#3A75C4` (éléments secondaires)

## 💰 Modèle Économique

| Source | Taux |
|--------|------|
| Commission Plateforme | 12% par transaction |
| Commission Démarcheur | 5% par réservation |

## 🔧 Configuration Mobile Money

### Airtel Money
```env
AIRTEL_CLIENT_ID=votre_client_id
AIRTEL_CLIENT_SECRET=votre_client_secret
AIRTEL_API_URL=https://openapiuat.airtel.africa
```

### Moov Money
```env
MOOV_API_KEY=votre_api_key
MOOV_API_SECRET=votre_api_secret
```

### Gabon Telecom Cash
```env
GABON_TELECOM_API_KEY=votre_api_key
```

## 🗺️ Configuration Google Maps

```env
GOOGLE_MAPS_API_KEY=votre_api_key
```

## 📝 Commandes Utiles

```bash
# Développement
composer dev                   # Lancer serveur + queue + vite

# Base de données
php artisan migrate:fresh --seed   # Réinitialiser la DB

# Cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Tests
php artisan test
```

## 🛣️ Routes Principales

| Route | Description |
|-------|-------------|
| `/` | Page d'accueil |
| `/proprietes` | Liste des propriétés |
| `/proprietes/{id}` | Détail d'une propriété |
| `/tableau-de-bord` | Dashboard (selon le rôle) |
| `/messages` | Messagerie |
| `/admin/*` | Administration |

## 📱 Responsive Design

L'application est entièrement responsive :
- **Mobile** : Optimisé pour l'utilisation principale
- **Tablette** : Interface adaptée
- **Desktop** : Expérience complète

## 🔒 Sécurité

- Protection CSRF
- Validation des entrées
- Politiques d'autorisation (Policies)
- Middleware d'authentification
- Middleware de type d'utilisateur

## 📈 Évolutions Futures

- [ ] Application mobile (Flutter/React Native)
- [ ] Chat en temps réel (WebSockets)
- [ ] Visites virtuelles 360°
- [ ] Programme de fidélité
- [ ] IA pour recommandations

## 🤝 Contribution

1. Fork le projet
2. Créer une branche (`git checkout -b feature/ma-fonctionnalite`)
3. Commit (`git commit -m 'Ajout de ma fonctionnalité'`)
4. Push (`git push origin feature/ma-fonctionnalite`)
5. Créer une Pull Request

## 📄 Licence

Ce projet est sous licence MIT.

## 👨‍💻 Développé par

Projet académique - Application de location immobilière au Gabon

---

<p align="center">
  <img src="https://via.placeholder.com/150x50/009639/ffffff?text=E-Loyer" alt="E-Loyer">
  <br>
  <strong>E-Loyer</strong> - La location simplifiée au Gabon 🇬🇦
</p>
