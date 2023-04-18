# Chat for Dashboard panel

## Migration

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