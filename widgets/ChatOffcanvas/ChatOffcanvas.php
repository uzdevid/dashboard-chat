<?php

namespace uzdevid\dashboard\chat\widgets\ChatOffcanvas;

use yii\base\Widget;

class ChatOffcanvas extends Widget {
    public function run() {
        return $this->render('index');
    }
}