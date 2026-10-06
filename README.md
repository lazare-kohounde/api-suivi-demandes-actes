# API de Suivi des Demandes d'Actes Administratifs (ASIN Bénin)

API REST développée avec Laravel permettant le suivi et la gestion du cycle de vie des demandes d'actes administratifs (acte de naissance, casier judiciaire, certificat de résidence).

---

## Prérequis

- PHP 8.2+ avec l'extension SQLite (`pdo_sqlite`, `sqlite3`)
- Composer 2+

---

## Installation et Démarrage Rapide

### 1. Cloner le projet et installer les dépendances
```bash
composer install
```

### 2. Configuration de l'environnement
**Sur Linux / macOS :**
```bash
cp .env.example .env
```

**Sur Windows (PowerShell) :**
```powershell
Copy-Item .env.example .env
```

### 3. Génération de la clé d'application
```bash
php artisan key:generate
```

### 4. Création du fichier de base de données SQLite
**Sur Linux / macOS :**
```bash
touch database/database.sqlite
```

**Sur Windows (PowerShell) :**
```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
```

**Sur Windows (CMD) :**
```cmd
type nul > database\database.sqlite
```

### 5. Exécution des migrations
```bash
php artisan migrate
```

### 6. Lancement du serveur de développement
```bash
php artisan serve
```
L'API est alors accessible sur : `http://127.0.0.1:8000`

---

## Tests
Pour exécuter la suite de tests automatisés :
```bash
php artisan test
```

*(Ce README sera complété avec la documentation détaillée des endpoints et des règles de gestion à l'étape 8).*
