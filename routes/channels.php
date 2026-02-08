<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('exam-monitor', function ($user) {
    return $user->role === 'admin' || $user->role === 'teacher';
});

Broadcast::channel('security-monitoring', function ($user) {
    return $user->role === 'admin' || $user->role === 'teacher';
});
