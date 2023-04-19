<?php

namespace uzdevid\dashboard\chat\models\service;

use Exception;
use uzdevid\dashboard\chat\models\ChatMessage;
use uzdevid\dashboard\chat\models\ChatParticipant;
use Yii;
use yii\base\InvalidConfigException;

class MessageService {
    /**
     * @param $chatId
     * @param $userId
     * @return array
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function getMessages($chatId, $userId): array {
        if ($chatId == 0) {
            return [];
        }

        $messages = ChatMessage::find()
            ->where(['chat_id' => $chatId])
            ->orderBy(['create_time' => SORT_ASC])
            ->all();

        $participant = self::getParticipant($chatId, $userId);

        return array_map(function (ChatMessage $message) use ($participant) {
            return self::getMessage($message, $participant);
        }, $messages);
    }


    /**
     * @param ChatMessage $message
     * @param ChatParticipant $participant
     * @return array
     *
     * @throws InvalidConfigException
     */
    public static function getMessage(ChatMessage $message, ChatParticipant $participant): array {
        return [
            'id' => $message->id,
            'user' => [
                'id' => $message->participant->user->id,
                'image' => $message->participant->user->profileImage,
                'fullName' => $message->participant->user->fullname,
            ],
            'position' => $message->participant_id == $participant->id ? 'right' : 'left',
            'type' => $message->type,
            'is_read' => $message->is_read,
            'read_time' => $message->read_time,
            'source' => $message->source,
            'create_time' => self::createTime($message->create_time),
            'create_time_seconds' => $message->create_time,
        ];
    }

    /**
     * @param int $chatId
     * @param int $userId
     * @return ChatParticipant
     *
     * @throws Exception
     */
    public static function getParticipant(int $chatId, int $userId): ChatParticipant {
        /** @var ChatParticipant $participant */

        $participant = ChatParticipant::find()
            ->where(['chat_id' => $chatId, 'user_id' => $userId])
            ->one();

        if ($participant == null) {
            throw new Exception('Participant not found');
        }

        return $participant;
    }

    /**
     * @param int $participantId
     * @param int $chatId
     * @param mixed $text
     * @return ChatMessage
     */
    public static function createMessage(int $participantId, int $chatId, mixed $text): ChatMessage {
        $message = new ChatMessage();
        $message->chat_id = $chatId;
        $message->participant_id = $participantId;
        $message->type = 'text';
        $message->source = $text;
        $message->is_read = 0;
        $message->read_time = null;
        $message->create_time = time();
        $message->save();

        return $message;
    }

    /**
     * @param int $time
     * @return string|null
     *
     * @throws InvalidConfigException
     */
    public static function createTime(int $time): ?string {
        if (strtotime(date('Y-m-d', $time)) == strtotime(date('Y-m-d'))) {
            return Yii::$app->formatter->asTime($time);
        } elseif (date('Y', $time) == date('Y')) {
            return Yii::$app->formatter->asDatetime($time, 'php:H:i jS F');
        }

        return Yii::$app->formatter->asDatetime($time);
    }

    public static function read(int $messageId): ChatMessage {
        $message = ChatMessage::findOne($messageId);

        if ($message->is_read == 0) {
            $message->is_read = 1;
            $message->read_time = time();
            $message->save();
        }

        return $message;
    }
}