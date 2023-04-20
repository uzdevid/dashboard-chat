# Chat for Dashboard panel

## Migration

config/console.php

```php
'controllerMap' => [
    'migrate' => [
        'class' => 'yii\console\controllers\MigrateController',
        'migrationPath' => [
            '@app/migrations',
            '@vendor/uzdevid/dashboard-chat/migrations'
        ],
    ],
],
```

config/console.php

```php
'controllerMap' => [
    'chat' => uzdevid\dashboard\chat\commands\ChatController::class,
],
```

and run command

```bash
yii chat/run
```