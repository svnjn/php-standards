# Local linking

Try a package inside a real app before releasing it. The app uses your working copy through a symlink, so every change shows up immediately.

## Link

From the package folder:

```bash
vendor/bin/svnjn-link ../my-app
```

This adds a Composer path repository to the app and requires the package at `*@dev`. It remembers how the app required the package before (for example `^1.2` in `require-dev`) in the package's `.svnjn/links.json`.

## Unlink

```bash
vendor/bin/svnjn-unlink ../my-app
```

This removes the path repository and puts the app back on the constraint it had — or removes the package if the app didn't require it before.

## Things to know

- **Don't commit the app's `composer.json` or `composer.lock` while linked.** Unlink first.
- Laravel apps rediscover the package's service provider automatically when Composer runs.
- If you add a new dependency to the package, run `composer update svnjn/<name>` in the app so it installs too.
- Composer must be on your `PATH`, or set `COMPOSER_BINARY=/path/to/composer`.
