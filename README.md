# drpl.sebastix.dev

Default and clean Drupal sandbox project.

### CI/CD

### Start development

1. `cd docker`
2. `docker compose up -d`
3. `docker compose exec drpl_drupal bash`
4. `composer install`
5. `drush si` for a clean site install
6. `drush cim` for importing current config files

### Updates

1. `composer update -W`
2. `drush updb`
3. `drush cex`

### What modules are included?

* Config split
  * Development
  * Acceptance
  * Production
* Drush
* Raven
* Backup Migrate
* Reroute email
* Symfony Mailer
* Admin Toolbar
* Gin
* Pathauto
* Masquerade
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
