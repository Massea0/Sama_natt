# Tâche 05 – Webhooks Dexchange

## Objectif
Recevoir les notifications Dexchange (callBackURL) de changement de statut transaction, sécuriser, et appliquer l’idempotence.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 05”).

## Référence
- Payload exemple webhook (init doc): https://docs-api.dexchange.sn/en/api-reference/endpoint/init
- Statut transaction (revalidation serveur): https://docs-api.dexchange.sn/en/api-reference/endpoint/get-transaction
- Erreurs: https://docs-api.dexchange.sn/errors
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `routes/api.php`, `app/Http/Middleware/**`, `config/logging.php`

## Portée MVP
- Endpoint public: `POST /api/v1/payments/dexchange/webhook`
- Validation:
  - vérifier secret/signature si disponible
  - sinon: validation de forme + récupération statut via `GET /transaction/{transactionId}` avant d’acter.

## Livrables
- Route + controller webhook.
- Table de déduplication (id webhook + transactionId).
- Logs structurés.

## Definition of Done
- Webhook idempotent.
- Un webhook ne crée pas 2 écritures.
- Observabilité: corrélation par `externalTransactionId`.
