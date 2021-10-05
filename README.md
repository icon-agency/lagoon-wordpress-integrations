# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.

Opinionated config and structure for development. Recommended base modules and patches.

## Usage

```yml
  "require": {
    "iconagency/wordpress_integrations": "^5.0.0",
  },
  "extra": {
    "enable-patching": true,
    "drupal-scaffold": {
      "allowed-packages": [
        "iconagency/wordpress_integrations"
      ],
      "locations": {
        "web-root": "web/"
      }
    }
  }
```

Package borrows the drupal-scaffold functionality, because it's good.

## This library
- https://packagist.org/packages/iconagency/drupal_integrations
- https://bitbucket.org/iconagency/lagoon-drupal-integrations

## Icon Agency docs
- https://dev.iconagency.com.au/

## Drupal project
- https://bitbucket.org/iconagency/lagoon-drupal/

## Recommends
- https://github.com/amazeeio/drupal-integrations