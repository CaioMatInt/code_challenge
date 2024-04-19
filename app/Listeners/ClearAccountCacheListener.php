<?php

namespace App\Listeners;

use App\Events\AccountTransactionCreatedEvent;
use Illuminate\Support\Facades\Cache;

class ClearAccountCacheListener
{
    public function __construct()
    { }

    public function handle(AccountTransactionCreatedEvent $event): void
    {
        Cache::forget('account_' . $event->account->custom_identifier);
    }
}
