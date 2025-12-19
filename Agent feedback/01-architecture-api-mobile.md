# Agent feedback – Architecture API Mobile (Tâche 01)

## Contexte
- **Tâche / fichier Plan concerné**: `Plan/01-architecture-api-mobile.md`
- **Hypothèses**:
  - L'API mobile est indépendante de Jaxon (pas de databag/stash)
  - Réutilisation maximale des services métier existants (`src/Service/**`)
  - Sanctum est déjà configuré et utilisable pour l'auth token
  - Cible MVP: tontines familiales/informelles Sénégal (Wave + OM via Dexchange)
- **Questions posées / non résolues**:
  - Stratégie de refresh token (expiration Sanctum actuellement `null`)
  - Validation KYC interne vs provider tiers (PRD suggère interne MVP)
  - Format exact des webhooks Dexchange (signature/secret à vérifier)

## Résultats (résumé exécutif)

Architecture proposée pour `/api/v1`:
1. **Auth**: Sanctum tokens avec abilities (scopes)
2. **Tenant context**: Middleware API dédié `ApiTenant` basé sur route params (`{guild}`, `{round}`)
3. **Format erreurs**: JSON:API-like avec `error.code`, `error.message`, `error.details`
4. **Pagination**: Cursor-based pour les listes (performant sur mobile)
5. **Endpoints MVP**: 18 routes couvrant auth, guilds, rounds, sessions, payments, KYC

## Détails

### 1) Découvertes / faits

#### Services métier réutilisables
Les services existants dans `src/Service` sont bien structurés et réutilisables:
- `TenantService`: gère user/guild/round courants (à adapter pour API)
- `Guild/GuildService`: CRUD guilds, pagination
- `Guild/MemberService`: gestion membres
- `Guild/RoundService`: gestion rounds/sessions
- `Guild/SessionService`: CRUD sessions
- `Meeting/Pool/DepositService`: cotisations (deposits)
- `Meeting/Pool/RemitmentService`: payouts (remitments)
- `Payment/PaymentService`: statut paiements

#### Modèles clés
- `User` → `Guild` (1:N) → `Round` (1:N) → `Session` (1:N)
- `Guild` → `MemberDef` (membres persistants)
- `Round` → `Member` (membres actifs dans un round)
- `Pool` → `Subscription` → `Receivable` (cotisations) / `Payable` (payouts)

#### Auth existante
- Sanctum configuré (`config/sanctum.php`)
- Provider: `Siak\Tontine\Model\User` (Eloquent)
- Route `/api/user` déjà protégée par `auth:sanctum`

#### Tenant actuel (Jaxon)
Le middleware `TontineTenant` lit/écrit la databag Jaxon. Pour l'API mobile, il faut un middleware alternatif qui:
- Lit `guild_id` et `round_id` depuis les route params ou headers
- Injecte dans `TenantService` sans databag

### 2) Recommandations

#### A) Convention API

```
Base URL: /api/v1
Content-Type: application/json
Accept: application/json
Auth: Bearer {token}
```

#### B) Format d'erreur standard

```json
{
  "error": {
    "code": "validation_failed",
    "message": "Les données fournies sont invalides",
    "details": {
      "amount": ["Le montant doit être positif"]
    }
  }
}
```

Codes d'erreur MVP:
- `unauthenticated` (401)
- `forbidden` (403)
- `not_found` (404)
- `validation_failed` (422)
- `payment_failed` (402)
- `kyc_required` (403, sous-code)
- `server_error` (500)

#### C) Pagination (cursor-based)

```json
{
  "data": [...],
  "meta": {
    "cursor": "eyJpZCI6MTB9",
    "has_more": true,
    "count": 10
  }
}
```

Paramètres:
- `?cursor=xxx` (optionnel, base64 encoded)
- `?limit=10` (défaut: 10, max: 50)

#### D) Middleware API Tenant

