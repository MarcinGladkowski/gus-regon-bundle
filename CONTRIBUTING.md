# GUS Bundle

## Getting Started

### Development Setup

1. Clone the repository
2. Install dependencies:
   ```bash
   cd gus-bundle
   composer install
   ```
3. Configure environment:
   ```bash
   cp .env.example .env.local
   # Edit .env.local with your test API key
   ```

## Development Guidelines

### Code Style

- Follow PSR-12 coding standards
- Use strict typing: `declare(strict_types=1);`
- Use PHP 8.2+ features (readonly properties, constructor promotion, etc.)
- Write self-documenting code with clear variable names
- Add PHPDoc only when necessary to explain complex logic

### Git Commit Conventions

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.
Structure your commit messages as follows:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

**Common usage:**
- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Changes that do not affect the meaning of the code (white-space, formatting, etc)
- `refactor`: A code change that neither fixes a bug nor adds a feature
- `perf`: A code change that improves performance
- `test`: Adding missing tests or correcting existing tests
- `chore`: Changes to the build process or auxiliary tools and libraries such as documentation generation

**VS Code Extension:**
This project includes a recommendation for the `vivaxy.vscode-conventional-commits` extension. When opening the project in VS Code, you should be prompted to install it. It helps you draft commit messages that follow this convention.

### Testing

All new code must include tests:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test suite
./vendor/bin/phpunit tests/Unit/Validator

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

**Test Requirements:**
- Unit tests for all public methods
- Test both success and failure scenarios
- Use descriptive test method names
- Follow Arrange-Act-Assert pattern

### Adding New Features

1. Create feature branch: `git checkout -b feature/your-feature-name`
2. Write failing tests first (TDD approach)
3. Implement the feature
4. Ensure all tests pass
5. Update documentation
6. Submit pull request

### Project Structure

```
src/
├── Client/          # API client layer
├── Service/         # Business logic layer
├── DTO/            # Data transfer objects
├── Validator/      # Input validation
├── Cache/          # Caching strategies
└── Exception/      # Custom exceptions
```

**Guidelines by Component:**

#### Client Layer
- Must implement interface
- Handle all SOAP/API errors
- Log API calls
- Support retry logic

#### Service Layer
- Implement business logic
- Use caching
- Return DTOs, never raw API responses
- Handle exceptions gracefully

#### DTOs
- Use readonly properties
- Provide `toArray()` method
- Add helper methods for common operations
- Keep immutable

#### Validators
- Pure functions (no side effects)
- Include normalization methods
- Comprehensive validation rules
- Well-tested edge cases

#### Exceptions
- Extend base `GusApiException`
- Include API error codes when available
- Provide meaningful error messages
- Support exception chaining


**Examples:**
```
feat(client): add support for batch REGON lookup

Implement batch lookup method to reduce API calls when
verifying multiple companies.

Closes #42
```

```
fix(validator): correct NIP checksum calculation for edge case

The validator was failing for NIPs starting with 0.
Updated weight calculation to handle this scenario.

Fixes #43
```

## Pull Request Process

1. Ensure all tests pass
2. Update documentation
3. Add entry to CHANGELOG.md
4. Request review from maintainers
5. Address review feedback
6. Squash commits if requested

### PR Checklist

- [ ] Tests added/updated
- [ ] All tests pass
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Code follows style guidelines
- [ ] No breaking changes (or clearly documented)
- [ ] Commits are clear and descriptive

## Bug Reports

When reporting bugs, include:

1. GUS Bundle version
2. PHP version
3. Symfony version
4. Steps to reproduce
5. Expected behavior
6. Actual behavior
7. Error messages/stack traces
8. API environment (test/production)

**Template:**
```markdown
## Bug Description
[Clear description of the bug]

## Environment
- GUS Bundle: 1.0.0
- PHP: 8.2.0
- Symfony: 7.0.0
- Environment: test

## Steps to Reproduce
1. Configure API key
2. Call `$service->getByRegon('123456785')`
3. Observe error

## Expected Behavior
Should return BusinessDataDTO

## Actual Behavior
Throws ApiConnectionException

## Error Message
```
[error message here]
```

## Additional Context
[Any other relevant information]
```

## Feature Requests

Feature requests are welcome! Please include:

1. Use case description
2. Expected behavior
3. Example code/API
4. Benefits
5. Alternative solutions considered

## Code Review Process

All submissions require review:

1. Automated tests must pass
2. Code review by maintainer
3. Documentation review
4. Breaking changes require discussion

## Performance Considerations

When contributing, consider:

- Cache efficiency
- API call reduction
- Memory usage
- Response time
- Database queries (if applicable)

## Security

If you discover a security vulnerability:

1. **DO NOT** open a public issue
2. Email maintainers directly
3. Include detailed description
4. Provide steps to reproduce
5. Suggest a fix if possible

## Questions?

- Open a discussion on GitHub
- Check existing issues
- Review documentation

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Recognition

Contributors will be recognized in:
- CHANGELOG.md
- README.md (for significant contributions)
- GitHub contributors page

Thank you for contributing! 🎉
