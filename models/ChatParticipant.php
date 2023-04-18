<?php

namespace uzdevid\dashboard\chat\models;

use uzdevid\dashboard\models\User;
use Yii;

/**
 * This is the model class for table "chat_participant".
 *
 * @property int $id
 * @property int $chat_id
 * @property int $user_id
 *
 * @property Chat $chat
 * @property ChatMessage[] $chatMessages
 * @property User $user
 */
class ChatParticipant extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'chat_participant';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['chat_id', 'user_id'], 'required'],
            [['chat_id', 'user_id'], 'integer'],
            [['chat_id'], 'exist', 'skipOnError' => true, 'targetClass' => Chat::class, 'targetAttribute' => ['chat_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'chat_id' => Yii::t('app', 'Chat ID'),
            'user_id' => Yii::t('app', 'User ID'),
        ];
    }

    /**
     * Gets query for [[Chat]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChat() {
        return $this->hasOne(Chat::class, ['id' => 'chat_id']);
    }

    /**
     * Gets query for [[ChatMessages]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChatMessages() {
        return $this->hasMany(ChatMessage::class, ['participant_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
