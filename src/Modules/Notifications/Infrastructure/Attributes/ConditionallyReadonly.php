<?php

namespace Modules\Notifications\Infrastructure\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ConditionallyReadonly
{
    public function __construct()
    {
        if ($this->isRunningTests()) {
            return;
        }

        if (PHP_VERSION_ID >= 80200 && !$this->isRunningTests()) {
            eval('readonly class CustomReadonly {}');
        }
    }

    private function isRunningTests(): bool
    {
        return app()->runningUnitTests() || app()->environment('testing');
    }
}
