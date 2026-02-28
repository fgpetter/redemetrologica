<laravel-boost-guidelines>
=== .ai/livewire-alpinejs rules ===

---
alwaysApply: true
---

## Livewire Instructions

- Use `vendor/bin/sail artisan make:livewire [Namespace\ComponentName]` to create new components.
- Always use class-based components, never Volt.
- Livewire components require a single root element in the Blade template.
- All Livewire requests hit the Laravel backend - always validate form data and run authorization checks in Livewire actions.

---

### wire:model Modifiers

- `wire:model` is deferred by default - only syncs on form submission or action calls.
- Use `wire:model.live` for real-time updates on every keystroke.
- Use `wire:model.blur` to sync only when input loses focus.
- Use `wire:model.live.debounce.500ms` for debounced live updates.
- Prefer `.blur` or `.debounce` over `.live` without debounce to reduce network requests.

---

### wire:key in Loops

Always add `wire:key` in loops to help Livewire track elements: `wire:key="item-{{ $item->id }}"`.

---

### Loading States

- Use `wire:loading` and `wire:target` for loading indicators.
- Use `wire:loading.attr="disabled"` to disable buttons during requests.
- Prefer Tailwind's `data-loading:opacity-50` over `wire:loading` for simpler cases.

---

### Lifecycle Hooks

- Use `mount()` for initialization with route parameters.
- Use `updated{Property}()` for reactive side effects when a specific property changes.
- Use `updating{Property}()` to intercept before a property changes.

---

### Computed Properties

- Use `#[Computed]` for expensive operations that should be memoized.
- Access in Blade with `$this->posts` (not `$posts`).
- Computed properties are cached within a single request.

---

### Form Validation

- Use `#[Validate('required|min:3')]` attribute on properties for inline validation.
- Call `$this->validate()` before persisting data.
- For complex forms, use Livewire Form Objects.

---

### Events and Communication

- Use `$this->dispatch('event-name', data: $value)` to dispatch events.
- Use `#[On('event-name')]` attribute to listen for events in another component.

---

### Modelable Child Components

Use `#[Modelable]` for reusable input components that work with `wire:model`:

```php
// Child component
#[Modelable]
public $value = '';
```

```blade
<livewire:custom-input wire:model="name" />
```

---

### Security

- Use `#[Locked]` for properties that should not be modified from the client.
- Eloquent models as properties are automatically locked.
- Always authorize actions before database operations.

---

### Nested Components vs Islands

**Use Islands when:** performance isolation without complexity, defer/lazy load content, region doesn't need own lifecycle.

**Use Nested Components when:** reusable self-contained functionality, separate lifecycle hooks, encapsulated state and logic.

---

## Performance

### Use Alpine for Client-Side Only Interactions

Always use Alpine.js for interactions that don't require database queries or server-side logic:

```blade
{{-- ✅ Good: Toggle handled by Alpine --}}
<div x-data="{ showDetails: false }">
    <button @click="showDetails = !showDetails">Toggle</button>
    <div x-show="showDetails">Details...</div>
</div>

{{-- ❌ Bad: Unnecessary server round-trip --}}
<button wire:click="toggleDetails">Toggle</button>
@if($showDetails) <div>Details...</div> @endif
```

---

### Prefetch Data for Autocomplete/Suggestions

For input suggestions, load data once from backend and filter client-side:

```blade
{{-- ✅ Good: Load once, filter with Alpine --}}
<div x-data="{
    search: '',
    items: @js($items),
    get filtered() {
        if (!this.search) return [];
        return this.items.filter(i => 
            i.toLowerCase().includes(this.search.toLowerCase())
        ).slice(0, 10);
    }
}">
    <input type="text" x-model="search">
    <template x-for="item in filtered" :key="item">
        <li @click="$wire.selectedItem = item" x-text="item"></li>
    </template>
</div>

{{-- ❌ Bad: Server request on every keystroke --}}
<input type="text" wire:model.live="search">
```

---

### Avoid wire:model.live Without Debounce

```blade
{{-- ✅ Good --}}
<input wire:model.live.debounce.300ms="search">
<input wire:model.blur="email">

{{-- ❌ Bad: Request on every keystroke --}}
<input wire:model.live="search">
```

---

### Lazy Load Heavy Components

```blade
<livewire:heavy-component lazy />
<livewire:dashboard-stats lazy="on-load" />
```

---

### Skip Re-renders for JavaScript-Only Actions

Use `#[Renderless]` for actions that don't need to update the UI (analytics, logging, etc.).

---

## Alpine.js Instructions

- Alpine is bundled with Livewire - don't manually include it.
- Use `x-data` to declare reactive state.
- Use `x-show` for frequently toggled elements (CSS toggle, stays in DOM).
- Use `x-if` inside `<template>` for elements that rarely change (removed from DOM).
- Use `x-transition` for smooth animations.

