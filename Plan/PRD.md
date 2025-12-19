# PRD – Sama Natt (MVP)

## 1) Contexte
**Sama Natt** est un SaaS (backend Laravel) destiné à des tontines **informelles/familiales** au Sénégal (Dakar), avec une UX **ultra simple** (faible littératie) et des paiements via mobile money.

Contraintes MVP confirmées:
- Cible: tontines familiales/informelles (confiance sociale existante, mais besoin de traçabilité).
- Paiements: **Wave + Orange Money** uniquement.
- **KYC requis avant réception d’un payout**, avec flexibilité d’admin/backoffice selon contexte.

## 2) Objectifs
### Objectifs produit
- Réduire les litiges et les arnaques en apportant **preuve/traçabilité**.
- Simplifier le parcours: **payer** et **savoir qui reçoit**.
- Permettre un fonctionnement robuste (réseaux instables, téléphones basiques).

### Objectifs business
- Fournir une base backend stable pour une app iOS (Android plus tard).
- Se positionner “outil de gestion de tontine” (pas une promesse de rendement).

## 3) Non-objectifs (MVP)
- Marketplace, social feed, chat, gamification.
- Crédit/micro-assurance.
- Support multi-pays.
- Paiements autres que Wave/OM.

## 4) Personas
- **Admin/Trésorier**: crée la tontine, définit règle, arbitre incidents.
- **Membre**: paye, veut la confiance et la visibilité.
- **Backoffice** (ops): support, validation KYC, gestion litiges, audit.

## 5) Hypothèses
- Les tontines “online inconnus” sont risquées; MVP vise des groupes déjà constitués.
- La simplicité UX prime sur la richesse de fonctionnalités.
- Les paiements doivent être confirmés via webhooks/statut pour éviter les doubles comptabilisations.

## 6) Parcours utilisateur (MVP)
### Parcours membre (jour J)
1. Ouvrir l’app → écran “Aujourd’hui” (statut: je dois payer / je suis à jour)
2. Choisir Wave ou Orange Money
3. Confirmer paiement
4. Voir confirmation + “qui reçoit cette période”

### Parcours admin
1. Créer une tontine (nom, périodicité, montant, règle de réception)
2. Ajouter membres (invitation simple)
3. Clôturer une période (ou déclenchement automatique)
4. Déclencher payout au bénéficiaire (si règles satisfaites + KYC ok)

### Parcours KYC (avant payout)
- Le bénéficiaire doit avoir un statut KYC “validé” avant de recevoir.
- Si non validé: payout bloqué, admin/backoffice voit pourquoi.

## 7) Règles métier (MVP)
### Types de tontine supportés
- **Rotatif**: à chaque période, 1 bénéficiaire.
- **Épargne/accumulation**: optionnel (peut être phase 2 si déjà supporté dans le backend existant).

### Règle d’ordre de réception
- Par défaut: ordre défini à la création (liste) ou tirage.
- Option MVP: “priorité exceptionnelle” (mariage/baptême/santé) à déclencher par admin avec journalisation.

### États de paiement
- Dû → Initié → Confirmé (SUCCESS) → Échoué/Annulé.
- Les écritures financières internes sont pilotées par le statut Dexchange.

## 8) Paiements (Wave + Orange Money via Dexchange)
### Choix d’intégration
- Agrégateur: **Dexchange API**.
- Encaissement (cotisations): services `WAVE_SN_CASHOUT`, `OM_SN_CASHOUT`.
- Décaissement (payout): services `WAVE_SN_CASHIN`, `OM_SN_CASHIN`.

### Exigences techniques
- `externalTransactionId` unique (idempotence) → gérer `409` “already used”.
- Consommer webhooks (callBackURL) et/ou polling `GET /transaction/{transactionId}`.
- Stocker:
  - transactionId Dexchange
  - externalTransactionId
  - serviceCode
  - amount/fee/number
  - status (PENDING/PROCESSING/SUCCESS/FAILED/CANCELLED)
- Sécurité:
  - Vérifier signature/secret webhook (si disponible côté Dexchange) sinon stratégie alternative (IP allowlist + double-check statut).
  - Ne jamais faire confiance au client mobile pour un statut.

## 9) KYC (avant payout)
### Données (MVP)
- Identité: nom, date de naissance (optionnel si trop lourd), type pièce, numéro pièce.
- Preuves: photos recto/verso + selfie (selon capacité), ou upload minimal.
- Statuts: `not_started`, `submitted`, `approved`, `rejected`.

### Règles
- Payout interdit si KYC != `approved`.
- Admin peut marquer “exception” si autorisé par policy (paramétrable).

## 10) Exigences produit/UX
- Peu d’écrans, phrases courtes, pictos/états.
- Fonctionne avec réseau intermittent: retries, affichage de statut.
- Notifications minimales: rappel de paiement + annonce bénéficiaire.

## 11) Exigences API (mobile)
- Auth: token (Sanctum) + refresh stratégie à définir.
- Endpoints versionnés: `/api/v1/...`
- Tout doit être scoped par “tenant” (tontine/guild/round) sans dépendre de la databag Jaxon.

## 12) Observabilité & Ops
- Journal d’événements: création, modification règles, paiements, payouts, exceptions KYC.
- Support: écran backoffice (phase MVP-lite) ou au minimum endpoints admin.
- Logs: inclure IDs de transaction.

## 13) Risques & mitigations
- **Fraude/arnaque**: renforcer identification, audit trail, règles explicites.
- **Paiements non fiables**: webhooks + statut + idempotence.
- **Faible littératie**: 1 action principale par écran.
- **Conformité**: KYC gating, rôles admin/backoffice.

## 14) KPIs (MVP)
- % cotisations réussies / échouées.
- Délai moyen de confirmation.
- % payouts bloqués par KYC.
- Litiges (nombre/100 utilisateurs).

## 15) Dépendances
- Dexchange API + configuration webhook.
- Stratégie KYC (workflow interne vs provider tiers) – MVP: interne.
- iOS app: besoin d’API stable + docs.
