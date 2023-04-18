<?php

namespace uzdevid\dashboard\chat\workers;

use app\models\service\ChatService;
use app\models\service\MessageService;
use Exception;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;

class ChatWorker extends Controller {
    private array $connections = [];
    private array $users = [];

    public function actionRun() {
        $worker = new Worker(Yii::$app->params['chat']['socketName']);

        $worker->onConnect = [$this, 'onConnect'];

        $worker->onClose = [$this, 'onClose'];

        $worker->onMessage = [$this, 'onMessage'];

        Worker::runAll();
    }

    /**
     * @param TcpConnection $connection
     */
    private function onConnect(TcpConnection $connection): void {
        $this->connections[$connection->id]['connection'] = $connection;
    }

    /**
     * @param TcpConnection $connection
     */
    private function onClose(TcpConnection $connection): void {
        unset($this->connections[$connection->id]);
    }

    /**
     * @param TcpConnection $connection
     * @param mixed $request
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function onMessage(TcpConnection $connection, mixed $request): void {
        $request = json_decode($request, true);
        $this->connections[$connection->id]['user']['id'] = $request['data']['user_id'];
        $this->users[$request['data']['user_id']]['connection_id'] = $connection->id;

        switch ($request['method']) {
            case 'getChats':
                $this->handleGetChats($connection, $request);
                break;
            case 'sendMessage':
                $this->handleSendMessage($connection, $request);
                break;
            case 'getMessages':
                $this->handleGetMessages($connection, $request);
                break;
            case 'messageRead':
                $this->handleMessageRead($connection, $request);
                break;
        }
    }

    private function sendToConnection(TcpConnection $connection, array $data): void {
        $connection->send(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param TcpConnection $connection
     * @param array $request
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function handleSendMessage(TcpConnection $connection, array $request): void {
        $userId = $request['data']['user_id'];
        $chatId = $request['data']['chat_id'];
        $text = $request['data']['source'];

        $participant = MessageService::getParticipant($chatId, $userId);

        $message = MessageService::createMessage($participant->id, $chatId, $text);

        foreach ($this->connections as $connection) {
            $this->sendToConnection($connection['connection'], [
                'method' => 'getChats',
                'chats' => ChatService::getChats($connection['user']['id']),
            ]);

            if (!in_array($connection['user']['id'], ChatService::participantsUserIds($chatId))) {
                continue;
            }

            $participant = MessageService::getParticipant($chatId, $connection['user']['id']);

            $this->sendToConnection($connection['connection'], [
                'method' => 'newMessage',
                'message' => MessageService::getMessage($message, $participant),
            ]);
        }
    }

    /**
     * @param TcpConnection $connection
     * @param array $request
     *
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function handleGetChats(TcpConnection $connection, array $request): void {
        $this->sendToConnection($connection, [
            'method' => $request['method'],
            'chats' => ChatService::getChats($request['data']['user_id']),
        ]);
    }

    /**
     * @param TcpConnection $connection
     * @param array $request
     *
     * @throws Exception
     */
    private function handleGetMessages(TcpConnection $connection, array $request): void {
        $userId = $request['data']['user_id'];
        $chatId = $request['data']['chat_id'];

        $connection->send(json_encode([
            'method' => $request['method'],
            'messages' => MessageService::getMessages($chatId, $userId),
        ], JSON_UNESCAPED_UNICODE));
    }

    private function handleMessageRead(TcpConnection $connection, array $request) {
        $message = MessageService::read($request['data']['message_id']);

        $connectionId = $this->users[$message->participant->user_id]['connection_id'];
        $userConnection = $this->connections[$connectionId]['connection'];

        $this->sendToConnection($userConnection, [
            'method' => $request['method'],
            'message_id' => $request['data']['message_id'],
        ]);

        $this->sendToConnection($connection, [
            'method' => 'chatUnreadCounterDown',
            'chat_id' => $message->chat_id,
        ]);
    }
}
