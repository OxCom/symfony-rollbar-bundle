# Configuration
Simple configuration of bundle:
```php
symfony_rollbar:
  rollbar:
    access_token: 'some-secret-token-here'
    environment: '%kernel.environment%'
```

#### Common settings
```enable```: It's possible to enable or disable handling of errors and exceptions.  Default: ```true```

```rollbar``` - configuration parameters for Rollbar instance. Full list of options can be found on [official documentation](https://rollbar.com/docs/notifier/rollbar-php/) page for PHP lib.

#### RollBar settings
Here you can find a list of some configuration options for RollBar.

```access_token```: Your project access token.

```agent_log_location```: Path to the directory where agent relay log files should be written. Should not include final slash. Only used when handler is agent. Default: ```%kernel.logs_dir%/rollbar.log```

```environment```: Environment name, e.g. 'production' or 'development'. Default: ```production```
 
```root```: Path to your project's root dir. Default ```%kernel.root_dir%```

More configuration details can be found in [DependencyInjection/Configuration.php](https://github.com/OxCom/symfony3-rollbar-bundle/blob/master/src/DependencyInjection/Configuration.php)