<?php

declare(strict_types=1);

namespace Tests\Feature\Notification\Http;

use Illuminate\Foundation\Testing\WithFaker;
use Modules\Notifications\Application\Services\NotificationService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        $this->setUpFaker();

        parent::setUp();
    }

    #[DataProvider('hookActionProvider')]
    public function testHook(string $action): void
    {
        $this->mock(NotificationService::class)
            ->shouldReceive('delivered')
            ->andReturn(true);

        $uri = route('notification.hook', [
            'action' => $action,
            'reference' => $this->faker->uuid,
        ]);

        $this->getJson($uri)->assertOk();
    }

    public function testInvalid(): void
    {
        $params = [
            'action' => 'dummy',
            'reference' => $this->faker->numberBetween(),
        ];

        $uri = route('notification.hook', $params);
        $this->getJson($uri)->assertNotFound();
    }

    public static function hookActionProvider(): array
    {
        return [
            ['delivered'],
            ['dummy'],
        ];
    }
}
