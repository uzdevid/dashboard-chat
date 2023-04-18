<?php

namespace uzdevid\dashboard\chat\widgets\ChatRoom;

use uzdevid\dashboard\chat\models\Chat;
use uzdevid\dashboard\offcanvaspage\OffCanvas;
use uzdevid\dashboard\offcanvaspage\OffCanvasOptions;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class ChatRoom extends Widget {
    public int|null $id;

    /**
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function init() {
        if (class_exists(OffCanvas::class)) {
            throw new InvalidConfigException(Yii::t('system.error', 'Extension "{name}" not found', ['name' => 'uzdevid/yii2-offcanvas-page']));
        }

        if (empty($this->id)) {
            throw new BadRequestHttpException(Yii::t('system.error', 'Chat id is empty'));
        }

        parent::init();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function run() {
        $chat = Chat::findOne($this->id);

        if ($chat == null) {
            throw new NotFoundHttpException(Yii::t('system.error', 'Chat not found'));
        }

        if (Yii::$app->request->isAjax) {
            $offcanvas = OffCanvas::options(OffCanvasOptions::SIDE_RIGHT);
            $view = $this->render('index', compact('chat'));

            $companion = null;
            foreach ($chat->chatParticipants as $participant) {
                if ($participant->user_id != Yii::$app->user->id) {
                    $companion = $participant;
                    break;
                }
            }

            return [
                'success' => true,
                'offcanvas' => $offcanvas,
                'body' => [
                    'title' => OffCanvas::title($companion->user->fullname, '<i class="bi bi-chat-right-text"></i>'),
                    'view' => $view
                ]
            ];
        }
    }
}