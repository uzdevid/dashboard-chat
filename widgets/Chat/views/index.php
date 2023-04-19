<?php
/***
 * @var \uzdevid\dashboard\chat\models\Chat $chat
 * @var \uzdevid\dashboard\chat\models\ChatParticipant $companion
 */
?>

<div id="messages-container" class="position-relative">
    <ul id="messages" class="list-unstyled no-scroll" style="position: relative; overflow-y: auto; height: calc(100vh - 200px);"></ul>

    <div class="bg-white w-100 position-absolute border-top pt-1">
        <textarea class="form-control border-0 outline-none" id="chat-textarea" style="resize: none; box-shadow: none;" rows="2" placeholder="<?php echo Yii::t('system.content', 'Message'); ?>"></textarea>
        <button type="button" onclick="send(this);" class="btn btn-primary mt-2 btn-rounded float-end">
            <i class="bi bi-send"></i>
            <?php echo Yii::t('system.content', 'Send'); ?>
        </button>
    </div>
</div>

<script>
    var messagesConnection = new WebSocket("<?php echo Yii::$app->params['chat']['clientSocketName']; ?>");

    messagesConnection.onopen = function (e) {
        let raw = {
            method: 'getMessages',
            data: {
                chat_id: "<?php echo $chat->id; ?>",
                user_id: "<?php echo Yii::$app->user->id; ?>"
            }
        }
        messagesConnection.send(JSON.stringify(raw));
    };

    messagesConnection.onerror = function (e) {
        toaster.error('Messages connection error', 'Error');
    };

    messagesConnection.onmessage = function (e) {
        const raw = JSON.parse(e.data);

        if (raw['method'] === 'getMessages') {
            raw['messages'].forEach(function (message) {
                buildMessage(message);
            });
        } else if (raw['method'] === 'newMessage') {
            buildMessage(raw['message']);
        } else if (raw['method'] === 'messageRead') {
            messageSetRead(raw['message_id']);
        } else if (raw['method'] === 'chatUnreadCounterDown') {
            chatUnreadCounterDown(raw['chat_id']);
        }
    };

    function send(btn) {
        btn.disabled = true;
        let raw = {
            method: 'sendMessage',
            data: {
                chat_id: "<?php echo $chat->id; ?>",
                user_id: "<?php echo Yii::$app->user->id; ?>",
                companion_user_id: "<?php echo $companion->user_id; ?>",
                source: $('#chat-textarea').val()
            }
        }
        messagesConnection.send(JSON.stringify(raw));
        $('#chat-textarea').val('');
        btn.disabled = false;
    }

    function buildMessage(message) {
        let template = $(`
                <li id="message-${message.id}" class="d-flex mb-3" data-position="${message.position}" data-create-time="${message.create_time_seconds}">
                    <div class="card w-100 m-0">
                        <div class="card-body pt-3">
                            <p class="mb-0" style="font-size: 14px;">${message.source}</p>
                        </div>
                        <div class="d-flex justify-content-end pb-1 px-2">
                            <p class="text-muted mb-0" style="font-size: 14px;"><i class="message-status"></i> ${message.create_time}</p>
                        </div>
                    </div>
                </li>`);

        let lastMessage = $('#messages li:last-child');

        let image = $(`<img src="${message.user.image}" alt="avatar" class="rounded-circle d-flex align-self-start shadow-1-strong" width="40">`);

        if (lastMessage && ($(lastMessage).data('position') != message.position || (message.create_time_seconds - $(lastMessage).data('create-time')) > 120)) {
            $(lastMessage).addClass('mb-5');

            if (message.position == 'left') {
                $(image).addClass('me-3');
                $(template).prepend(image);
                $(template).find('.card').addClass('bg-warning-light');
            } else {
                $(template).addClass('justify-content-end');
                $(template).find('.card').addClass('bg-primary-light');
                $(template).find('.card').css('max-width', '90%');
            }
        } else {
            if (message.position == 'right') {
                $(template).find('.card').addClass('bg-primary-light');
                $(template).addClass('justify-content-end');
            } else {
                $(template).find('.card').addClass('bg-warning-light');
                $(template).addClass('justify-content-start');
            }

            $(template).find('.card').css('max-width', '90%');
        }

        if (message.position == 'right') {
            if (message.is_read == 1) {
                $(template).find('.message-status').addClass('bi bi-check2-all');
            } else {
                $(template).find('.message-status').addClass('bi bi-check2');
            }
        }

        $('#messages').append(template);
        $('#messages').scrollTop($('#messages').prop('scrollHeight'));

        if (message.position == 'left' && message.is_read == 0) {
            messageRead(message.id);
        }
    }

    function messageRead(messageId) {
        let raw = {
            method: 'messageRead',
            data: {
                user_id: "<?php echo Yii::$app->user->id; ?>",
                message_id: messageId
            }
        }

        messagesConnection.send(JSON.stringify(raw));
    }

    function messageSetRead(messageId) {
        $(`#message-${messageId}`).find('.message-status').removeClass('bi-check2').addClass('bi-check2-all');
    }

    function chatUnreadCounterDown(chatId) {
        let totalCounter = $('#unread-messages');
        let totalCount = parseInt($(totalCounter).text());

        let counter = $(`#chat-${chatId}-unreads`);
        let count = parseInt($(counter).text());

        totalCount--;
        count--;

        if (count <= 0) {
            $(counter).empty();
        }

        if (totalCount <= 0) {
            $(totalCounter).empty();
        }
    }

    $('#offcanvas-page').on('hide.bs.offcanvas', function () {
        messagesConnection.close();
    });
</script>