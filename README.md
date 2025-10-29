## Digital Project — DDEV Setup

This project is configured to run locally using DDEV (Docker-based). Follow the steps below to get up and running quickly.

### Prerequisites

- Docker (latest)
- DDEV (v1.23+)
- mkcert (optional, for local HTTPS certificates)

Install DDEV: see `https://ddev.readthedocs.io/en/stable/#installation`

### Quick Start

1) Start DDEV services:

```bash
ddev start
```

2) Install PHP dependencies:

```bash
ddev composer install
```

3) Install Drupal (fresh site) OR import an existing DB:

- Fresh install (uses standard profile by default; adjust as needed):

```bash
ddev drush si standard -y --account-name=admin --account-pass=admin --site-name="Digital Project"
```

- Import an existing SQL dump (replace path with your dump):

```bash
ddev import-db --src=path/to/dump.sql.gz
ddev drush cr
```

4) Set file permissions if needed (one-time):

```bash
ddev exec "mkdir -p web/sites/default/files && chmod -R u+rwX web/sites/default/files"
```

### Project Conventions

- Webroot: `web/`
- Custom modules: `web/modules/custom/`
- Custom themes: `web/themes/custom/`
- PHP version used in CI: 8.2

### URLs

After `ddev start`, DDEV prints your project URLs. Commonly:

- App: `https://<project>-ddev-site.ddev.site`
- Mailhog: `https://<project>-ddev-mailhog.ddev.site`
- phpMyAdmin: `https://<project>-ddev-phpmyadmin.ddev.site`

You can re-print them anytime:

```bash
ddev describe
```

### Common Commands

```bash
# Start/stop
ddev start
ddev stop

# Shell and Drush
ddev ssh
ddev drush cr
ddev drush uli
ddev drush updb -y

# Composer inside the container
ddev composer install
ddev composer require drupal/module_name

# Import/export DB
ddev export-db --file=.ddev/backups/export.sql.gz
ddev import-db --src=.ddev/backups/export.sql.gz

# Run PHPStan/PHPCS if configured
ddev exec vendor/bin/phpstan analyse
ddev exec vendor/bin/phpcs -q
```

### Xdebug (optional)

```bash
ddev xdebug on
# Configure your IDE to listen on port 9003 (default for Xdebug v3)
```

### CI Notes

GitHub Actions CI validates Composer and lints custom module PHP files. See `.github/workflows/ci.yml`.

### Troubleshooting

- Clear caches: `ddev drush cr`
- Rebuild container if base images changed: `ddev restart --skip-import-db`
- Check logs: `ddev logs`
- Verify settings: `ddev describe`


