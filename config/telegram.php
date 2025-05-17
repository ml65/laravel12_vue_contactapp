<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_ids' => explode(',', env('TELEGRAM_CHAT_IDS', '')),
    'enabled' => env('TELEGRAM_NOTIFICATIONS_ENABLED', false),
]; 