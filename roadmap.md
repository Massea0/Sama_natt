# Roadmap – Sama Natt (Arcadis Tech) – Backend + Front

> Objectif de ce document: fournir un état **exhaustif** du projet (contexte produit, décisions d’architecture, ce qui est déjà fait, ce qui reste à faire, et comment relancer/dev/tester) afin qu’un autre chat/agent puisse reprendre le travail uniquement à partir de ce fichier.

## 0) TL;DR (état actuel)

- Base technique: **Laravel 11** (PHP) + UI web existante pilotée par **Jaxon**.
- Produit “Sama Natt” (MVP mobile): iOS d’abord, UX très simple, paiements via **Dexchange** (Wave + Orange Money), **KYC requis avant payout**.
- API mobile v1: `/api/v1/*` en JSON, indépendante de Jaxon.
- **Tâches backend terminées et validées**:
  - Tâche 01: architecture API v1 (squelette + conventions) – commit `be949e9a`
  - Tâche 02: auth mobile Sanctum (login/logout + tests) – commit `b02dadcc`
  - Tâche 03: tenant scoping renforcé (session→round + tests) – commit `dcd346fd`
- Tests API v1: passent avec Postgres local/Docker (voir commandes plus bas).
- Prochaine intention: **pause backend** et démarrage **front** (app mobile / UX simple), tout en conservant une backlog backend détaillée.

---

## 1) Contexte produit (MVP)

Source principale: `Plan/PRD.md`.

### 1.1 Contraintes non négociables
- Cible: tontines **familiales/informelles** (confiance sociale existante, besoin de traçabilité).
- Plateformes: **iOS d’abord** (Android plus tard).
- Paiements: **Wave + Orange Money uniquement**, via **Dexchange**.
- KYC: **obligatoire avant payout** (réception d’argent), avec possible flexibilité admin/backoffice.
- UX: **faible littératie** → le parcours doit être minimal, clair, et robuste au réseau instable.

### 1.2 Parcours MVP (résumé)
- Membre (jour J): voir statut → payer via Wave/OM → voir confirmation + “qui reçoit”.
- Admin: créer tontine → ajouter membres → clôturer une période → déclencher payout (si KYC OK).

---

## 2) Architecture du repo (à connaître avant de coder)

