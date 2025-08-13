<?php

declare(strict_types=1);

namespace Modules\Notifications\Infrastructure\Drivers;

use Illuminate\Support\Facades\Http;

class DummyDriver implements DriverInterface
{
    public function __construct(
        private Http $client,
        private string $webhookUrl
    ) {}

    public function send(
        string $toEmail,
        string $subject,
        string $message,
        string $reference,
    ): bool {
        sleep(2);

        $url = $this->webhookUrl . '/delivered/' . $reference;
        $this->client::get($url);

        return true;
    }
}
