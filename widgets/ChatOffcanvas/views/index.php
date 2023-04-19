<div class="offcanvas offcanvas-end" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" id="chat-offcanvas" aria-labelledby="chat-offcanvas-label">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="chat-offcanvas-label">
            <i class="bi bi-chat-right-text"></i>
            <?php echo Yii::t('system.content', 'Chats'); ?>
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div id="chats-container" class="offcanvas-body">
        <ul id="chats" class="list-unstyled mb-0"></ul>
    </div>
</div>

<script>
    function setConnection() {
        const chatsConnection = new WebSocket("<?php echo Yii::$app->params['chat']['clientSocketName']; ?>");

        chatsConnection.onopen = () => {
            const raw = {
                method: 'getChats',
                data: {
                    user_id: "<?php echo Yii::$app->user->id; ?>"
                }
            };
            chatsConnection.send(JSON.stringify(raw));
        };

        chatsConnection.onerror = () => {
            toaster.error('Chat connection error', 'Error');
        };

        chatsConnection.onmessage = event => {
            const data = JSON.parse(event.data);

            if (data.method === 'getChats') {
                const chats = data.chats;
                let unreadMessages = 0;

                $('#chats').empty();

                for (const chat of chats) {
                    buildChat(chat);
                    unreadMessages += chat.unread;
                }

                $('#unread-messages').text(unreadMessages || '');
            }
        };
    }

    function buildChat(chat) {
        const template = `
    <li id="chat-${chat.id}" class="p-2 border-bottom">
      <div class="d-flex justify-content-between position-relative">
        <div class="d-flex flex-row">
          <img src="${chat.image}" alt="avatar" class="rounded-circle d-flex align-self-center me-3 shadow-1-strong" width="60">
          <div class="pt-1">
            <p class="fw-bold mb-0">${chat.title}</p>
            <p class="small text-muted">${chat.lastMessage.source}</p>
          </div>
        </div>
        <div class="pt-1">
          <p class="small text-muted mb-1">${chat.lastMessage.create_time}</p>
          <span id="chat-${chat.id}-unreads" class="badge bg-danger float-end">${chat.unread || ''}</span>
        </div>
        <a class="stretched-link in-offcanvas" href="/ru/system/chat/room?id=${chat.id}"></a>
      </div>
    </li>
  `;

        $('#chats').append(template);
    }

    setConnection();

</script>