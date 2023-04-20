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

config/web.php

```php
'modules' => [
    'system' => [
        'class' => uzdevid\dashboard\modules\system\Module::class,
        'modules' => [
            'api' => uzdevid\dashboard\modules\system\modules\api\Module::class,
        ],
        'controllerMap' => [
            'chat' => \uzdevid\dashboard\chat\controllers\ChatController::class
        ]
    ],
    'tamaddun' => app\modules\tamaddun\Module::class,
],
```