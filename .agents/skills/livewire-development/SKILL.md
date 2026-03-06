---
name: livewire-development
description: "Develops reactive Livewire 3 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI."
license: MIT
metadata:
  author: laravel
---

# Livewire Development

## When to Apply

Activate when: creating/modifying Livewire components, debugging reactivity, writing Livewire tests, adding Alpine interactivity, or working with wire: directives.

## Documentation

Use `search-docs` for Livewire 3 patterns. Use `vendor/bin/sail artisan make:livewire [Namespace\ComponentName]` for new components.

## Essentials

- State on server, UI reflects it. Validate and authorize in actions (like HTTP requests).
- `wire:model` is deferred by default; use `wire:model.live` or `.blur`/`.debounce` for updates.
- Single root element per component. Add `wire:key` in loops.
- Alpine bundled with Livewire; plugins: persist, intersect, collapse, focus.
- Prefer Alpine for client-only interactions; avoid server round-trips for toggles.

## Pitfalls

- Forgetting `wire:key` in loops; using `wire:model` expecting real-time (use `.live`); not validating in actions; including Alpine separately.
