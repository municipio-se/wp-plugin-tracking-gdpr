# Tracking GDPR Plugin

This plugin is part of [Municipio LTS](https://github.com/municipio-se/municipio-lts).

## Installation

1. Add the following to your `composer.json` file:
   ```json
   {
     "repositories": [
       {
         "type": "vcs",
         "url": "https://github.com/municipio-se/wp-plugin-tracking-gdpr.git",
         "only": ["municipio/wp-plugin-tracking-gdpr"],
         "no-api": true
       }
     ]
   }
   ```
2. Install the package and its dependencies:
   ```bash
   composer require municipio/wp-plugin-tracking-gdpr:dev-main
   ```
