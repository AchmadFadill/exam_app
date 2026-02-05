<?php

namespace App\Livewire\Common;

use Livewire\Component;

class NavbarNotifications extends Component
{
    public function getNotificationsProperty()
    {
        return auth()->check() ? auth()->user()->unreadNotifications : collect();
    }

    public function markAsRead($notificationId)
    {
        if (auth()->check()) {
            $notification = auth()->user()->notifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
            }
        }
    }

    public function markAllAsRead()
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
        }
    }

    public function render()
    {
        return view('livewire.common.navbar-notifications');
    }
}
