# UI packages

Packages with screens use **Livewire 4** (Alpine included) and **Tailwind v4**. Logic stays in PHP, so PHPStan and the tests cover it; there's no second codebase in Vue or React.

There are two kinds, chosen when the package is created.

## Filament plugins

For screens that live inside an app's Filament 5 panel.

- `src/Filament/<Name>Plugin.php` implements `Filament\Contracts\Plugin`; apps add it with `$panel->plugin(<Name>Plugin::make())`. Its `register()` adds pages, resources and widgets to the panel.
- Build screens with Filament's own builders — tables, schemas, actions — rather than custom Blade. They're styled by Filament and need no CSS from the package.
- Data that isn't in Eloquent works too: a table's `records()` takes a closure returning rows keyed by record ID. Build the rows from your data objects (their `jsonSerialize()`), and after an action changes data, call `$this->flushCachedTableRecords()` so the table shows the change.
- The plugin ships **no CSS**, following Filament's advice. If its views use Tailwind classes Filament itself doesn't, apps add the package's views to their Filament theme with `@source`.
- Filament pages override `protected` methods of their parent; that's allowed — the `strict` arch preset ignores `src/Filament` where it has to.

## Standalone dashboards

For screens outside any panel, like Horizon or Telescope.

- `src/Dashboard/` holds a service provider that registers the routes, Livewire components and views, and a gate named `view<Name>`. Until the app defines that gate, only the `local` environment gets in.
- Remember Laravel only lets guests through a gate whose first parameter is nullable: `fn (?Authenticatable $user = null): bool => ...`.
- Styles are built by Vite into `dist/` and **committed**. The package serves them itself through a route with a content-hashed URL and a year-long cache, so apps never publish or build assets.
- While `npm run dev` runs, the layout loads CSS from the Vite dev server instead: editing a Blade view reloads the page, and new Tailwind classes work instantly. `composer dev` runs the demo app and Vite together.
- CI fails if `dist/` doesn't match a fresh `npm run build`.

## Livewire hooks

Livewire calls some component methods by name from its own base class: `rules()`, `messages()` and `validationAttributes()`. Declare them `public` — Livewire can't call a `private` one, and the page fails at runtime.

## Testing

| What | How |
|---|---|
| A Livewire component's behaviour | `livewire(Component::class)->call('markPaid', 'inv_1')->assertSee(...)` (`use function Pest\Livewire\livewire;`) |
| A Filament table action | `FilamentActions::call(livewire(Page::class), TestAction::make('markPaid')->table('inv_1'))` — a typed wrapper in `tests/Support`, because Filament's `callAction()` is a macro PHPStan can't see |
| Routes, gates, served assets | `get('/dashboard')->assertForbidden()` (`use function Pest\Laravel\get;`) |
| A whole user flow in a real browser | `visit('/dashboard')->click('Mark paid')->assertSee(...)` in `tests/Browser/` |

Browser tests use [Pest's browser plugin](https://pestphp.com/docs/browser-testing) with Playwright: `npm ci` installs Playwright, and `npx playwright install chromium` downloads the browser once per machine. If browser tests fail with "Playwright is outdated", the browser download is usually missing — run that second command. The browser plugin also needs PHP's `sockets` extension: Homebrew's PHP and GitHub's runners include it, but the official PHP Docker image needs `docker-php-ext-install sockets`. They run separately with `composer test:browser`, which builds the demo app first; `composer test` skips them. In CI they run on pull requests, tags and public repositories.

## The demo app

`composer serve` opens the UI in the workbench app:

- **Filament:** `/admin`, already signed in as the seeded user (`workbench/app/Http/Middleware/SignInDemoUser.php`).
- **Dashboard:** `/<name>`. The demo app runs as `APP_ENV=local` (`testbench.yaml`), so the dashboard's default gate lets you in.

Demo data that should survive between requests needs real storage; the example keeps invoices in a JSON file under the app's storage folder, reseeded by `composer build`.
