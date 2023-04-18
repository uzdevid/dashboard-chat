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
    const CHAT_WORKER_URL = "<?php echo Yii::$app->params['chat']['clientSocketName']?>";
    const USER_ID = "<?php echo Yii::$app->user->id; ?>";
</script>