# API de Suivi des Demandes d'Actes Administratifs

API REST développée avec Laravel permettant le dépôt, la consultation et le suivi du cycle de vie des demandes d'actes administratifs pour les usagers béninois.  
Réalisée dans le cadre de l'**Étude de cas DEP/ASIN 2026** (Poste de Développeur Junior, ASIN Bénin).

---

## 1. Présentation

Cette application fournit une interface de programmation (API REST) et un écran web minimaliste permettant aux usagers de soumettre et suivre leurs demandes d'actes administratifs (acte de naissance, casier judiciaire, certificat de résidence), et aux agents administratifs de faire évoluer le traitement de chaque dossier selon un cycle de vie strict et irréversible.

---

## 2. Prérequis

- **PHP** : 8.2 ou supérieur (testé avec PHP 8.3 / 8.5)
- **Extensions PHP requises** : `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `tokenizer`
- **Composer** : 2.x
- Aucun serveur de base de données externe requis (SQLite autonome)
- Aucun outil de build front-end (Node.js/npm non requis pour l'exécution)

---

## 3. Installation et Démarrage Rapide

Exécutez les commandes suivantes dans l'ordre depuis la racine du projet :

### 1. Installer les dépendances du projet
```bash
composer install
```

### 2. Configurer le fichier d'environnement
**Sous Linux / macOS :**
```bash
cp .env.example .env
```
**Sous Windows (PowerShell) :**
```powershell
Copy-Item .env.example .env
```
**Sous Windows (CMD) :**
```cmd
copy .env.example .env
```

### 3. Générer la clé de sécurité de l'application
```bash
php artisan key:generate
```

### 4. Créer le fichier de base de données SQLite
**Sous Linux / macOS :**
```bash
touch database/database.sqlite
```
**Sous Windows (PowerShell) :**
```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
```
**Sous Windows (CMD) :**
```cmd
type nul > database\database.sqlite
```

### 5. Exécuter les migrations
```bash
php artisan migrate
```

### 6. Démarrer le serveur de développement
```bash
php artisan serve
```

- **Point d'entrée de l'API** : `http://127.0.0.1:8000/api`
- **Écran de consultation web** : `http://127.0.0.1:8000/`

---

## 4. Documentation de l'API

Tous les échanges s'effectuent au format JSON. Pensez à toujours inclure l'en-tête `Accept: application/json`.

### Tableau récapitulatif des endpoints

| Méthode | URL | Paramètres / Body | Succès | Codes d'erreur | Description |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **POST** | `/api/demandes` | JSON : `npi`, `type_acte`, `nombre_copies` | **201** | `422` | Dépose une nouvelle demande (statut initial forcé à `deposee`) |
| **GET** | `/api/usagers/{npi}/demandes` | Query : `statut`, `page`, `taille` (max 20) | **200** | `422` | Liste paginée des demandes de l'usager (triées du plus récent au plus ancien) |
| **PATCH** | `/api/demandes/{id}/statut` | JSON : `statut`, `motif` (optionnel/obligatoire si rejet) | **200** | `404`, `409`, `422` | Fait avancer le traitement selon le cycle de vie |
| **GET** | `/api/demandes/stats` | *Aucun* | **200** | - | Compteur global des demandes par statut (les 4 statuts toujours présents) |

---

### Exemples de requêtes cURL

#### 1. Déposer une demande (`POST /api/demandes`)
```bash
curl -i -X POST http://127.0.0.1:8000/api/demandes \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "npi": "0123456789",
    "type_acte": "acte_naissance",
    "nombre_copies": 2
  }'
```
**Réponse attendue (201 Created) :**
```json
{
  "id": 1,
  "npi": "0123456789",
  "type_acte": "acte_naissance",
  "nombre_copies": 2,
  "statut": "deposee",
  "statut_libelle": "Déposée",
  "motif_rejet": null,
  "created_at": "2026-10-06T11:46:06.000000Z",
  "updated_at": "2026-10-06T11:46:06.000000Z"
}
```

