function setConnection() {
    const chatsConnection = new WebSocket(CHAT_WORKER_URL);

    chatsConnection.onopen = () => {
        const raw = {
            method: 'getChats',
            data: {
                user_id: USER_ID
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
