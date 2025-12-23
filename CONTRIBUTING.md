# Contributing to Savefile-Manager

Thank you for considering a contribution! These guidelines help keep changes consistent and maintainable.

## Getting started

- Fork the repo and create a feature branch from `master`.
- Use clear branch names (e.g., `feature/add-backup-ui`, `bugfix/path-handling`).
- Ensure you have the required tooling installed (PHP/composer/docker/etc. as applicable).

## Development workflow

- Keep changes scoped and incremental.
- Prefer small, focused PRs over large, mixed changes.
- Update docs and tests alongside code changes.

## Coding standards

- Match the existing style and formatting used in the project.
- Follow language-idiomatic best practices; avoid introducing new dependencies without discussion.
- Keep functions cohesive and avoid unnecessary global state.

## Testing

- Add or update tests relevant to your changes.
- Run the full test suite (or applicable modules) before opening a PR and ensure it passes.
- Include repro steps for any bugfix.

## Commits

- Write clear, imperative commit messages (e.g., `Add savefile validation`).
- Group related changes into a single commit when possible.
- Reference issues with `Fixes #<id>` or `Refs #<id>` when appropriate.

## Pull requests

- Provide a concise summary of the change and the motivation.
- Include screenshots or logs when UI/UX or behavior changes.
- Ensure your branch is up to date with `master` before requesting review.
- Respond to review feedback promptly and keep discussions focused.

## Reporting issues

- Use the issue template when available.
- Provide environment details, steps to reproduce, expected vs actual behavior, and logs if relevant.

## Community standards

- Be respectful, constructive, and inclusive in all interactions.
- Follow the project’s license and avoid adding non-compatible code or assets.

## Release notes

- If your change is user-facing, suggest a brief entry for release notes in the PR description.
