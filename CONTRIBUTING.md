# Contributing to Solo-AI-TW2

## 🤝 Welcome!

Thank you for your interest in contributing to the TWLan AI Bot System!

## 📋 Development Workflow

### Branch Strategy
- `main` - Production-ready code
- `develop` - Integration branch for features
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Urgent production fixes

### Creating a Feature Branch
```bash
git checkout develop
git pull origin develop
git checkout -b feature/your-feature-name
```

### Commit Message Format
We follow [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add new AI personality type
fix: resolve memory leak in decision resolver
docs: update guardrails documentation
refactor: optimize world snapshot queries
test: add unit tests for memory system
chore: update dependencies
```

### Pull Request Process
1. Create feature branch from `develop`
2. Implement changes with tests
3. Update documentation
4. Push to origin
5. Create PR against `develop`
6. Wait for review and CI checks
7. Address feedback
8. Merge when approved

## 🧪 Testing

### Running Tests
```bash
cd ai-bots
python -m pytest tests/
```

### Test Coverage
- Aim for 80%+ coverage on new code
- All bug fixes must include regression tests
- Integration tests for major features

## 📝 Documentation

### Update Documentation When:
- Adding new features
- Changing existing behavior
- Adding configuration options
- Modifying database schema

### Documentation Locations
- `docs/` - Technical documentation
- `ai-bots/README.md` - Bot system overview
- Inline code comments for complex logic

## 🎯 Code Standards

### Python Style
- Follow PEP 8
- Use type hints
- Maximum line length: 100 characters
- Use descriptive variable names

### Example
```python
async def plan_attack(
    bot: AIBotState,
    village: VillageState,
    personality: PersonalityProfile,
    world: WorldSnapshot
) -> List[Decision]:
    """
    Plan attack decisions based on bot state and personality.
    
    Args:
        bot: Current bot state
        village: Village to plan from
        personality: Bot personality profile
        world: Current world snapshot
        
    Returns:
        List of attack decisions sorted by priority
    """
    decisions: List[Decision] = []
    # Implementation...
    return decisions
```

### SQL Standards
- Use parameterized queries (never string interpolation)
- Add indexes for frequently queried columns
- Include migration rollback scripts
- Comment complex queries

## 🐛 Bug Reports

### Good Bug Report Includes:
- Clear description
- Steps to reproduce
- Expected vs actual behavior
- Environment details (OS, Python version, etc.)
- Relevant logs/screenshots

### Template
```markdown
## Description
Brief description of the bug

## Steps to Reproduce
1. Start orchestrator
2. Wait for tick
3. Observe error

## Expected Behavior
Bot should attack target

## Actual Behavior
Bot crashes with error

## Environment
- OS: Windows 11
- Python: 3.11.5
- Docker: 24.0.6

## Logs
```
[ERROR] guardrails_error...
```
```

## 💡 Feature Requests

### Good Feature Request Includes:
- Clear use case
- Proposed solution
- Alternatives considered
- Impact assessment

## 🚀 Release Process

### Version Numbering
We use [Semantic Versioning](https://semver.org/):
- MAJOR: Breaking changes
- MINOR: New features (backwards compatible)
- PATCH: Bug fixes

### Release Checklist
- [ ] All tests passing
- [ ] Documentation updated
- [ ] CHANGELOG.md updated
- [ ] Version bumped
- [ ] Git tag created
- [ ] Docker image built

## 📞 Questions?

- Open an issue for technical questions
- Check existing documentation first
- Be respectful and constructive

## 📜 License

By contributing, you agree that your contributions will be licensed under the same license as the project.

---

**Thank you for contributing!** 🎉
