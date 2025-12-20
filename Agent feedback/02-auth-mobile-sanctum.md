# Agent feedback – Tâche 02: Auth mobile (Sanctum)

## Contexte
- **Tâche / fichier Plan concerné**: [Plan/02-auth-mobile-sanctum.md](../Plan/02-auth-mobile-sanctum.md)
- **Hypothèses**: 
  - L'app iOS utilisera des tokens Bearer (pas de sessions/cookies)
  - Un seul token actif par device est suffisant pour le MVP
  - Le refresh token n'est pas requis pour le MVP (token longue durée)
- **Questions posées / non résolues**: 
  - Faut-il implémenter un refresh token ou une expiration configurable ?
  - Doit-on limiter le nombre de tokens par utilisateur ?

## Résultats (résumé exécutif)
- Sanctum est déjà configuré et le modèle `User` utilise le trait `HasApiTokens`
- L'endpoint `GET /api/v1/me` existe déjà avec protection `auth:sanctum`
- Il manque uniquement les routes `POST /api/v1/auth/login` et `POST /api/v1/auth/logout`

## Détails

### 1) Découvertes / faits

**Configuration actuelle:**
- `config/auth.php`: provider utilise `Siak\Tontine\Model\User::class` (hérite de `App\Models\User`)
- `App\Models\User` utilise le trait `HasApiTokens` de Sanctum ✅
- `config/sanctum.php`: expiration `null` (tokens sans expiration par défaut)
- Routes API versionnées sous `/api/v1/` avec middleware `auth:sanctum`

**Endpoints existants (protégés):**
- `GET /api/v1/me` → `UserController::show()` retourne l'utilisateur courant
- `PUT /api/v1/me` → `UserController::update()` met à jour le profil
- `GET /api/v1/me/guilds` → liste des tontines de l'utilisateur

**Tests existants:**
- `tests/Feature/Api/V1/UserTest.php` couvre déjà `/api/v1/me` avec Sanctum
- `tests/Feature/Auth/AuthenticationTest.php` teste l'auth web (sessions)

### 2) Recommandations

#### A. Routes à créer

```
POST /api/v1/auth/login    → AuthController::login()
POST /api/v1/auth/logout   → AuthController::logout()
```

L'endpoint `GET /api/v1/me` existe déjà et satisfait le besoin "récupérer l'utilisateur courant".

#### B. Contrôleur `AuthController`

Créer `app/Http/Controllers/Api/V1/AuthController.php`:

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Siak\Tontine\Model\User;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * 
     * Body: { "email": "...", "password": "...", "device_name": "iPhone 15" }
     * Response: { "data": { "token": "...", "user": {...} } }
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->error(
                'invalid_credentials',
                'Email ou mot de passe incorrect',
                401
            );
        }

        // Créer un token nommé (device_name pour identifier l'appareil)
        $token = $user->createToken($validated['device_name'])->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     * 
     * Invalide le token courant.
     * Response: { "data": { "message": "Déconnexion réussie" } }
     */
    public function logout(Request $request): JsonResponse
    {
        // Révoquer uniquement le token utilisé pour cette requête
        $request->user()->currentAccessToken()->delete();

        return $this->success([
            'message' => 'Déconnexion réussie',
        ]);
    }
}
```

#### C. Routes à ajouter dans `routes/api.php`

```php
// Auth routes (public)
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [V1\AuthController::class, 'login']);
});