### 2.1 Stack
- Backend: Laravel 11
- Auth mobile: Laravel Sanctum (tokens)
- UI web existante: Jaxon (AJAX serveur)
- Services métier: principalement dans `src/Service/**` (namespace `Siak\Tontine\`)

### 2.2 Web (Jaxon) vs API mobile
- Web:
  - Endpoint AJAX Jaxon: `/ajax` (config `config/jaxon.php`)
  - Les composants Jaxon sont sous `ajax/` et autochargés via Composer (PSR-4 `Ajax\` → `ajax/`).
  - Le “tenant context” web (guild/round) est alimenté via databag Jaxon `tenant`.
- Mobile API:
  - Doit être **indépendante de Jaxon**.
  - Le “tenant context” est **explicite dans l’URL** et appliqué via middleware `api.tenant`.

### 2.3 Tenant context (définition)
- `guild` ≈ tontine / groupe
- `round` ≈ cycle / round
- `session` ≈ meeting / séance

Le tenant context est centralisé dans `Siak\Tontine\Service\TenantService`.

---

## 3) Ce qui a été fait (chronologie détaillée)

### 3.1 Documentation / planification
Commit: `92fc2823` (docs)
- Dossier `Plan/` créé avec:
  - `Plan/PRD.md`
  - `Plan/00-recherche-tontines-senegal.md`
  - `Plan/01-...` à `Plan/09-...`
  - `Plan/AGENT-INSTRUCTIONS.md`, `Plan/AGENT-PROMPTS.md`
- Dossier `Agent feedback/` avec templates et rapports (01–03).

### 3.2 Tâche 01 – Architecture API mobile v1
Commit: `be949e9a` (feat(api): add mobile API v1 architecture)
- Ajout d’une API REST versionnée `/api/v1`.
- Ajout du middleware `api.tenant` (alias enregistré) et d’un premier set de controllers/resources/tests.
- Endpoints v1 posés (health/me/guilds/rounds/sessions…) + tests Feature.

### 3.3 Tâche 02 – Auth mobile (Sanctum)
Commit: `b02dadcc` (feat(api): add v1 auth login/logout)
- Ajout de `POST /api/v1/auth/login` (public, throttle `5,1`)
- Ajout de `POST /api/v1/auth/logout` (protégé `auth:sanctum`)
- Ajout du contrôleur `app/Http/Controllers/Api/V1/AuthController.php`
- Ajout de tests `tests/Feature/Api/V1/AuthTest.php`
- Rapport: `Agent feedback/02-auth-mobile-sanctum.md`

**Contrat login (actuel):**
- Body: `{ "email": "...", "password": "...", "device_name": "iPhone 15" }`
- Réponse 200: `{ data: { token: "...", user: {...} } }`
- Réponse 401 (invalid credentials): `{ error: { code: "invalid_credentials", message: "..." } }`

### 3.4 Tâche 03 – Tenant scoping API (guild/round/session)
Commit: `dcd346fd` (Tighten api tenant scoping)
- Renforcement du middleware `app/Http/Middleware/ApiTenant.php`:
  - Validation **session → round**: si une route inclut `{session}`, on vérifie que la session est bien dans le round du chemin, sinon `404 not_found`.
- Ajout de tests `tests/Feature/Api/V1/TenantScopeTest.php` couvrant:
  - mismatch round/guild
  - mismatch session/round
- Rapport: `Agent feedback/03-tenant-scoping-api.md`

### 3.5 Validation tests (état actuel)
- Les tests API v1 passent avec Postgres.
- Reste: 4 dépréciations vendor sous PHP 8.5 (`akaunting/laravel-money`) → non bloquant, mais à surveiller.

---

## 4) API mobile v1 – contrat actuel (backend)

### 4.1 Base
- Base URL: `/api/v1`
- Headers:
  - `Accept: application/json`
  - Auth: `Authorization: Bearer <token>`

### 4.2 Endpoints existants (implémentés)
Fichier source: `routes/api.php`

Public:
- `GET  /api/v1/health`
- `POST /api/v1/auth/login` (throttle)

Protégés (`auth:sanctum`):
- `POST /api/v1/auth/logout`
- `GET  /api/v1/me`
- `PUT  /api/v1/me`
- `GET  /api/v1/me/guilds`

Tenant-scoped (`auth:sanctum` + `api.tenant`):
- `GET /api/v1/guilds/{guild}`
- `GET /api/v1/guilds/{guild}/members`
- `GET /api/v1/guilds/{guild}/rounds`
- `GET /api/v1/guilds/{guild}/rounds/{round}`
- `GET /api/v1/guilds/{guild}/rounds/{round}/sessions`
- `GET /api/v1/guilds/{guild}/rounds/{round}/sessions/{session}`
- `GET /api/v1/guilds/{guild}/rounds/{round}/sessions/{session}/beneficiary`

### 4.3 Conventions JSON
- Succès: `{ "data": ... }` ou `{ "data": ..., "meta": ... }`
- Erreur: `{ "error": { "code": "...", "message": "..." } }`

---

## 5) Dev & exécution (backend)

### 5.1 Prérequis
- PHP: `^8.2` (le repo tourne sur 8.4/8.5)
- Extensions: `ext-intl` (obligatoire), `ext-gmp`, `ext-json`
- Composer
- Docker (recommandé pour Postgres et Chromium)

### 5.2 Installation
- `composer install`
- `php artisan key:generate`
- Copier `.env.example` → `.env` et configurer DB

### 5.3 Docker (environnement dev)
Fichier: `docker/docker-compose.yml`
- Service Postgres: `tontine-postgres` (mot de passe: `tontine`)
- Service Chromium headless: `tontine-chromium` (PDF)

### 5.4 Lancer le serveur
- `php artisan serve` (ou `composer run dev` si front web)

### 5.5 Tests (IMPORTANT: DB)
Le repo ne versionne pas `.env.testing`. Pour lancer les tests, injecter les variables DB.

Exemple (Postgres sur `127.0.0.1:5432`):

```bash
DB_CONNECTION=tontine \
DB_HOST=127.0.0.1 \
DB_PORT=5432 \
DB_DATABASE=postgres \
DB_USERNAME=postgres \
DB_PASSWORD=tontine \
./vendor/bin/phpunit --testdox tests/Feature/Api/V1
```

Notes:
- PHPUnit 11: ne pas utiliser `-v` (option inconnue). Utiliser `--testdox` ou `--debug`.

---

## 6) Roadmap backend (backlog extrêmement détaillé)

> Intention actuelle: pause backend. Mais ce backlog est prêt pour reprise immédiate.

### 6.1 Tâche 04 – Intégration paiements Dexchange (PAUSÉE)
Fichier: `Plan/04-paiements-dexchange-integration.md`

Objectif:
- Encaissement cotisations (CASHOUT): `WAVE_SN_CASHOUT`, `OM_SN_CASHOUT`
- Décaissement payouts (CASHIN): `WAVE_SN_CASHIN`, `OM_SN_CASHIN`

Livrables attendus (techniques):
1) **Client HTTP** `DexchangeClient`
   - Config via `config/services.php` + env (API key/secret, base URL)
   - Timeouts, retries (uniquement idempotents)
   - Mapping erreurs (notamment `409 externalTransactionId already used`)
2) **Modélisation DB** (migrations)
   - Table (suggestion): `payment_transactions`
   - Champs minimum:
     - `id` (ULID)
     - `provider` = "dexchange"
     - `external_transaction_id` (unique)
     - `provider_transaction_id` (transactionId)
     - `service_code` (Wave/OM + cashin/cashout)
     - `amount`, `fee`, `currency`
     - `msisdn`/numéro, métadonnées
     - `status` (pending/processing/success/failed/cancelled)
     - `raw_init_payload`, `raw_last_status_payload`
     - timestamps
3) **API endpoints (mobile)**
   - Init cotisation: POST (sur une session) → retourne transaction + statut
   - Init payout: POST (sur un bénéficiaire) → retourne transaction + statut
   - Statut: GET (par id interne)
4) **Idempotence**
   - `externalTransactionId` stable côté serveur.
   - Si init reçoit 409, retrouver la transaction existante et renvoyer l’état connu.
5) **Tests**
   - Unit tests: mapping statuts Dexchange → statuts internes
   - Feature tests: init idempotent, erreurs, permissions

Definition of Done:
- Init fonctionne avec clés sandbox/prod.
- Les transactions sont persistées et consultables.

### 6.2 Tâche 05 – Webhooks Dexchange
Fichier: `Plan/05-webhooks-dexchange.md`

Objectif:
- Endpoint webhook public idempotent.

Livrables attendus:
1) Route: `POST /api/v1/payments/dexchange/webhook`
2) Sécurisation:
   - si Dexchange offre signature/secret: valider
   - sinon: stratégie alternative: validation payload + **double-check serveur** via endpoint statut (`GET /transaction/{transactionId}`) avant d’acter.
3) Déduplication:
   - table `payment_webhook_events` (ou équivalent) avec unique sur (provider_event_id) ou (transactionId + status + timestamp)
4) Mise à jour transaction interne (state machine)

Definition of Done:
- Un webhook rejoué ne duplique rien.
- Logs corrélés via `externalTransactionId`.

### 6.3 Tâche 06 – Workflow KYC (avant payout)
Fichier: `Plan/06-kyc-workflow.md`

Objectif:
- Empêcher tout payout si KYC != approved.

Livrables attendus:
1) DB:
   - Table `kyc_submissions` (liée à user)
   - Champs: status, données d’identité minimales, raisons de rejet, timestamps
2) Stockage fichiers:
   - Recto/verso pièce, selfie (optionnel)
   - Driver: local en dev, S3 en prod (selon config)
3) API mobile:
   - `POST /api/v1/kyc/submit`
   - `GET  /api/v1/kyc/status`
4) API admin/backoffice:
   - `POST /api/v1/admin/kyc/{id}/approve`
   - `POST /api/v1/admin/kyc/{id}/reject`
5) Gating payout:
   - Lors d’un init payout: refuser (403/422) si KYC pas approved.
6) Audit log (lié à tâche 09)

Definition of Done:
- KYC submission créée, statut consultable.
- Approve/reject trace un événement.

### 6.4 Tâche 07 – Flux core tontine
Fichier: `Plan/07-tontine-core-flows.md`

Objectif:
- Exposer les flux métier essentiels via API.

Livrables attendus:
- Création tontine (guild)
- Ajout membres
- Gestion rounds/sessions (selon modèle existant)
- Calcul bénéficiaire déterministe
- Close period

Definition of Done:
- Un admin peut créer et gérer la tontine via API.

### 6.5 Tâche 08 – Notifications
Fichier: `Plan/08-notifications-rappels.md`

Objectif:
- Jobs idempotents pour rappels paiement + annonce bénéficiaire.

Livrables attendus:
- Events/Listeners + jobs de queue
- (Option MVP) stockage notifications in-app sans provider push au début

### 6.6 Tâche 09 – Audit log
Fichier: `Plan/09-audit-log.md`

Objectif:
- Traçabilité exploitable pour litiges.

Livrables attendus:
- Table `audit_events`
- Service `AuditLogger`
- Écrire un event pour chaque action critique (paiement, payout, override, KYC, règles)

---

## 7) Roadmap front (priorité actuelle)

> Le backend fournit déjà une base: login, tenant scoping, lecture du profil + tontines + sessions.

### 7.1 Hypothèses front (à confirmer)
- Front principal = **app iOS** (SwiftUI ou React Native/Expo – à décider).
- UX très simple: 1 action par écran, textes courts, états visuels.

### 7.2 Contrat d’intégration (minimum)
- Login (email/password/device_name) → stocker `token` en secure storage.
- Appels suivants avec header `Authorization: Bearer <token>`.
- Gestion erreurs:
  - 401 → écran login
  - 404 tenant mismatch → message “Accès non autorisé ou ressource introuvable”
  - 422 validation → afficher champs manquants

### 7.3 Écrans MVP (proposés, alignés PRD)
1) **Login**
   - Champs simples + bouton “Se connecter”
2) **Accueil / Aujourd’hui**
   - Statut: “Je dois payer” / “Je suis à jour”
   - Bouton principal: “Payer” (quand la tâche paiements sera implémentée)
3) **Mes tontines**
   - Appel: `GET /api/v1/me/guilds`
4) **Détails tontine**
   - Appel: `GET /api/v1/guilds/{guild}`
   - Membres: `GET /api/v1/guilds/{guild}/members`
5) **Détails round + sessions**
   - Rounds: `GET /api/v1/guilds/{guild}/rounds`
   - Sessions: `GET /api/v1/guilds/{guild}/rounds/{round}/sessions`
6) **Détail session**
   - `GET /api/v1/guilds/{guild}/rounds/{round}/sessions/{session}`
   - Bénéficiaire: `GET /api/v1/guilds/{guild}/rounds/{round}/sessions/{session}/beneficiary`

### 7.4 Front – Définition de Done (phase 1)
- Auth OK (login/logout), token stocké et réutilisé.
- Navigation: me → guilds → rounds → sessions.
- Gestion des erreurs réseau + état “chargement” + retry.

---

## 8) Questions ouvertes (à garder visibles)

Auth:
- Faut-il une expiration Sanctum + refresh token ? (actuellement tokens sans expiration par défaut)

Paiements Dexchange:
- Stratégie signature/secret webhook disponible ?
- Mapping exact des statuts Dexchange → statuts internes.

KYC:
- Quelles données minimales acceptables pour le MVP (champs + pièces) ?
- Rôle exact “admin/backoffice” dans l’application (UI web existante vs endpoints admin).

---

## 9) Index rapide (fichiers clés)

- Product/plan:
  - `Plan/PRD.md`
  - `Plan/AGENT-INSTRUCTIONS.md`
  - `Plan/AGENT-PROMPTS.md`
- API v1:
  - `routes/api.php`
  - `app/Http/Middleware/ApiTenant.php`
  - `app/Http/Controllers/Api/V1/*`
  - `app/Http/Resources/Api/V1/*`
- Tests API v1:
  - `tests/Feature/Api/V1/*`
- Web (Jaxon):
  - `ajax/`
  - `config/jaxon.php`

