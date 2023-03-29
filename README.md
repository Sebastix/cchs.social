# drpl.sebastix.dev

A clean Drupal sandbox project with some default configuration.

## Docker

See `docker/docker-compose.yml`.

## CI/CD

See `.gitlab-ci.yml`

## Start development

1. `cd docker`
2. `docker compose up -d`
3. `docker compose exec drpl_drupal bash`
4. `composer install`
5. `drush si` for a clean site install
6. `drush cim` for importing current config files
7. `drush cr`
8. Navigate to http://localhost in your browser

## Updates

1. `composer outdated`
2. `composer update -W`
3. `drush cr`
4. `drush updb`
5. `drush cex`
6. `composer clearcache`

## What modules are included?

* Config split
* Drush
* Raven
* Backup Migrate
* Symfony Mailer
* Admin Toolbar
* Gin
* Pathauto
* Masquerade
* Ultimate Cron
* Advanced CSS/JS Aggregation

Development only:
* Coder
* Devel
* Webprofiler
* Drupal Coder
* Drupal Rector

### Config split configurations

```php
$config['config_split.config_split.dev']['status'] = TRUE|FALSE;
$config['config_split.config_split.acceptance']['status'] = TRUE|FALSE;
$config['config_split.config_split.production']['status'] = TRUE|FALSE;
```
`settings.local.php`

#### Production

#### Acceptance

#### Development

## Security checks

@TODO - https://github.com/FriendsOfPHP/security-advisories and https://github.com/fabpot/local-php-security-checker

## Code checks

@TODO - https://www.drupal.org/project/coder
