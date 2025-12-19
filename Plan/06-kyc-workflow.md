# Tâche 06 – Workflow KYC (avant payout)

## Objectif
Implémenter un workflow KYC minimal pour bloquer les payouts tant que l’identité n’est pas validée.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 06”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `config/filesystems.php`, `database/migrations/**`, `app/Models/**`

## Portée MVP
- Collecte données + uploads
- Statuts `not_started` → `submitted` → `approved|rejected`
- Rôles: user (soumet), admin/backoffice (valide/rejette)

## Livrables
- Modèle `kyc_submissions`
- Stockage fichiers (S3/local selon env)
- API:
  - `POST /api/v1/kyc/submit`
  - `GET /api/v1/kyc/status`
  - `POST /api/v1/admin/kyc/{id}/approve`
  - `POST /api/v1/admin/kyc/{id}/reject`

## Definition of Done
- Un payout échoue (403/422) si KYC non approuvé.
- Audit log des décisions.
