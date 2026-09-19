# Debugging

## Find the failing test fast

```bash
vendor/bin/pest --filter="issues an invoice"   # tests whose name matches
vendor/bin/pest tests/Unit/InvoiceTest.php     # one file
vendor/bin/pest --bail                         # stop at the first failure
```

Add `->only()` to a test to run just that one while you work on it (the arch preset doesn't catch it, so remove it before committing).

## Look at a value

- In a test: `dump($value)` prints it and keeps going; `dd($value)` prints it and stops. Both are allowed in `tests/` and banned in `src/`.
- In `src/`: throw, or step through with Xdebug. Don't leave debug calls behind; `composer check` fails on them.

## Step through with Xdebug

**VS Code:** the package ships `.vscode/launch.json` (install the recommended PHP Debug extension).

- **Debug current test file** runs the open test file under Xdebug and stops at your breakpoints.
- **Listen for Xdebug** waits for any PHP process started with Xdebug, such as the command below.

**PhpStorm:** set it up once per project:

1. **Settings → PHP**: pick your PHP interpreter (with Xdebug).
2. **Settings → PHP → Test Frameworks**: add **Pest**, using `vendor/bin/pest` and `phpunit.xml`.
3. Click the gutter icon next to a test and choose **Debug**.

**Any editor, from the terminal:** start listening in the editor, set a breakpoint, then run:

```bash
XDEBUG_MODE=debug XDEBUG_SESSION=1 vendor/bin/pest --filter="issues an invoice"
```

Don't use `--parallel` while debugging; worker processes won't stop at your breakpoint.

## Ask PHPStan what it thinks a type is

When PHPStan reports something surprising, put this in the code temporarily:

```php
\PHPStan\dumpType($invoice->lines);
```

`composer analyse` then prints the inferred type at that line. Remove it afterwards.

For more detail on a PHPStan run: `vendor/bin/phpstan analyse -vvv --debug path/to/File.php`.

## Coverage locally

`composer test:coverage` sets `XDEBUG_MODE=coverage` itself. CI uses PCOV, which is faster; locally Xdebug is fine.

## Debugging the Laravel workbench

`composer serve` runs the package inside a real Laravel app. The app's log is in `workbench/storage/logs/laravel.log`. Run Artisan commands against it with `vendor/bin/testbench <command>`.

To step through a request, start the server with Xdebug:

```bash
XDEBUG_MODE=debug XDEBUG_SESSION=1 composer serve
```
