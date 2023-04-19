<?php

namespace uzdevid\dashboard\chat\models\service;

use Exception;
use uzdevid\dashboard\chat\models\Chat;
use uzdevid\dashboard\chat\models\ChatMessage;
use uzdevid\dashboard\chat\models\ChatParticipant;
use uzdevid\dashboard\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

class ChatService {
    /**
     * @param $user_id
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function getChats($user_id): array {
        $chats = Chat::find()
            ->joinWith([
                'chatParticipants' => function ($query) use ($user_id) {
                    $query->andWhere(['chat_participant.user_id' => $user_id]);
                },
                'chatMessages' => function ($query) use ($user_id) {
                    $query->orderBy(['chat_message.create_time' => SORT_DESC])->limit(1);
                }])->all();


        return array_map(function ($chat) use ($user_id) {
            return self::getChat($chat, $user_id);
        }, $chats);
    }

    /**
     * @param Chat $chat
     * @param int $userId
     * @return array
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function getChat(Chat $chat, int $userId): array {
        $companion = self::getCompanion($chat->id, $userId);

        return [
            'id' => $chat->id,
            'title' => $companion->user->fullname,
            'image' => $companion->user->profileImage,
            'unread' => self::getUnreadMessagesCount($chat, $companion),
            'lastMessage' => [
                'source' => $chat->lastMessage == null ? '' : mb_strimwidth($chat->lastMessage->source, 0, 20, '...'),
                'create_time' => $chat->lastMessage == null ? '' : MessageService::createTime($chat->lastMessage->create_time),
            ],
        ];
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @return ChatParticipant
     *
     * @throws Exception
     */
    public static function getCompanion(int $chatId, int $userId): ChatParticipant {
        /** @var ChatParticipant $companion */

        $companion = ChatParticipant::find()
            ->where(['chat_id' => $chatId])
            ->andWhere(['!=', 'user_id', $userId])
            ->one();

        if ($companion == null) {
            throw new Exception(Yii::t('system.error', 'Companion not found'));
        }

        return $companion;
    }

    public static function getUnreadMessagesCount(Chat $chat, ChatParticipant $companion): int {
        return ChatMessage::find()
            ->where(['chat_id' => $chat->id])
            ->andWhere(['participant_id' => $companion->id])
            ->andWhere(['is_read' => 0])
            ->count();
    }

    public static function participantsUserIds(int $chatId): array {
        return ChatParticipant::find()
            ->select(['user_id'])
            ->where(['chat_id' => $chatId])
            ->column();
    }

    public static function getChatId(...$userIds) {
        $chats = Chat::find()
            ->joinWith('chatParticipants')
            ->where(['chat_participant.user_id' => $userIds])
            ->groupBy('chat.id')
            ->having('COUNT(chat.id) = ' . count($userIds))
            ->all();

        if (!isset($chats[0])) {
            throw new NotFoundHttpException(Yii::t('system.error', 'Chat not found'));
        }

        return $chats[0]->id;
    }

    public static function createChat(int $createUserId, array $userIds) {
        $chat = new Chat();
        $chat->user_id = $createUserId;
        $chat->create_time = time();
        $chat->save();

        foreach ($userIds as $userId) {
            $chatParticipant = new ChatParticipant();
            $chatParticipant->chat_id = $chat->id;
            $chatParticipant->user_id = $userId;
            $chatParticipant->save();
        }

        return $chat;
    }

    public static function createFakeChat(int $companionId) {
        $companion = User::findOne($companionId);

        if ($companion == null) {
            throw new NotFoundHttpException(Yii::t('system.error', 'Companion not found'));
        }

        return (object)[
            'id' => 0,
            'title' => $companion->fullname,
            'image' => $companion->profileImage,
            'chatParticipants' => [
                (object)[
                    'user_id' => Yii::$app->user->id,
                    'user' => (object)[
                        'id' => Yii::$app->user->id,
                        'fullname' => Yii::$app->user->identity->fullname,
                    ]
                ],
                (object)[
                    'user_id' => $companion->id,
                    'user' => (object)[
                        'id' => $companion->id,
                        'fullname' => $companion->fullname,
                    ]
                ]
            ],
        ];
    }
}