Créer `app/Http/Middleware/ApiTenant.php`:
- Lit `{guild}` et `{round}` depuis route params
- Vérifie que l'user authentifié a accès au guild (owner ou guest)
- Injecte dans `TenantService`

#### E) Endpoints MVP

##### Auth (public)
| Méthode | Route | Description |
|---------|-------|-------------|
| POST | `/auth/register` | Inscription (phone + OTP) |
| POST | `/auth/login` | Login (phone + OTP) |
| POST | `/auth/refresh` | Refresh token |
| POST | `/auth/logout` | Logout (revoke token) |

##### User (auth:sanctum)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/me` | Profil utilisateur |
| PUT | `/me` | Mise à jour profil |
| GET | `/me/guilds` | Liste des tontines de l'user |

##### Guilds (tenant:guild)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/guilds/{guild}` | Détails tontine |
| GET | `/guilds/{guild}/members` | Liste membres |
| GET | `/guilds/{guild}/rounds` | Liste rounds |

##### Rounds (tenant:guild,round)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/guilds/{guild}/rounds/{round}` | Détails round |
| GET | `/guilds/{guild}/rounds/{round}/sessions` | Liste sessions |
| GET | `/guilds/{guild}/rounds/{round}/sessions/{session}` | Détails session |

##### Payments (cotisations)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/guilds/{guild}/rounds/{round}/sessions/{session}/dues` | Mes cotisations dues |
| POST | `/guilds/{guild}/rounds/{round}/sessions/{session}/payments` | Initier paiement (Wave/OM) |
| GET | `/payments/{payment}` | Statut paiement |

##### Payouts (bénéficiaire)
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/guilds/{guild}/rounds/{round}/sessions/{session}/beneficiary` | Qui reçoit? |

##### KYC
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/me/kyc` | Statut KYC |
| POST | `/me/kyc` | Soumettre KYC |

##### Health
| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/health` | Health check |

#### F) Structure fichiers proposée

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           ├── AuthController.php
│   │           ├── UserController.php
│   │           ├── GuildController.php
│   │           ├── RoundController.php
│   │           ├── SessionController.php
│   │           ├── PaymentController.php
│   │           └── KycController.php
│   ├── Middleware/
│   │   └── ApiTenant.php
│   ├── Requests/
│   │   └── Api/
│   │       └── V1/
│   │           ├── LoginRequest.php
│   │           ├── PaymentRequest.php
│   │           └── KycRequest.php
│   └── Resources/
│       └── Api/
│           └── V1/
│               ├── UserResource.php
│               ├── GuildResource.php
│               ├── MemberResource.php
│               ├── RoundResource.php
│               ├── SessionResource.php
│               ├── PaymentResource.php
│               └── DueResource.php
routes/
└── api.php (à étendre)
```

#### G) Exemple routes/api.php

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1;

// Health check (public)
Route::get('/v1/health', fn() => response()->json(['status' => 'ok']));

// Auth (public)
Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [V1\AuthController::class, 'register']);
    Route::post('/login', [V1\AuthController::class, 'login']);
    Route::post('/refresh', [V1\AuthController::class, 'refresh']);
});