// Auth routes (protected)
Route::prefix('v1/auth')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [V1\AuthController::class, 'logout']);
});
```

#### D. Politique de tokens

| Aspect | Recommandation MVP | Justification |
|--------|-------------------|---------------|
| **Naming** | `device_name` obligatoire | Permet d'identifier l'appareil (ex: "iPhone 15 Pro") |
| **Expiration** | Aucune (`null`) | Simplification MVP; à revoir si besoin de sécurité renforcée |
| **Abilities** | Non utilisées | Toutes les abilities par défaut; à ajouter si granularité requise |
| **Révocation** | Token courant uniquement | `logout` révoque le token utilisé, pas tous les tokens |

**Option future (post-MVP):**
- Ajouter `POST /api/v1/auth/logout-all` pour révoquer tous les tokens d'un utilisateur
- Configurer une expiration (ex: 30 jours) dans `config/sanctum.php`

#### E. Tests Feature à écrire

Créer `tests/Feature/Api/V1/AuthTest.php`:

```php
<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Siak\Tontine\Model\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ========== LOGIN ==========

    public function test_login_returns_token_and_user(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ])
            ->assertJsonPath('data.user.email', 'test@example.com');

        // Vérifier que le token a été créé
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
        ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    public function test_login_fails_with_unknown_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'password123',
            'device_name' => 'iPhone 15',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'invalid_credentials');
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password', 'device_name']);
    }

    // ========== LOGOUT ==========

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('iPhone 15')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('data.message', 'Déconnexion réussie');

        // Vérifier que le token a été supprimé
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPhone 15',
        ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    public function test_logout_only_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('iPhone 15')->plainTextToken;
        $token2 = $user->createToken('iPad Pro');

        $this->withHeader('Authorization', "Bearer $token1")
            ->postJson('/api/v1/auth/logout');

        // Le token 2 doit toujours exister
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'iPad Pro',
        ]);
    }

    // ========== ME (déjà couvert mais ajoutons un test avec token réel) ==========

    public function test_me_works_with_bearer_token(): void
    {
        $user = User::factory()->create(['name' => 'Test User']);
        $token = $user->createToken('iPhone 15')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test User');
    }
}
```

### 3) Risques / points d'attention

| Risque | Mitigation |
|--------|------------|
| **Brute-force login** | Ajouter rate limiting sur `/api/v1/auth/login` (ex: `throttle:5,1`) |
| **Token stocké en clair côté iOS** | L'app iOS doit utiliser le Keychain |
| **Pas de refresh token** | Acceptable MVP, mais à considérer pour la v2 |
| **Pas de validation email** | Le champ `email_verified_at` existe mais n'est pas vérifié au login |

**Rate limiting recommandé (à ajouter dans les routes):**
```php
Route::post('/login', [V1\AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 tentatives par minute
```

## Références
- Sanctum docs: https://laravel.com/docs/11.x/sanctum
- Fichiers analysés:
  - `routes/api.php`
  - `config/sanctum.php`
  - `config/auth.php`
  - `app/Models/User.php`
  - `src/Model/User.php`
  - `app/Http/Controllers/Api/V1/UserController.php`
  - `tests/Feature/Api/V1/UserTest.php`

## Actions proposées (next steps)

1. **Créer le contrôleur** `app/Http/Controllers/Api/V1/AuthController.php`
2. **Ajouter les routes** dans `routes/api.php`
3. **Créer les tests** `tests/Feature/Api/V1/AuthTest.php`
4. **Exécuter les tests**: `./vendor/bin/phpunit tests/Feature/Api/V1/AuthTest.php`
5. **(Optionnel)** Ajouter rate limiting sur le login

## Flux iOS simplifié

```
┌─────────────────────────────────────────────────────────────────┐
│                        iOS App Flow                              │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. LOGIN                                                        │
│     POST /api/v1/auth/login                                      │
│     Body: { email, password, device_name: "iPhone 15" }          │
│     ↓                                                            │
│     Response: { token: "1|abc...", user: {...} }                 │
│     ↓                                                            │
│     Store token in Keychain                                      │
│                                                                  │
│  2. AUTHENTICATED REQUESTS                                       │
│     Header: Authorization: Bearer 1|abc...                       │
│     GET /api/v1/me                                               │
│     GET /api/v1/me/guilds                                        │
│     ...                                                          │
│                                                                  │
│  3. LOGOUT                                                       │
│     POST /api/v1/auth/logout                                     │
│     Header: Authorization: Bearer 1|abc...                       │
│     ↓                                                            │
│     Token invalidé côté serveur                                  │
│     ↓                                                            │
│     Supprimer token du Keychain                                  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```
