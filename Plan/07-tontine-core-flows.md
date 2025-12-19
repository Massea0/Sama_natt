# Tâche 07 – Flux core tontine (création, périodes, bénéficiaire)

## Objectif
Définir et exposer les flux de base: création d’une tontine, ajout membres, calendrier/périodes, choix du bénéficiaire et clôture.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 07”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Recherche tontines: [Plan/00-recherche-tontines-senegal.md](./00-recherche-tontines-senegal.md)
- Repo: `src/Service/**`, `src/Model/**`, `app/Models/**`

## Portée MVP
- Rotatif: 1 bénéficiaire par période.
- Règles d’ordre: liste fixe OU tirage.
- Option: “priorité exceptionnelle” déclenchée par admin et loggée.

## Livrables
- API endpoints:
  - `POST /api/v1/guilds` (ou équivalent “tontine group”)
  - `POST /api/v1/guilds/{guild}/members`
  - `GET /api/v1/guilds/{guild}/rounds/{round}`
  - `POST /api/v1/guilds/{guild}/rounds/{round}/close`
- Modèles/Services nécessaires.

## Definition of Done
- Un admin peut créer + ajouter membres.
- Le bénéficiaire du round est calculé de manière déterministe.
- Journal des changements de règle.
