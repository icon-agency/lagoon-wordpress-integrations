# Icon Agency Lagoon Integrations

Icon Agency integration with Amazee Lagoon.

Opinionated config and structure for development. Recommended base modules and patches.

## Usage

```yml
  "require": {
    "iconagency/wordpress_integrations": "^6.0.0",
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

- https://packagist.org/packages/iconagency/wordpress_integrations
- https://bitbucket.org/iconagency/lagoon-wordpress-integrations

## Icon Agency docs

- https://dev.iconagency.com.au/

## Wordpress project

- https://bitbucket.org/iconagency/lagoon-wordpress/

## Attributes

- Lagoon Logs, Govind Maloo
