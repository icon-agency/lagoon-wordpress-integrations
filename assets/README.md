# Icon Agency Wordpress

## Requirements

- [Docker](https://docs.docker.com/install/)
- [Lando](https://docs.lando.dev/basics/installation.html#system-requirements)

## Local environment setup

- [Getting Started](https://dev.iconagency.com.au/)
- [Start a new project](https://dev.iconagency.com.au/#/lando-new-project)
- [Start an existing project](https://dev.iconagency.com.au/#/lando-start)

## FAQ

### How do I override Lando?

Create .lando.local.yml file

### How can I optimize my Lando?

Take a look at [Lando performance](https://docs.lando.dev/config/performance.html#configuration)

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

### Should I commit the contrib modules I download?

Composer recommends **no**. They provide [argumentation against but also
workarounds if a project decides to do it anyway](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).

### How can I apply patches to downloaded modules?

If you need to apply patches (depending on the project being modified, a pull
request is often a better solution), you can do so with the
[composer-patches](https://github.com/cweagans/composer-patches) plugin.

To add a patch to wordpress module foobar insert the patches section in the extra
section of composer.json:

```json
"extra": {
    "patches": {
        "wpackagist-plugin/foobar": {
            "Patch description": "URL to patch"
        }
    }
}
```

### Installing Advanced Custom Fields

Add your key to `.env` and rebuild your container...

```conf
ACF_PRO_LICENSE="Check 1password for details..."
```

```bash
lando rebuild
```

Add the Icon Agency ACF repository to `composer.json`

```json
  "repositories": {
    "advanced-custom-fields": {
      "type": "composer",
      "url": "https://auth-acf-composer-proxy.iconagency.com.au/wordpress-muplugin/",
      "only": [
        "advanced-custom-fields/*"
      ]
    }
  },
```

Require ACF

```bash
lando composer require advanced-custom-fields/advanced-custom-fields-pro
```
