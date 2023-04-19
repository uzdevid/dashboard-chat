<?php

namespace uzdevid\dashboard\chat\widgets\Chat;

use uzdevid\dashboard\chat\models\Chat as ChatModel;
use uzdevid\dashboard\chat\models\service\ChatService;
use uzdevid\dashboard\offcanvaspage\OffCanvas;
use uzdevid\dashboard\offcanvaspage\OffCanvasOptions;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class Chat extends Widget {
    public string|null $id = null;
    public int|null $companionId = null;

    /**
     * @throws InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function init() {
        parent::init();
    }

    /**
     * @throws NotFoundHttpException
     */
    public function run() {
        if ($this->id === null) {
            $chat = ChatService::createFakeChat($this->companionId);
        } else {
            $chat = ChatModel::findOne($this->id);

            if ($chat == null) {
                throw new NotFoundHttpException(Yii::t('system.message', 'Chat not found'));
            }
        }

        if (Yii::$app->request->isAjax) {
            $offcanvas = OffCanvas::options(OffCanvasOptions::SIDE_RIGHT);

            $companion = null;
            foreach ($chat->chatParticipants as $participant) {
                if ($participant->user_id != Yii::$app->user->id) {
                    $companion = $participant;
                    break;
                }
            }

            $view = $this->render('index', compact('chat', 'companion'));

            return json_encode([
                'success' => true,
                'offcanvas' => $offcanvas,
                'body' => [
                    'title' => OffCanvas::title($companion->user->fullname, '<i class="bi bi-chat-right-text"></i>'),
                    'view' => $view
                ]
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}