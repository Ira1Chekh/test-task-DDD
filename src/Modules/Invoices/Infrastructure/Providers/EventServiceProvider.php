<?php

namespace Modules\Invoices\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Invoices\Application\Services\InvoiceService;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            ResourceDeliveredEvent::class,
            [InvoiceService::class, 'handleResourceDelivered']
        );
    }
}
