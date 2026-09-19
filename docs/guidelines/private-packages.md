# Private packages

Private packages aren't on Packagist. Apps install them straight from their GitHub repository, by tag.

## 1. Point the app at the repository

In the app's `composer.json`:

```json
{
    "repositories": [
        { "type": "vcs", "url": "https://github.com/svnjn/invoices" }
    ],
    "require": {
        "svnjn/invoices": "^1.0"
    }
}
```

Or from the command line:

```bash
composer config repositories.svnjn-invoices vcs https://github.com/svnjn/invoices
composer require svnjn/invoices:^1.0
```

## 2. Give Composer access

Create a GitHub fine-grained personal access token with **read-only access to the contents** of the svnjn repositories the app needs.

- **Your machine:** store it once, globally:

  ```bash
  composer config --global github-oauth.github.com <token>
  ```

- **CI and servers:** set the `COMPOSER_AUTH` environment variable (a repository or organisation secret):

  ```json
  {"github-oauth": {"github.com": "<token>"}}
  ```

  Packages built from the starter kit already pass a `COMPOSER_AUTH` secret to CI, for packages that depend on other private packages.

Never commit a token, including in `auth.json`.

## 3. Updating

Apps pick up new versions with `composer update svnjn/invoices` like any other package. Only tagged versions matching the constraint are installed.

## Private packages depending on each other

A private package may require another private package; both need the `repositories` entry in the **app**, because Composer only reads `repositories` from the root project. A public package must never depend on a private one.
