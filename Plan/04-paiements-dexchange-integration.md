# Tâche 04 – Intégration paiements Dexchange (Wave + Orange Money)

## Objectif
Intégrer Dexchange pour encaisser les cotisations (CASHOUT) et décaisser les payouts (CASHIN) avec idempotence, webhooks et états.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 04”).

## Références
- API Reference: https://docs-api.dexchange.sn/api-reference/introduction
- Services (SN): https://docs-api.dexchange.sn/api-reference/endpoint/services
- Init: https://docs-api.dexchange.sn/en/api-reference/endpoint/init
- Statut: https://docs-api.dexchange.sn/en/api-reference/endpoint/get-transaction
- Erreurs: https://docs-api.dexchange.sn/errors
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `config/services.php`, `config/logging.php`, `config/queue.php`, `database/migrations/**`

## Portée MVP
- Supporter `WAVE_SN_CASHOUT`, `OM_SN_CASHOUT` (cotisation).
- Supporter `WAVE_SN_CASHIN`, `OM_SN_CASHIN` (payout).
- Stocker transactions et mettre à jour via webhook + polling.

## Livrables
- Service `DexchangeClient` (HTTP) + config via env.
- Modèle DB `payments` / `payment_transactions`.
- API endpoints internes:
  - init cotisation
  - init payout
  - statut

## Points techniques clés
- `externalTransactionId` unique et stable (ex: ULID).
- Gérer `409` (déjà utilisé) → retourner la transaction existante.
- Retry uniquement idempotent.

## Definition of Done
- Init transaction fonctionne en sandbox/prod (selon clés).
- Webhook met à jour l’état.
- Tests unitaires sur mapping statuts.
