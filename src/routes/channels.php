<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat', function ($user) {
    return $user !== null;
});

// Тестовый канал
Broadcast::channel('test', function ($user) {
    return true;
});