---

### Accessing Livewire from Alpine ($wire)

```blade
<div x-data="{ localValue: '' }">
    <span x-text="$wire.count"></span>
    <button @click="$wire.increment()">+</button>
    <button @click="$wire.count = 0">Reset</button>
    <button @click="localValue = await $wire.getValue()">Get</button>
</div>
```

---

### x-modelable for Custom Inputs

Use `x-modelable` to make Alpine components work with `wire:model`:

```blade
<div x-data="{ count: 0 }" x-modelable="count" {{ $attributes }}>
    <button @click="count--">-</button>
    <span x-text="count"></span>
    <button @click="count++">+</button>
</div>

{{-- Usage --}}
<x-counter-input wire:model="quantity" />
```

---

### Alpine.data() for Reusable Components

Register in `resources/js/app.js`:

```javascript
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

Alpine.data('dropdown', () => ({
    open: false,
    toggle() { this.open = !this.open },
    close() { this.open = false }
}));

Livewire.start();
```

---

### Event Modifiers

- `@click.prevent` - preventDefault()
- `@click.stop` - stopPropagation()
- `@click.outside` - click outside element
- `@click.once` - trigger only once
- `@keydown.enter`, `@keydown.escape` - key modifiers

---

### Included Alpine Plugins

Livewire includes: `persist`, `intersect`, `collapse`, `focus`.

- `x-intersect="$wire.loadMore()"` - lazy loading on scroll
- `x-collapse` - smooth accordion collapse
- `x-trap="open"` - focus trap for modals
- `$persist('value')` - persist to localStorage

---

## Combining Livewire and Alpine

### Optimistic UI Updates

```blade
<button
    wire:click="bookmark"
    x-data="{ bookmarked: @js($bookmarked) }"
    @click="bookmarked = !bookmarked"
    :class="{ 'text-yellow-500': bookmarked }"
>
    <span x-show="!bookmarked">☆</span>
    <span x-show="bookmarked">★</span>
</button>
```

---

## Testing Livewire Components

- Use `Livewire::test(Component::class)` to test components.
- Chain `->set('property', 'value')` to set properties.
- Chain `->call('method')` to call actions.
- Assert with `->assertHasErrors(['field'])`, `->assertSet('prop', 'value')`, `->assertRedirect()`.
- Test component exists on page with `->assertSeeLivewire(Component::class)`.

=== .ai/php-laravel rules ===

---
alwaysApply: true
---

## General code instructions

- Don't generate code comments above the methods or code blocks if they are obvious. Don't add docblock comments when defining variables, unless instructed to, like `/** @var \App\Models\User $currentUser */`. Generate comments only for something that needs extra explanation for the reasons why that code was written.
- For library documentation, if some library is not available in Laravel Boost 'search-docs', always use context7. Automatically use the Context7 MCP tools to resolve library id and get library docs without me having to explicitly ask.
- Every frontend change or debugging mode use Crhome DevTools MCP to check for errors.
- If envinronment is running over Docker Sail, always use `vendor/bin/sail` to run a command. Example: `vendor/bin/sail artisan:migrate` or `vendor/bin/sail npm run build` `vendor/bin/sail composer install`
- Don't run laravel pint after changing a PHP or blade file
- When runnuing debug, don't try to write a new file using file_put_contents('/pathtoproject/.cursor/debug.log'), always use native Laravel loggin method

---

## PHP instructions

- In PHP, use `match` operator over `switch` whenever possible
- Generate Enums always in the folder `app/Enums`, not in the main `app/` folder, unless instructed differently.
- Always use Enum value as the default in the migration if column values are from the enum. Always casts this column to the enum type in the Model.
- Don't create temporary variables like `$currentUser = auth()->user()` if that variable is used only one time.
- Always use Enum where possible instead of hardcoded string values, if Enum class exists. For example, in Blade files, and in the tests when creating data if field is casted to Enum then use that Enum instead of hardcoding the value.

---

## Laravel instructions

