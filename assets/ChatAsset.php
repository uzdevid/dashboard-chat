<?php

namespace uzdevid\dashboard\chat\assets;

use yii\web\AssetBundle;

class ChatAsset extends AssetBundle {
    public $sourcePath = '@vendor/uzdevid/yii2-dashboard-chat/assets';
    public $css = [];
    public $js = [
        'js/chats.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}