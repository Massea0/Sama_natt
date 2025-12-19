# Tâche 01 – Architecture API mobile (Laravel)

## Objectif
Poser une API REST `/api/v1` adaptée à l’app iOS, sans dépendance aux mécanismes Jaxon (databag/stash), tout en réutilisant les services métier existants dans `src/Service/**`.

## Contrainte MVP
- Pas de fonctionnalités “nice to have”.
- Doit supporter: auth, gestion tontine (group), cotisations, paiements Dexchange, KYC gating.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 01”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Recherche tontines: [Plan/00-recherche-tontines-senegal.md](./00-recherche-tontines-senegal.md)
- Repo: `routes/api.php`, `src/Service/**`

## Livrables
- Convention d’API (versionning, pagination, erreurs).
- Squelette des routes `/api/v1/*`.
- Contrats DTO/Resources Laravel.
- Stratégie “tenant context” côté API.

## Implémentation (suggestion)
- Créer un middleware API tenant (ex: `ApiTenant`) basé sur headers/route params.
- Ne pas utiliser la databag Jaxon.
- Utiliser Sanctum pour auth.

## Definition of Done
- `/api/v1/health` accessible.
- `/api/v1/*` renvoie JSON stable (format standard).
- Tenant scoped et testé (au moins 1 test Feature).

## Risques
- Dériver des services métier existants; privilégier l’adaptation via services/DTO.