#### 2. Consulter les demandes d'un usager (`GET /api/usagers/{npi}/demandes`)
```bash
curl -i "http://127.0.0.1:8000/api/usagers/0123456789/demandes?statut=deposee&taille=10&page=1" \
  -H "Accept: application/json"
```
**Réponse attendue (200 OK) :**
```json
{
  "data": [
    {
      "id": 1,
      "npi": "0123456789",
      "type_acte": "acte_naissance",
      "nombre_copies": 2,
      "statut": "deposee",
      "statut_libelle": "Déposée",
      "motif_rejet": null,
      "created_at": "2026-10-06T11:46:06.000000Z",
      "updated_at": "2026-10-06T11:46:06.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/usagers/0123456789/demandes?page=1",
    "last": "http://127.0.0.1:8000/api/usagers/0123456789/demandes?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```
*Note : Si un NPI valide ne possède aucune demande, l'API renvoie un tableau vide `{"data": [], ...}` avec le statut HTTP 200 (jamais de 404).*

#### 3. Faire avancer le statut d'une demande (`PATCH /api/demandes/{id}/statut`)
Passage à l'étape "en_cours" :
```bash
curl -i -X PATCH http://127.0.0.1:8000/api/demandes/1/statut \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "statut": "en_cours"
  }'
```

Rejet d'une demande depuis "en_cours" avec motif :
```bash
curl -i -X PATCH http://127.0.0.1:8000/api/demandes/1/statut \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "statut": "rejetee",
    "motif": "Dossier incomplet : justificatif d identite illisible"
  }'
```

#### 4. Obtenir les statistiques globales (`GET /api/demandes/stats`)
```bash
curl -i http://127.0.0.1:8000/api/demandes/stats \
  -H "Accept: application/json"
```
**Réponse attendue (200 OK) :**
```json
{
  "deposee": 12,
  "en_cours": 4,
  "validee": 8,
  "rejetee": 2
}
```

---

### Format uniforme des erreurs

Toutes les erreurs renvoyées par l'API respectent la structure uniforme suivante :

```json
{
  "erreur": "Message explicite en français",
  "details": {
    "champ": [
      "Description du problème sur le champ"
    ]
  }
}
```

**Exemple d'erreur 422 (validation) :**
```json
{
  "erreur": "Le NPI doit comporter exactement 10 chiffres.",
  "details": {
    "npi": [
      "Le NPI doit comporter exactement 10 chiffres."
    ]
  }
}
```

**Exemple d'erreur 409 (transition interdite / saut d'étape) :**
```json
{
  "erreur": "Impossible de passer de 'deposee' à 'validee' : la demande doit d'abord être en cours de traitement."
}
```

**Exemple d'erreur 404 (ressource non trouvée) :**
```json
{
  "erreur": "Demande introuvable."
}
```

---

## 5. Cycle de Vie et Règles de Gestion

### Schéma du cycle de vie

```text
               ┌─────────────┐
               │   DEPOSEE   │
               └──────┬──────┘
                      │
                      ▼
               ┌─────────────┐
               │  EN_COURS   │
               └───┬─────┬───┘
                   │     │
         ┌─────────┘     └─────────┐
         ▼                         ▼
  ┌─────────────┐           ┌─────────────┐
  │   VALIDEE   │           │   REJETEE   │  (avec motif obligatoire)
  └─────────────┘           └─────────────┘
  (État terminal)           (État terminal)
```

### Règles de gestion non négociables
1. **Identifiant NPI** : Chaîne d'exactement 10 chiffres (`^\d{10}$`). Les zéros en début de chaîne sont obligatoirement préservés.
2. **Types d'actes acceptés** : `acte_naissance`, `casier_judiciaire`, `certificat_residence`.
3. **Nombre de copies** : Entier compris entre 1 et 5 inclus.
4. **Statut initial forcé** : Toute nouvelle demande reçoit obligatoirement le statut `deposee` côté serveur, même si un autre statut est passé dans la requête.
5. **Aucun saut d'étape** : Une demande au statut `deposee` ne peut pas passer directement à `validee` ou `rejetee` (HTTP 409).
6. **États terminaux irréversibles** : Une demande passée au statut `validee` ou `rejetee` est définitivement scellée. Toute tentative ultérieure de modification est refusée (HTTP 409).
7. **Motif de rejet obligatoire** : Le statut `rejetee` impose un motif non vide (après trim). Pour une validation ou une mise en cours, tout motif envoyé est automatiquement ignoré.

---

## 6. Modèle de Données et Choix de Conception

