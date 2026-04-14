# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Custom PHPStan rules library (`iwf-web/phpstan-rules`) enforcing coding standards, security practices, and architectural conventions across IWF projects. PHP 8.3+, PHPStan ^2.1.

## Commands

```bash
bin/test.sh              # Run PHPStan + PHPUnit (local PHP or Docker fallback)
bin/test.sh 8.4          # Target specific PHP version
bin/lint.sh              # Auto-fix code style (PHP CS Fixer)
composer lint:check      # Check style without modifying (CI mode)
composer phpstan         # PHPStan only
composer phpunit         # PHPUnit only
composer test            # Both PHPStan + PHPUnit
XDEBUG_TRIGGER=1 bin/test.sh  # Debug with Xdebug
```

## Architecture

### Rule Implementation

Rules implement `PHPStan\Rules\Rule<NodeType>`. Each rule:
- Targets a specific AST node type (FuncCall, Attribute, Use_, Class_, ClassMethod, New_, etc.)
- Defines `const string IDENTIFIER = 'iwfWeb.ruleName'`
- Returns `IdentifierRuleError[]` from `processNode()`
- Gets registered in NEON config under `services` with tag `phpstan.rules.rule`
- Receives configuration via constructor injection from NEON parameters

### Rule Categories & Config

- **`src/Common/`** → `config/common.neon` — Universal rules (mb functions, annotations, use aliases, attribute requirements)
- **`src/Controller/`** → `config/controller.neon` — Symfony controller rules (IsGranted, HandleTrait return types)
- **`src/Coala/`** → `config/coala.neon` — Rules for optional Coala packages (DateProvider, Messenger, Testing). These silently skip when their target package isn't installed, checked via `ReflectionProvider::hasClass()`

Entry point: `rules.neon` includes all three config files. Each config defines `parametersSchema`, default `parameters` under `iwfWeb` key, and `services`.

### Shared Traits (`src/Concern/`)

- **AttributeFinderTrait** — `hasAttribute()`, `methodHasAttribute()` for searching attribute groups
- **NamespaceMatcherTrait** — `matchesNamespace()` for prefix-based namespace checks
- **RequiredUseAliasMatcherTrait** (in `src/Common/`) — Shared between `RequiredUseAliasRule` and `RequiredUseAliasGroupUseRule`

### Test Structure

- **`tests/AbstractRuleTestCase.php`** — Base class extending PHPStan's `RuleTestCase`. Provides `assertRuleErrors()`, `assertNoRuleErrors()`, and `assertRuleErrorsByAnnotation()`
- One test class per rule in `tests/`, extending `AbstractRuleTestCase<RuleClass>`
- Test data in `tests/data/` — PHP files with `// @error iwfWeb.ruleIdentifier` annotations on lines that should trigger errors
- Convention: `rule-name.php` for failing cases, `rule-name-correct.php` for passing cases

### Adding a New Rule

1. Create rule class in `src/Category/` implementing `Rule<NodeType>` with `IDENTIFIER` constant
2. Register in appropriate `config/*.neon` with `parametersSchema` if configurable
3. Create test class in `tests/` extending `AbstractRuleTestCase`
4. Add test data files in `tests/data/` with `@error` annotations
5. Run `bin/test.sh`

## Code Style

- PHP CS Fixer with IWF custom standard (`iwf-web/php-coding-standard`)
- PHPStan level max with bleeding edge enabled
- Conventional Commits for git messages (`feat:`, `fix:`, `refactor:`, `chore:`)
