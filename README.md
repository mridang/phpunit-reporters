# phpunit-reporters

**phpunit-reporters** is a PHPUnit extension that displays code coverage results in Jest/Istanbul's console format with a directory tree structure.

##### Why?

Using phpunit-reporters lets you see coverage in Jest's familiar table format right in your terminal. The reporter organizes files into a directory tree, shows uncovered line ranges (e.g., `5-12,18`), and applies the same color coding as Jest so your PHP coverage reports look exactly like your JavaScript ones.

## Usage

Add the package to your project:

```bash
composer require --dev mridang/phpunit-reporters
```

Then run your tests with coverage:

```bash
vendor/bin/phpunit --coverage-clover build/coverage/clover.xml
```

The Jest-style coverage table will automatically appear at the end of your test run.

#### Options

The plugin automatically detects terminal width and formats output accordingly. Coverage data is read from PHPUnit's clover.xml report, so use standard PHPUnit coverage options:

```bash
vendor/bin/phpunit --coverage-clover build/coverage/clover.xml
```

Ensure Xdebug or PCOV is installed and configured for coverage collection.

## Contributing

Contributions are welcome! If you find a bug or have suggestions for improvement, please open an issue or submit a pull request.

## License

Apache License 2.0 © 2025 Mridang Agarwalla
