# SaaS Gestion Stock & Shifts 🍽️

Plateforme SaaS pour la gestion de restaurants : stocks, employés, plannings (shifts) et notifications.

## 📚 Stack Technique

### Backend (API)
- **Framework** : Symfony 7 + API Platform 3
- **Base de données** : SQLite (par défaut pour le dev), compatible PostgreSQL/MySQL
- **Authentification** : JWT (LexikJWTAuthenticationBundle)
- **Architecture** : DDD Lite, PSR-12, SOLID

### Frontend (Client)
- **Framework** : Vue.js 3 (Composition API) + Vite + Pinia
- **Style** : Tailwind CSS (Classes utilitaires via CSS standard pour l'instant)

---

## 🚀 Prérequis

Assurez-vous d'avoir installé sur votre machine :
- **PHP** 8.2+
- **Composer**
- **Node.js** 18+ & **npm**

---

## 🛠️ Installation Complète (Premier Lancement)

### 1. Clonage du projet
```bash
git clone <votre-repo-url>
cd natif
```

### 2. Configuration du Backend
```bash
cd backend

# 1. Installation des dépendances PHP
php composer.phar install

# 2. Configuration de l'environnement
# (Le fichier .env est déjà configuré pour SQLite par défaut)

# 3. Génération des clés JWT (Pour l'authentification)
php bin/console lexik:jwt:generate-keypair

# 4. Création de la Base de Données & Schéma
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction

# 5. Chargement des Données de Test (Fixtures)
php bin/console doctrine:fixtures:load --no-interaction
```

### 3. Configuration du Frontend
```bash
cd ../frontend

# Installation des dépendances JS
npm install
```

---

## ▶️ Lancement du Projet en Local

Il est recommandé d'ouvrir **deux terminaux** différents.

### Terminal 1 : Backend (API)
```bash
cd backend
php -S localhost:8000 -t public
```
> ✅ L'API sera accessible sur : `http://localhost:8000/api`

### Terminal 2 : Frontend (Interface)
```bash
cd frontend
npm run dev
```
> ✅ Le Dashboard sera accessible sur : `http://localhost:5173`

> **Note technique** : Le frontend utilise un **Proxy** (via Vite) pour rediriger automatiquement les requêtes `/api` vers `localhost:8000`. Cela évite les problèmes de CORS.

---

## 🔑 Identifiants de Démonstration

Une fois les fixtures chargées, vous pouvez vous connecter avec ces utilisateurs :

| Rôle | Email | Mot de passe | Restaurant |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@natif.com` | `password` | (Tous) |
| **Manager** | `manager@natif.com` | `password` | La Bella Vita 🍕 |
| **Manager** | `manager2@natif.com` | `password` | Sushi Tokyo 🍣 |
| **Employé** | `employee@natif.com` | `password` | La Bella Vita |
| **Employé** | `employee2@natif.com` | `password` | Sushi Tokyo |

> ⚠️ **Multi-Tenant**: Chaque Manager/Employé ne voit que les données de **son propre restaurant**. L'Admin voit tout.

---

## 📂 Structure du Projet

- **`/backend`**
  - `src/Entity` : Modèle de données (User, Restaurant, Product...)
  - `src/State` : Logique métier API Platform (ex: Hashage mot de passe)
  - `src/DataFixtures` : Données de test
- **`/frontend`**
  - `src/views` : Pages principales (Login, Dashboard, Manager/Products...)
  - `src/stores` : Gestion d'état global (Auth, User) avec Pinia
  - `src/services` : Configuration Axios (Appels API)

---

## 🐛 Dépannage Rapide

**Problème : "Network Error" ou "CORS Error" lors du login ?**
- Vérifiez que le backend tourne bien sur le port **8000**.
- Vérifiez que vous accédez au frontend via **localhost:5173** (le proxy est configuré pour ça).
- Si le backend a planté, relancez la commande `php -S localhost:8000 -t public`.

**Problème : Impossible de se connecter (Bad Credentials) ?**
- Assurez-vous d'avoir lancé les fixtures : `php bin/console doctrine:fixtures:load -n`.
