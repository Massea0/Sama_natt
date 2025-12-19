# Instructions agents (Plan)

## Objectif
Permettre à plusieurs agents de travailler en parallèle sur les tâches du dossier [Plan](./) en produisant des sorties cohérentes, vérifiables, et compatibles avec l’MVP Sama Natt.

## Contrainte MVP (à respecter strictement)
- Tontine **familiale/informelle**.
- Paiements **Wave + Orange Money** uniquement.
- **KYC requis avant payout** (avec flexibilité admin/backoffice).
- UX cible: **faible littératie**, donc simplicité maximale.

## Règles de contribution
- Un agent travaille sur **une seule tâche Plan** à la fois.
- Chaque proposition doit inclure:
  - Hypothèses explicites
  - Impacts (tables, endpoints, services)
  - Risques + mitigations
  - Definition of Done testable
- Ne pas ajouter de fonctionnalités hors scope.

## Sources / preuves
- Pour paiements: utiliser la doc Dexchange (liens dans la tâche 04/05).
- Pour les pratiques tontines: se baser sur la synthèse [00-recherche-tontines-senegal.md](./00-recherche-tontines-senegal.md).

## Format de rendu attendu
- Rendre un rapport dans [Agent feedback/](../Agent%20feedback/) en partant de [REPORT-TEMPLATE.md](../Agent%20feedback/REPORT-TEMPLATE.md).
- Nom: `YYYY-MM-DD__agent__<tache-ou-sujet>.md`.

## Conventions techniques (repo)
- Backend Laravel 11.
- UI web existante pilotée via Jaxon (ne pas dépendre de Jaxon pour l’API mobile).
- Services métier principalement dans `src/Service/**` (préférer injection).
- Tests: `./vendor/bin/phpunit`.
