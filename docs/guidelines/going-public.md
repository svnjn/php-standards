# Going public

Checklist for turning a private package into an open-source one.

## Code and history

- [ ] No private dependencies: `composer.json` requires only public packages.
- [ ] No secrets, internal URLs, customer names or other private data anywhere in the code **or the git history**. When in doubt, publish a fresh repository with a squashed history.
- [ ] `composer check` passes.

## Licence and community files

- [ ] Replace `LICENSE.md` with the MIT licence, and set `"license": "MIT"` in `composer.json`.
- [ ] Add `CONTRIBUTING.md`, `SECURITY.md` and `CODE_OF_CONDUCT.md` (copy them from a public package made with the starter kit, or generate a throwaway public package with `php configure.php --visibility=public` and take them from there).
- [ ] README: replace the private install instructions with `composer require svnjn/<name>`.
- [ ] `docs/installation.md`: same.

## CI

- [ ] In `.github/workflows/ci.yml`, set `visibility: public`. Public repositories get free CI minutes, so the full matrix runs on every push.

## Publish

- [ ] Make the GitHub repository public.
- [ ] Submit it at [packagist.org/packages/submit](https://packagist.org/packages/submit).
- [ ] Make Packagist update automatically. If the package page says it isn't auto-updated, Packagist couldn't add its webhook (the `svnjn` organization hasn't granted it access). Add it yourself under the repository's **Settings → Webhooks → Add webhook**:
  - Payload URL: `https://packagist.org/api/github?username=<your Packagist username>`
  - Content type: `application/json`
  - Secret: your Packagist API token (from your Packagist profile)
  - Events: just the push event
- [ ] Apps can drop the `repositories` entry for the package.
