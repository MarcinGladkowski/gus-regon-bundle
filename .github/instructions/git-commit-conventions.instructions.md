---
description: 'Standard for Git commit messages using Conventional Commits.'
applyTo: '**'
---

# Git Commit Conventions

Rules for generating git commit messages in this repository.

## Format

Structure your commit messages as follows:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

## Commit Types

Use one of the following types:

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries

## Guidelines

- **Description**: Use the imperative mood ("add" not "added"). No period at the end.
- **Scope**: Optional, but encouraged for identifying the component affected (e.g., `client`, `validator`, `dto`).
- **Body**: Optional. Use for complex changes to explain "what" and "why".
- **Footer**: Optional. Use for breaking changes (start with `BREAKING CHANGE:`) or closing issues (e.g., `Closes #42`).

## Examples

```
feat(client): add support for batch REGON lookup
```

```
fix(validator): correct NIP checksum calculation
```
