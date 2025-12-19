# Tâche 08 – Notifications (rappels paiement + annonce bénéficiaire)

## Objectif
Mettre en place des notifications minimales adaptées au MVP.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 08”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `app/Events/**`, `app/Listeners/**`, `config/queue.php`

## Portée MVP
- Rappel “paiement dû”.
- Notification “bénéficiaire du round”.

## Livrables
- Événements internes (Laravel events).
- Queue jobs (déjà présente dans l’environnement dev).
- Endpoints (si push via provider plus tard) ou stockage notifications in-app.

## Definition of Done
- Les événements sont émis.
- Les jobs sont idempotents.
