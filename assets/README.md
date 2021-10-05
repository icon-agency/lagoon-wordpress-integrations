# Icon Agency Drupal

## Requirements

- [Docker](https://docs.docker.com/install/)
- [Lando](https://docs.lando.dev/basics/installation.html#system-requirements)

## Local environment setup

- [Getting Started](https://dev.iconagency.com.au/)
- [Start a new project](https://dev.iconagency.com.au/#/lando-new-project)
- [Start an existing project](https://dev.iconagency.com.au/#/lando-start)

## FAQ

### Installing plugins/themes

https://wpackagist.org/

SSH into Lando

```bash
lando ssh
```

Example install plugin:

```bash
composer require wpackagist-plugin/akismet
```

Example install theme:

```bash
composer require wpackagist-theme/astra
```
