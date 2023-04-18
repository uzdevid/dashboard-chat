<?php

use yii\db\Migration;

/**
 * Class m230418_195915_chat
 */
class m230418_195915_chat extends Migration {
    /**
     * {@inheritdoc}
     */
    public function safeUp() {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        echo "m230418_195915_chat cannot be reverted.\n";

        return false;
    }

    public function up() {
        $this->createTable('chat', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->null()->defaultValue(null),
            'user_id' => $this->integer(11)->notNull(),
            'create_time' => $this->integer()->notNull(),
        ]);

        $this->createTable('chat_participant', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer(11)->notNull(),
            'user_id' => $this->integer(11)->notNull()
        ]);

        $this->createTable('chat_message', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->integer(11)->notNull(),
            'participant_id' => $this->integer(11)->notNull(),
            'message_id' => $this->integer(11)->null()->defaultValue(null),
            'type' => $this->string(255)->notNull(),
            'source' => $this->text()->notNull(),
            'is_read' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'read_time' => $this->integer(11)->null()->defaultValue(null),
            'create_time' => $this->integer(11)->notNull(),
        ]);

        $this->addForeignKey('fk_chat_user_id', 'chat', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_chat_participant_chat_id', 'chat_participant', 'chat_id', 'chat', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_chat_participant_user_id', 'chat_participant', 'user_id', 'user', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_chat_message_chat_id', 'chat_message', 'chat_id', 'chat', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_chat_message_participant_id', 'chat_message', 'participant_id', 'chat_participant', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_chat_message_message_id', 'chat_message', 'message_id', 'chat_message', 'id', 'CASCADE', 'CASCADE');
    }

    public function down(): bool {
        echo "m230418_195915_chat cannot be reverted.\n";

        return false;
    }
}
