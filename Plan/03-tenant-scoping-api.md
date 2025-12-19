# Tâche 03 – Tenant scoping côté API

## Objectif
Reproduire le “tenant context” (tontine/guild/round) côté API, mais de façon explicite et sûre.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 03”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo (contexte web actuel, à ne pas dépendre côté API): `app/Http/Middleware/TontineTenant.php`, `src/Service/TenantService.php`
- Repo (cible API): `routes/api.php`, `src/Service/**`

## Décision proposée
- Chaque ressource est adressée via IDs dans l’URL:
  - `/api/v1/guilds/{guild}/rounds/{round}/...`
- Le backend valide que l’utilisateur a accès au `guild` et au `round`.

## Livrables
- Middleware / helpers de scoping.
- Policies Laravel pour autorisations.

## Definition of Done
- Accès refusé (403/404) si mauvais tenant.
- Tests Feature sur un accès interdit.
