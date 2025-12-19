# Copilot instructions (tontine)

## Architecture (à connaître avant de coder)
- App Laravel 11 (routes HTTP classiques) + UI majoritairement pilotée via **Jaxon** (AJAX côté serveur).
- Les composants Jaxon vivent dans `ajax/` et sont auto-chargés via Composer (`"Ajax\\": "ajax/"` dans `composer.json`).
- Le “tenant context” (utilisateur/guild/round courants) est central:
  - Middleware `app/Http/Middleware/TontineTenant.php` lit/écrit la **databag** Jaxon `tenant` (`guild.id`, `round.id`) et hydrate `Siak\Tontine\Service\TenantService`.
  - Les composants utilisent `TenantService` (souvent via `#[Inject]`) pour `user()/guild()/round()`.

## Jaxon: conventions de composants
- Bases:
  - `Ajax\Component` (serveur → HTML/DOM via Jaxon)
  - `Ajax\FuncComponent` (actions/handlers)
  - `Ajax\PageComponent` (pages paginées: méthode `page()` qui appelle `render()` + `paginator()->render()`)
- Conventions fréquentes:
  - Rendu de vues via `renderView()` ⇒ cherche `tontine::$view` (ex: `renderView('pages.select.guild', ...)` dans `ajax/Page/MenuFunc.php`).
  - Persistences UI via `bag('tenant')->set(...)` et partage via `stash()` (ex: `menu.current.guild`, `tenant.guild`, `tenant.round`).
  - DI via attributs Jaxon `#[Inject]` (ex: `TenantService`, `LocaleService`).

## Où brancher / intégrer Jaxon
- Le endpoint AJAX est `/ajax` (cf. `config/jaxon.php` → `jaxon.lib.core.request.uri`).
- Les middlewares appliqués aux appels Jaxon sont dans `config/jaxon.php` (`tontine`, `analytics`, `jaxon.config`, `jaxon.ajax`, ...).
- Le middleware `app/Http/Middleware/TontineJaxon.php` sélectionne les dossiers de composants exposés selon la route (ex: `tontine.home` charge `ajax/Page` + `ajax/App`).
  - Si vous ajoutez une nouvelle page qui doit charger d’autres composants/JS, ajuster `TontineJaxon::getOptions()`.
- Le middleware `app/Http/Middleware/TontineTemplate.php` configure les namespaces Blade:
  - namespace `tontine` pointe sur `resources/views/tontine/app/{template}`
  - namespace `pagination` pointe sur `.../parts/table/pagination`

## Couche “métier” (src/)
- La logique métier est principalement dans `src/Service/**` (Guild/Planning/Meeting/Report/Payment/Presence…).
- Les services sont enregistrés en singletons dans `app/Providers/SiakServiceProvider.php`.
  - Préférer l’injection (constructeur ou `#[Inject]`) plutôt que `new`.

## Workflows dev (repo-specific)
- Installation (README): `composer install && php artisan key:generate && php artisan migrate && php artisan db:seed`.
- Dev multi-process: `composer run dev` (script `dev` dans `composer.json` lance `artisan serve`, `queue:listen`, `pail`, et `npm run dev`).
- Tests: `./vendor/bin/phpunit` (config `phpunit.xml`).
- Format: `./vendor/bin/pint` (Laravel Pint).

## PDF / Chromium
- Les rapports PDF sont exposés dans `routes/web.php` (`/pdf/report/...`).
- Le rendu PDF utilise Chrome/Chromium via `chrome-php/chrome` et la config `config/chrome.php`.
  - En Docker, un service `tontine-chromium` est défini dans `docker/docker-compose.yml` (port 9222).
