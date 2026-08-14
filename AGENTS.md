# Statamic Livewire

This repository is a Statamic addon (a Laravel package) integrating Livewire 4 with Statamic 6 and Antlers. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `marcorieser/statamic-livewire`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Git & Commits

- Only commit when explicitly instructed for that specific commit; a standing "commit as you go" permission must be granted explicitly for the session.
- Use Conventional Commits (`feat:`, `fix:`, `chore:`, `test:`, `ci:`, `refactor:`, `docs:`, …).
- Commit in logical units: one self-contained change per commit; split unrelated changes instead of bundling them.
- Keep subjects short and precise; add a body only when the subject alone is not self-explanatory.
- No co-author trailers or AI attribution; commits are authored by the repository owner only.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Rector check: `composer refactor:check` (apply: `composer refactor`)
- Pest tests: `composer test:unit`
- Coverage: `composer test:coverage` (needs Xdebug or PCOV)
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.