// Protected routes
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    // Auth
    Route::post('/auth/logout', [V1\AuthController::class, 'logout']);
    
    // User
    Route::get('/me', [V1\UserController::class, 'show']);
    Route::put('/me', [V1\UserController::class, 'update']);
    Route::get('/me/guilds', [V1\UserController::class, 'guilds']);
    Route::get('/me/kyc', [V1\KycController::class, 'show']);
    Route::post('/me/kyc', [V1\KycController::class, 'store']);
    
    // Payment status (global, no tenant)
    Route::get('/payments/{payment}', [V1\PaymentController::class, 'show']);
    
    // Guild-scoped routes
    Route::prefix('guilds/{guild}')
        ->middleware(['api.tenant'])
        ->group(function () {
            Route::get('/', [V1\GuildController::class, 'show']);
            Route::get('/members', [V1\GuildController::class, 'members']);
            Route::get('/rounds', [V1\RoundController::class, 'index']);
            
            // Round-scoped routes
            Route::prefix('rounds/{round}')->group(function () {
                Route::get('/', [V1\RoundController::class, 'show']);
                Route::get('/sessions', [V1\SessionController::class, 'index']);
                
                // Session-scoped routes
                Route::prefix('sessions/{session}')->group(function () {
                    Route::get('/', [V1\SessionController::class, 'show']);
                    Route::get('/dues', [V1\PaymentController::class, 'dues']);
                    Route::post('/payments', [V1\PaymentController::class, 'store']);
                    Route::get('/beneficiary', [V1\SessionController::class, 'beneficiary']);
                });
            });
        });
});
```

#### H) Middleware ApiTenant (ébauche)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Siak\Tontine\Service\Guild\GuildService;
use Siak\Tontine\Service\TenantService;

class ApiTenant
{
    public function __construct(
        private TenantService $tenantService,
        private GuildService $guildService
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $this->tenantService->setUser($user);

        // Guild from route param
        if ($guildId = $request->route('guild')) {
            $guild = $this->guildService->getUserOrGuestGuild($user, (int) $guildId);
            if (!$guild) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'Tontine non trouvée',
                    ]
                ], 404);
            }
            $this->tenantService->setGuild($guild);

            // Round from route param
            if ($roundId = $request->route('round')) {
                $round = $this->tenantService->getRound((int) $roundId);
                if (!$round) {
                    return response()->json([
                        'error' => [
                            'code' => 'not_found',
                            'message' => 'Round non trouvé',
                        ]
                    ], 404);
                }
                $this->tenantService->setRound($round);
            }
        }

        return $next($request);
    }
}
```

### 3) Risques / points d'attention

1. **Auth OTP**: Le PRD mentionne "phone + OTP" mais pas de service OTP existant. À intégrer (Twilio, Orange SMS, etc.).

2. **Sanctum expiration**: Actuellement `null` (jamais expire). Recommander:
   ```php
   'expiration' => 60 * 24 * 7, // 7 jours
   ```
   + refresh token strategy.

3. **KYC model**: Pas de table KYC existante. À créer:
   ```
   kyc_submissions: id, user_id, status, id_type, id_number, selfie_path, submitted_at, reviewed_at, reviewer_id
   ```

4. **Payments Dexchange**: Pas de service Dexchange existant. À créer:
   - `DexchangeService` (init, callback, status check)
   - Table `payments` (external_id, dexchange_id, status, amount, service_code, etc.)

5. **Rate limiting**: Ajouter throttling sur endpoints sensibles (auth, payments).

6. **Idempotence**: Pour `POST /payments`, utiliser `Idempotency-Key` header.

7. **Offline/retry**: L'app iOS doit gérer les retries; côté API, assurer idempotence et réponses claires.

## Références
- PRD MVP: `Plan/PRD.md`
- Recherche tontines: `Plan/00-recherche-tontines-senegal.md`
- Tâche: `Plan/01-architecture-api-mobile.md`
- Services existants: `src/Service/**`
- Middleware tenant actuel: `app/Http/Middleware/TontineTenant.php`
- Config Sanctum: `config/sanctum.php`
- Config Auth: `config/auth.php`

## Actions proposées (next steps)

1. **Tâche 02**: Créer le middleware `ApiTenant.php` et l'enregistrer dans `bootstrap/app.php`
2. **Tâche 03**: Implémenter les controllers/resources pour les endpoints MVP (auth, guilds, sessions)
3. **Tâche 04**: Créer le service Dexchange + table payments
4. **Tâche 05**: Créer le modèle/service KYC
5. **Tâche 06**: Tests Feature pour les endpoints (au moins `/health`, `/me`, `/me/guilds`)
6. **Tâche 07**: Documentation OpenAPI/Swagger pour l'équipe iOS
