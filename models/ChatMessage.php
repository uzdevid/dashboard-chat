<?php

namespace uzdevid\dashboard\chat\models;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "chat_message".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $participant_id
 * @property int|null $message_id
 * @property string $type
 * @property string $source
 * @property int $is_read
 * @property int $read_time
 * @property int $create_time
 *
 * @property Chat $chat
 * @property ChatMessage[] $chatMessages
 * @property ChatMessage $message
 * @property ChatParticipant $participant
 */
class ChatMessage extends ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'chat_message';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['chat_id', 'participant_id', 'type', 'source', 'create_time'], 'required'],
            [['chat_id', 'participant_id', 'message_id', 'is_read', 'read_time', 'create_time'], 'integer'],
            [['source'], 'string'],
            [['type'], 'string', 'max' => 255],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['message_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatMessage::class, 'targetAttribute' => ['message_id' => 'id']],
            [['participant_id'], 'exist', 'skipOnError' => true, 'targetClass' => ChatParticipant::class, 'targetAttribute' => ['participant_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'chat_id' => Yii::t('app', 'Chat ID'),
            'participant_id' => Yii::t('app', 'Participant ID'),
            'message_id' => Yii::t('app', 'Message ID'),
            'type' => Yii::t('app', 'Type'),
            'source' => Yii::t('app', 'Source'),
            'is_read' => Yii::t('app', 'Is Read'),
            'read_time' => Yii::t('app', 'Read time'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return ActiveQuery
     */
    public function getChat(): ActiveQuery {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[ChatMessages]].
     *
     * @return ActiveQuery
     */
    public function getChatMessages(): ActiveQuery {
        return $this->hasMany(ChatMessage::class, ['message_id' => 'id']);
    }

    /**
     * Gets query for [[Message]].
     *
     * @return ActiveQuery
     */
    public function getMessage(): ActiveQuery {
        return $this->hasOne(ChatMessage::class, ['id' => 'message_id']);
    }

    /**
     * Gets query for [[Participant]].
     *
     * @return ActiveQuery
     */
    public function getParticipant(): ActiveQuery {
        return $this->hasOne(ChatParticipant::class, ['id' => 'participant_id']);
    }
}
