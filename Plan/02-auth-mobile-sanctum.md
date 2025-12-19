# Tâche 02 – Auth mobile (Sanctum)

## Objectif
Permettre à l’app iOS de s’authentifier et d’obtenir un token, avec un minimum de surface.

## Portée MVP
- Login / logout
- Récupérer l’utilisateur courant
- Pas de SSO.

## Prompt agent
- Voir [Plan/AGENT-PROMPTS.md](./AGENT-PROMPTS.md) (section “Tâche 02”).

## Références
- PRD MVP: [Plan/PRD.md](./PRD.md)
- Repo: `routes/api.php`, `config/sanctum.php`, `config/auth.php`, `app/Models/User.php`

## Livrables
- Routes:
  - `POST /api/v1/auth/login`
  - `POST /api/v1/auth/logout`
  - `GET /api/v1/me`
- Politique de tokens (naming, expiration si nécessaire).

## Definition of Done
- Login renvoie token + user.
- Logout invalide token.
- Tests Feature basiques.
