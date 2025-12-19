# Tâche 09 – Audit log (traçabilité)

## Objectif
Avoir une traçabilité exploitable pour litiges: qui a fait quoi, quand, et pourquoi.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 09”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `database/migrations/**`, `config/logging.php`

## Portée MVP
- Événements: création tontine, ajout membre, changement règle, init paiement, confirmation paiement, init payout, blocage KYC, override admin.

## Livrables
- Table `audit_events`.
- Helper/service `AuditLogger`.
- API admin: recherche basique par tontine + période.

## Definition of Done
- Les actions critiques écrivent un event.
- Format stable et horodaté.
