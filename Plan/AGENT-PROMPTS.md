# Prompts agents (par tâche)

Chaque prompt est conçu pour être copié/collé dans un agent. L’agent doit produire:
1) un compte-rendu dans [Agent feedback/](../Agent%20feedback/) (template imposé),
2) des recommandations concrètes (sans implémenter de code, sauf demande explicite).

## Prompt – Tâche 01 (Architecture API)
Fichier tâche: [Plan/01-architecture-api-mobile.md](./01-architecture-api-mobile.md)

Contexte: Backend Laravel 11 existant (UI Jaxon). On veut une API mobile `/api/v1` indépendante de Jaxon, tenant-scoped, réutilisant les services métier.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Recherche tontines: [Plan/00-recherche-tontines-senegal.md](./00-recherche-tontines-senegal.md)

Dossiers/fichiers du repo à consulter:
- Services métier: `src/Service/**`
- Routes API existantes: `routes/api.php`
- Auth/token: `config/sanctum.php`, `config/auth.php`

Demande: Propose l’architecture (routes, middlewares, policies, format d’erreur, pagination) et une liste d’endpoints minimaux MVP.
Contraintes: pas de features hors MVP.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 02 (Auth Sanctum)
Fichier tâche: [Plan/02-auth-mobile-sanctum.md](./02-auth-mobile-sanctum.md)

Contexte: Sanctum est présent mais l’API est minimale. Il faut un flux d’auth simple pour iOS.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Routes API existantes: `routes/api.php`
- Config auth: `config/sanctum.php`, `config/auth.php`
- Modèle user: `app/Models/User.php`

Demande: Spécifie le flux login/logout/me pour app iOS, la gestion de tokens, et les tests Feature à écrire.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 03 (Tenant scoping)
Fichier tâche: [Plan/03-tenant-scoping-api.md](./03-tenant-scoping-api.md)

Contexte: Le web utilise une databag Jaxon `tenant` (guild.id, round.id). L’API mobile doit être explicite (IDs dans l’URL) et sécurisée.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Tenant web actuel (pour comprendre, pas pour réutiliser): `app/Http/Middleware/TontineTenant.php`, `src/Service/TenantService.php`
- Services métier: `src/Service/**`
- Authorization/policies: `app/Providers/AuthServiceProvider.php` (si présent), `config/auth.php`

Demande: Propose une stratégie de scoping + règles d’autorisation + patterns d’erreurs (403 vs 404) et tests.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 04 (Paiements Dexchange)
Fichier tâche: [Plan/04-paiements-dexchange-integration.md](./04-paiements-dexchange-integration.md)

Contexte: Dexchange API (Wave + Orange Money) pour encaisser les cotisations et décaisser les payouts.

Docs / références (Dexchange):
- API reference: https://docs-api.dexchange.sn/api-reference/introduction
- Liste des services (SN): https://docs-api.dexchange.sn/api-reference/endpoint/services
- Init transaction: https://docs-api.dexchange.sn/en/api-reference/endpoint/init
- Statut transaction: https://docs-api.dexchange.sn/en/api-reference/endpoint/get-transaction
- Error handling: https://docs-api.dexchange.sn/errors

Docs / références (produit):
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Config HTTP/logging: `config/services.php`, `config/logging.php`
- Queue: `config/queue.php`
- Migrations existantes: `database/migrations/**`

Demande: Propose la modélisation DB, le client HTTP, l’idempotence via `externalTransactionId`, la stratégie webhooks+polling, et les cas d’erreurs.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 05 (Webhooks Dexchange)
Fichier tâche: [Plan/05-webhooks-dexchange.md](./05-webhooks-dexchange.md)

Contexte: On reçoit des webhooks de changement de statut.

Docs / références (Dexchange):
- Payload webhook (exemple dans init): https://docs-api.dexchange.sn/en/api-reference/endpoint/init
- Statut transaction (revalidation serveur): https://docs-api.dexchange.sn/en/api-reference/endpoint/get-transaction
- Error handling: https://docs-api.dexchange.sn/errors

Docs / références (produit):
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Routes HTTP: `routes/api.php`
- Middleware: `app/Http/Middleware/**`
- Logs: `storage/logs/**`

Demande: Propose un endpoint webhook idempotent, sécurisé (signature/secret si possible sinon double-check), et une stratégie de déduplication.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 06 (KYC)
Fichier tâche: [Plan/06-kyc-workflow.md](./06-kyc-workflow.md)

Contexte: KYC requis avant payout.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Stockage fichiers: `config/filesystems.php`
- Modèles + migrations: `app/Models/**`, `database/migrations/**`

Demande: Propose les champs, statuts, endpoints, UX minimal (côté mobile), et comment le payout est bloqué/autorisé.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 07 (Flux core tontine)
Fichier tâche: [Plan/07-tontine-core-flows.md](./07-tontine-core-flows.md)

Contexte: Tontine familiale/informelle. Rotatif, périodes, bénéficiaire.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Recherche tontines: [Plan/00-recherche-tontines-senegal.md](./00-recherche-tontines-senegal.md)

Dossiers/fichiers du repo à consulter:
- Services métier existants: `src/Service/**`
- Modèles existants: `src/Model/**`, `app/Models/**`

Demande: Propose les règles minimales, endpoints, et comment gérer “priorité exceptionnelle” avec journalisation.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 08 (Notifications)
Fichier tâche: [Plan/08-notifications-rappels.md](./08-notifications-rappels.md)

Contexte: Rappels et annonce bénéficiaire.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Queue: `config/queue.php`
- Events/listeners: `app/Events/**`, `app/Listeners/**`
- Notifications (si utilisées): `app/Notifications/**`

Demande: Propose une approche minimale (in-app + jobs), idempotence, et quelles métriques.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).

## Prompt – Tâche 09 (Audit log)
Fichier tâche: [Plan/09-audit-log.md](./09-audit-log.md)

Contexte: Réduire litiges et renforcer la confiance.

Docs / références:
- PRD MVP: [Plan/PRD.md](./PRD.md)

Dossiers/fichiers du repo à consulter:
- Logging: `config/logging.php`, `storage/logs/**`
- Migrations: `database/migrations/**`

Demande: Propose un schéma d’audit, les événements à logguer, et un endpoint admin minimal.
Livrable: 1 rapport dans [Agent feedback/](../Agent%20feedback/) (template: [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md)).
