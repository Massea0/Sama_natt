# Recherche – Tontines au Sénégal (synthèse orientée produit)

## Objectif
Synthétiser les pratiques réelles (règles, variantes) et les problèmes fréquents des tontines au Sénégal, afin d’en déduire les exigences MVP de **Sama Natt** (tontine informelle/familiale, paiements **Wave + Orange Money**, KYC avant payout).

## Ce qu’on observe (pratiques)
### 1) Tontine = épargne/solidarité, pas seulement “investissement”
- Les tontines servent à **mettre de côté** dans l’économie informelle, lisser les revenus variables, et financer des besoins (maison, urgence, activités).
- Formats observés:
  - **Rotatif**: chaque période, une personne reçoit la cagnotte.
  - **Accumulation / “épargne”**: chacun récupère son cumul à une date (fin de mois / fin de trimestre), parfois via un “gestionnaire”.

### 2) Périodicité et montants flexibles
- Cotisations **quotidiennes** (ex. 1 000–15 000 FCFA) ou **mensuelles** (ex. 5 000 FCFA) selon revenus.
- Les groupes “marché” (pêche/mareyage, etc.) peuvent utiliser la tontine comme discipline d’épargne.

### 3) Tirage au sort et ordre de réception
- En présentiel: tirage au sort quotidien/hebdo/mensuel.
- En ligne: des admins font parfois des **vidéos/Live** pour prouver la transparence du tirage.

### 4) Variantes “événement/ceremony priority” (à confirmer)
- Pratique terrain rapportée (à confirmer avec entretiens): priorité à un membre en cas d’événement (mariage, baptême, santé).
- Implication produit: possibilité de gérer une **priorité exceptionnelle** (avec accord/admin).

## Problèmes / pain points (constats Sénégal)
### 1) Arnaques et détournements dans les e-tontines
- Les “natts” en ligne (WhatsApp/Facebook/Instagram/TikTok) sont décrites comme un lieu d’**arnaques récurrentes**.
- Modus opérandi rapportés:
  - L’admin disparaît avec la cagnotte (montants très élevés possibles).
  - Retrait/blocage de participants après tirage (opacité sur qui a été payé).
  - Promesses irréalistes (“cotisez X, gagnez Y en 30 jours”) → assimilable à schémas frauduleux.

### 2) Confiance et traçabilité insuffisantes
- Les participants ne se connaissent pas toujours, identité floue (pas de photo, pas de vraie identité).
- L’absence de preuve formelle (qui doit quoi, qui a payé, qui doit recevoir) rend les litiges difficiles.

### 3) Méfiance envers le “stockage” sur wallets / plateformes
- Certains utilisateurs retirent immédiatement l’argent reçu; peur d’escroquerie/vol.
- Implication UX: afficher clairement l’état des fonds (payé/en attente) et réduire la rétention inutile d’argent.

### 4) Difficile d’opérer sans règles explicites
- Les imprévus (désistement, retards, changements) nécessitent un arbitrage.
- Implication produit: règles simples + rôle admin (trésorier) + journal d’événements.

## Implications produit (MVP – décisions proposées)
### A) “Trust by design” (anti-arnaque)
- Un groupe doit avoir:
  - Un admin identifié.
  - Une règle d’ordre de payout (tirage, calendrier, priorité exceptionnelle).
  - Un registre immuable des contributions et décisions.

### B) Paiements: Wave + Orange Money via Dexchange
- Utiliser Dexchange pour:
  - Encaissement (CASHOUT): `WAVE_SN_CASHOUT`, `OM_SN_CASHOUT`.
  - Décaissement (CASHIN): `WAVE_SN_CASHIN`, `OM_SN_CASHIN`.
- Approche recommandée:
  - Toujours initier avec `externalTransactionId` unique.
  - Consommer les webhooks et/ou vérifier le statut via `GET /transaction/{transactionId}`.
  - Gérer idempotence + retries + mode maintenance.

### C) KYC avant payout
- MVP: KYC requis **avant de recevoir** (payout) mais flexible selon règles/admin.
- Le backend doit pouvoir bloquer/autoriser un payout si KYC incomplet.

### D) UX faible littératie
- Parcours minimal:
  - Voir “Je dois payer aujourd’hui ?”
  - Payer (Wave/OM)
  - Voir “Qui reçoit ?” + date

## Sources (liens)
- Le Soleil – reportage sur tontines et épargne (2/2): https://lesoleil.sn/enquetes/reportages/epargne-les-tontines-un-moyen-deconomiser-de-largent/
- PressAfrik – arnaques e-tontines (WhatsApp/Facebook/Instagram): https://www.pressafrik.com/Arnaques-dans-les-e-Tontines-comment-les-femmes-senegalaises-jettent-des-millions-en-ligne_a232086.html
- Jotaay – e-tontines, solidarité numérique et dérives: https://www.jotaay.net/E-TONTINES-AU-SENEGAL-Entre-solidarite-numerique-et-derives-financieres_a48169.html
- OSIRIS – “tontines numériques” et inclusion (article de synthèse): https://www.osiris.sn/tontines-numeriques-un-levier-innovant-pour-l-inclusion-financiere.html
- Dexchange API – services: https://docs-api.dexchange.sn/api-reference/endpoint/services
- Dexchange API – init transaction: https://docs-api.dexchange.sn/en/api-reference/endpoint/init
- Dexchange API – statut transaction: https://docs-api.dexchange.sn/en/api-reference/endpoint/get-transaction
- Dexchange API – erreurs: https://docs-api.dexchange.sn/errors

## Questions ouvertes (pour entretiens terrain)
1) Dans les tontines familiales, qui arbitre les conflits et selon quelles règles ?
2) La “priorité événement” est-elle courante ou marginale (et dans quels milieux) ?
3) À quel point les participants acceptent-ils un “wallet interne” vs paiement direct à chaque échéance ?
4) Quelles pièces sont réalistes pour KYC (CNI, passeport, permis, etc.) et qui valide (admin vs backoffice) ?
