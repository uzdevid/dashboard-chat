<?php

namespace app\models;

use uzdevid\dashboard\models\User;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "chat".
 *
 * @property int $id
 * @property string|null $name
 * @property int $user_id
 * @property int $create_time
 *
 * @property ChatMessage[] $chatMessages
 * @property ChatParticipant[] $chatParticipants
 * @property User $user
 *
 * @property ChatMessage $lastMessage
 */
class Chat extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return 'chat';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'create_time'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'user_id' => Yii::t('app', 'User ID'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }

    /**
     * Gets query for [[ChatMessages]].
     *
     * @return ActiveQuery
     */
    public function getChatMessages() {
        return $this->hasMany(ChatMessage::class, ['chat_id' => 'id']);
    }

    /**
     * Gets query for [[ChatParticipants]].
     *
     * @return ActiveQuery
     */
    public function getChatParticipants() {
        return $this->hasMany(ChatParticipant::class, ['chat_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser() {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getLastMessage(): ActiveQuery {
        return $this->hasOne(ChatMessage::class, ['chat_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
