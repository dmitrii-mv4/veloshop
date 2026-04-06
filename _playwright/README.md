# Automation & Visual Testing

This folder contains Playwright-based visual regression tests for comparing the implemented design against Figma reference screenshots.

## Quick Start

```bash
# Install dependencies
npm install

# Run visual tests
npm run test:visual

# Run tests with UI reporter
npm run test:ui
```

## Folder Structure

```
automation/
├── tests/                      # Playwright test files
│   └── visual-comparison.spec.js
├── screenshots/
│   ├── target/                 # Figma reference images (COMMIT THESE)
│   │   ├── figma-design.png
│   │   └── figma-design-mobile.png
│   └── current/                # Test output (IGNORED BY GIT)
├── playwright.config.js        # Playwright configuration
├── package.json                # Dependencies and scripts
└── REDESIGN_ARCHIVE.md         # Project documentation
```

## Adding New Visual Tests

1. Export new Figma design to `screenshots/target/`
2. Update test file with new comparison logic
3. Run tests: `npm run test:visual`
4. Review any differences

## Scripts

| Command | Description |
|---------|-------------|
| `npm test` | Run all tests |
| `npm run test:visual` | Run visual comparison tests only |
| `npm run test:ui` | Run tests with Playwright UI |
| `npm run test:debug` | Run tests in debug mode |

## Configuration

Edit `playwright.config.js` to change:
- Base URL (default: `http://dev.site02.loc`)
- Viewport sizes
- Browser configurations
- Timeout settings

## Troubleshooting

### Tests Timeout
Increase timeout in `playwright.config.js`:
```javascript
timeout: 60000, // 60 seconds
```

### Screenshots Don't Match
1. Check if Figma reference is up to date
2. Clear browser cache
3. Run test with `--debug` flag

### Site Not Loading
Ensure local development server is running at the configured base URL.
