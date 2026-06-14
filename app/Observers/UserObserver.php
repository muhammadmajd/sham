<?php

namespace App\Observers;

use App\Models\User;
use App\Services\XrayProvisionService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $user->notify(new \App\Notifications\UserCreatedNotification);
        \App\Jobs\ProvisionUserOnXray::dispatch($user);
    }
}
