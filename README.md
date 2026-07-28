### Composer

The fastest way to install Phinx bundle is to add it to your project using Composer (http://getcomposer.org/).

1. Install Composer:

    ```
    curl -sS https://getcomposer.org/installer | php
    ```

1. Require Phinx bundle as a dependency using Composer:

    ```
    php composer.phar require diablomedia/phinx-bundle
    ```

1. Install bundle:

    ```
    php composer.phar install
    ```
    
1. Add bundle to `config/bundles.php`

    ```php
    return [
        // [...]
        DiabloMedia\PhinxBundle\DiabloMediaPhinxBundle::class => ['all' => true],
    ];
    ```
    
1. Add bundle config to `config/packages/diablomedia_phinx.yaml`

   Example:

   ```yaml
   diablomedia_phinx:
       environment:
           connection:
               dsn: 'mysql://db_user:db_password@127.0.0.1:3306/db_name'
   ```

   See `DependencyInjection/Configuration.php` for full list of available options.