- Using Services in Controllers: if Service class is used only in ONE method of Controller, inject it directly into that method with type-hinting. If Service class is used in MULTIPLE methods of Controller, initialize it in Constructor.
- Prioritize Actions over Services. Recurse to Services only when the business logic requires multiple related methods within a single class.
- **Eloquent Observers** should be registered in Eloquent Models with PHP Attributes, and not in AppServiceProvider. Example: `#[ObservedBy([UserObserver::class])]` with `use Illuminate\Database\Eloquent\Attributes\ObservedBy;` on top
- Aim for "slim" Controllers and put larger logic pieces in action classes or Services if the business logic requires multiple methods.
- Use "thin controller fat model" pattern. Examples: use `User::active()->get()` instead of `Users::where('active', 1)` in controllers.
- Priorize reusing blade components. Search in `/resources/views/components` before creating a new chunk of code in blade.
- Use Laravel helpers instead of `use` section classes. Examples: use `auth()->id()` instead of `Auth::id()` and adding `Auth` in the `use` section. Other examples: use `redirect()->route()` instead of `Redirect::route()`, or `str()->slug()` instead of `Str::slug()`.
- Don't use `whereKey()` or `whereKeyNot()`, use specific fields like `id`. Example: instead of `->whereKeyNot($currentUser->getKey())`, use `->where('id', '!=', $currentUser->id)`.
- Don't add `::query()` when running Eloquent `create()` statements. Example: instead of `User::query()->create()`, use `User::create()`.
- In Livewire projects, don't use Livewire Volt. Only Livewire class components.
- When adding columns in a migration, update the model's `$fillable` array to include those new attributes.
- Never chain multiple migration-creating commands (e.g., `make:model -m`, `make:migration`) with `&&` or `;` — they may get identical timestamps. Run each command separately and wait for completion before running the next.
- Enums: If a PHP Enum exists for a domain concept, always use its cases (or their `->value`) instead of raw strings everywhere — routes, middleware, migrations, seeds, configs, and UI defaults.
- Controllers: Single-method Controllers should use `__invoke()`; multi-method RESTful controllers should use `Route::resource()->only([])`
- Don't create Controllers with just one method which just returns `view()`. Instead, use `Route::view()` with Blade file directly.
- Always use Laravel's @session() directive instead of @if(session()) for displaying flash messages in Blade templates.
- In Blade files always use `@selected()` and `@checked()` directives instead of `selected` and `checked` HTML attributes. Good example: @selected(old('status') === App\Enums\ProjectStatus::Pending->value). Bad example: {{ old('status') === App\Enums\ProjectStatus::Pending->value ? 'selected' : '' }}.

---

## Testing instructions

### Before Writing Tests

  1. **Check database schema** - Use `database-schema` tool to understand:
     - Which columns have defaults
     - Which columns are nullable
     - Foreign key relationship names

  2. **Verify relationship names** - Read the model file to confirm:
     - Exact relationship method names (not assumed from column names)
     - Return types and related models

  3. **Test realistic states** - Don't assume:
     - Empty model = all nulls (check for defaults)
     - `user_id` foreign key = `user()` relationship (could be `author()`, `employer()`, etc.)
     - When testing form submissions that redirect back with errors, assert that old input is preserved using `assertSessionHasOldInput()`.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.26
- laravel/framework (LARAVEL) - v11
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- livewire/livewire (LIVEWIRE) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v10

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `livewire-development` — Develops reactive Livewire 3 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI.
- `laravel-pdf` — Generate PDFs from Blade views or HTML using spatie/laravel-pdf. Covers creating, formatting, saving, downloading, and testing PDFs with the Browsershot, Cloudflare, or DOMPDF driver.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `vendor/bin/sail npm run build`, `vendor/bin/sail npm run dev`, or `vendor/bin/sail composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double-check the available parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `vendor/bin/sail artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

=== laravel/v11 rules ===

# Laravel 11

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- This project upgraded from Laravel 10 without migrating to the new streamlined Laravel 11 file structure.
- This is perfectly fine and recommended by Laravel. Follow the existing structure from Laravel 10. We do not need to migrate to the Laravel 11 structure unless the user explicitly requests it.

## Laravel 10 Structure

- Middleware typically lives in `app/Http/Middleware/` and service providers in `app/Providers/`.
- There is no `bootstrap/app.php` application configuration in a Laravel 10 structure:
    - Middleware registration is in `app/Http/Kernel.php`
    - Exception handling is in `app/Exceptions/Handler.php`
    - Console commands and schedule registration is in `app/Console/Kernel.php`
    - Rate limits likely exist in `RouteServiceProvider` or `app/Http/Kernel.php`

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

## New Artisan Commands

- List Artisan commands using Boost's MCP tool, if available. New commands available in Laravel 11:
    - `vendor/bin/sail artisan make:enum`
    - `vendor/bin/sail artisan make:class`
    - `vendor/bin/sail artisan make:interface`

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces using only PHP — no JavaScript required.
- Instead of writing frontend code in JavaScript frameworks, you use Alpine.js to build the UI when client-side interactions are required.
- State lives on the server; the UI reflects it. Validate and authorize in actions (they're like HTTP requests).
- IMPORTANT: Activate `livewire-development` every time you're working with Livewire-related tasks.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `vendor/bin/sail artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `vendor/bin/sail artisan test --compact`.
- To run all tests in a file: `vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `vendor/bin/sail artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
