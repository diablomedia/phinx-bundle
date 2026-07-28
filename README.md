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
    
1. Add bundle config to `config/packages/diablo_media_phinx.yaml`

   Minimal example:

   ```yaml
   diablo_media_phinx:
       environment:
           connection:
               dsn: 'mysql://db_user:db_password@127.0.0.1:3306/db_name'
   ```

### Configuration

The bundle can be installed and Symfony's cache can be built without any
configuration. A database connection is required before running commands that
access the database.

| Option | Type / default | Description |
| --- | --- | --- |
| `migration_base_class` | `class-string`; Phinx's `AbstractMigration` | Base class used by newly generated migrations. The class must extend Phinx's `AbstractMigration`. |
| `adapters` | map; empty | Registers custom Phinx database adapters. Each key is the adapter name used by a DSN and each value is a class implementing Phinx's `AdapterInterface`. |
| `paths.migrations` | string, list, or namespace map; `%kernel.project_dir%/src/Resources/db/migrations` | Location or locations scanned for migrations. A keyed map associates migration namespaces with paths. Glob patterns are supported by Phinx. |
| `paths.seeds` | string, list, or namespace map; `%kernel.project_dir%/src/Resources/db/seeds` | Location or locations scanned for seed classes. A keyed map associates seed namespaces with paths. |
| `environment.connection.dsn` | string; required when `connection` is configured | Database-agnostic connection URL in the form `<adapter>://[<user>[:<password>]@]<host>[:<port>]/<database>[?<options>]`. Common adapters are `mysql`, `pgsql`, `sqlite`, and `sqlsrv`. |
| `environment.migration_table` | string; `phinxlog` | Table used by Phinx to record which migrations have run. Schema-qualified names such as `phinx.log` are supported by databases such as PostgreSQL. |
| `environment.table_prefix` | string; none | Prefix Phinx applies to table names handled through its adapter. |
| `environment.table_suffix` | string; none | Suffix Phinx applies to table names handled through its adapter. |
| `environment.prompt_password` | boolean; `false` | Prompts for the database password using a hidden Symfony Console question before database-dependent commands run. Create commands do not prompt. |

For example:

```yaml
diablo_media_phinx:
    migration_base_class: App\Migration\AbstractMigration

    paths:
        migrations:
            App\Migrations: '%kernel.project_dir%/migrations'
        seeds:
            App\Seeds: '%kernel.project_dir%/seeds'

    adapters:
        custom: App\Database\CustomPhinxAdapter

    environment:
        migration_table: phinx_migrations
        table_prefix: app_
        connection:
            dsn: 'mysql://db_user:db_password@127.0.0.1:3306/db_name?charset=utf8mb4'
```

The bundle exposes a single Phinx environment named `default`; Symfony
configuration determines its connection and paths. See the
[Phinx configuration reference](https://book.cakephp.org/phinx/0/en/configuration.html)
for more detail about DSNs, migration paths, adapters, and migration behavior.

### Interactive database password

To keep the database password out of configuration and environment variables,
omit it from the DSN and enable the password prompt:

```yaml
diablo_media_phinx:
    environment:
        prompt_password: true
        connection:
            dsn: 'mysql://db_user@127.0.0.1:3306/db_name'
```

Database-dependent commands will ask for the password without displaying it:

```text
Database password:
```

Commands run with `--no-interaction` cannot prompt and will stop with an
explanatory error. For CI and automated deployments, use a separate Symfony
configuration that supplies a complete DSN and leaves `prompt_password`
disabled.
