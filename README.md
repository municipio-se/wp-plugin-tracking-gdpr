# Installation

1. Add the following to your `composer.json` file:
   ```json
   {
     "repositories": [
       {
         "type": "vcs",
         "url": "https://github.com/whitespace-se/wp-plugin-tracking-gdpr.git",
         "only": ["whitespace-se/wp-plugin-tracking-gdpr"],
         "no-api": true
       }
     ]
   }
   ```
2. Install the package and its dependencies:
   ```bash
   composer require whitespace-se/wp-plugin-tracking-gdpr:dev-main
   ```