### Table `demandes`
- `id` : Identifiant unique auto-incrémenté.
- `npi` : `VARCHAR(10)` (type chaîne afin d'éviter la troncature des zéros préfixes).
- `type_acte` : `VARCHAR` casté vers l'enum `TypeActe`.
- `nombre_copies` : `TINYINT UNSIGNED` (entier de 1 à 5).
- `statut` : `VARCHAR` (défaut `deposee`), casté vers l'enum `StatutDemande`.
- `motif_rejet` : `TEXT` nullable (uniquement renseigné en cas de rejet).
- `created_at`, `updated_at` : Timestamps ISO 8601.
- **Indexation** : Index composite `(npi, created_at)` pour garantir des performances optimales lors de la consultation ordonnée des dossiers d'un usager.

### Choix d'architecture
- **Enums PHP natifs (PHP 8.2+)** : Centralisation complète du cycle de vie et des messages de transition dans l'enum `StatutDemande`. Les contrôleurs restent très fins sans logique métier dupliquée.
- **FormRequests dédiés** : `StoreDemandeRequest`, `ListeDemandesRequest`, `UpdateStatutRequest` encapsulent la validation avec des messages d'erreur en français clair.
- **Centralisation des exceptions dans `bootstrap/app.php`** : Format d'erreur uniforme (`{"erreur": "...", "details": {...}}`) garanti pour les codes 422, 404 (`ModelNotFoundException`) et 409 (`TransitionInterditeException`).
- **Agrégation SQL pour les statistiques** : `Demande::selectRaw('statut, count(*) as total')->groupBy('statut')->pluck('total', 'statut')` plutôt qu'un comptage en mémoire PHP.
- **SQLite** : Zéro dépendance et zéro configuration nécessaire pour l'évaluation par le jury.

---

## 7. Lancement des Tests Automatisés

Le projet comprend une suite complète de **18 tests fonctionnels** (PHPUnit) avec base de données SQLite en mémoire (`:memory:`) :

```bash
php artisan test
```

### Couverture des tests (`tests/Feature/`) :
- **`DepotDemandeTest`** : Dépôt valide, validation stricte du NPI (longueurs, caractères), types d'actes inconnus, copies limites (0, 1, 5, 6), protection contre l'injection de statut.
- **`ConsultationDemandesTest`** : Tri antichronologique (`created_at desc`, `id desc`), filtrage par statut, NPI inexistant (200 avec tableau vide), isolation des dossiers entre usagers, pagination (limite 20, rejet à 21, page suivante).
- **`CycleDeVieTest`** : Transitions valides, interdiction des sauts d'étapes (409), immutabilité des états terminaux (409), rejet sans motif (422), nettoyage du motif sur validation, demandes inconnues (404).
- **`StatistiquesTest`** : Présence systématique des 4 statuts avec les valeurs exactes, y compris lorsque le compteur est à 0.

---

## 8. État d'Avancement du Projet

### Récapitulatif :
- **Socle obligatoire (10 pts)** : **100% RÉALISÉ**
  - Dépôt de demande avec statut `deposee` garanti
  - Consultation chronologique avec filtres
  - Cycle de vie strict et gestion des transitions
- **Bonus réalisés (2 pts)** : **100% DES 4 BONUS RÉALISÉS**
  1. Pagination native des demandes (taille configurable, max 20)
  2. Compteur global par statut avec SQL GROUP BY (4 statuts garantis)
  3. Tests automatisés complets des règles de gestion (18 tests verts)
  4. Interface web Blade accessible sur `/` avec fetch JS asynchrone (sécurisée avec `textContent`, zéro framework/build)
- **Bonus manquants** : **AUCUN**

### Pistes d'amélioration pour la production :
1. **Authentification & Habilitations (RBAC)** : Intégration de Laravel Sanctum pour sécuriser l'endpoint `PATCH /api/demandes/{id}/statut` réservé aux seuls agents habilités.
2. **Journal d'audit (Audit Trail)** : Création d'une table `demande_historiques` traçant chaque changement d'état avec l'identifiant de l'agent, l'adresse IP et l'horodatage exact.
3. **Notifications usagers** : Déclenchement d'événements et de files d'attente pour avertir l'usager par SMS / email lors de la validation ou du rejet de sa demande.
