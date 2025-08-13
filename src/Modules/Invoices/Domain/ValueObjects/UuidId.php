<?php

namespace Modules\Invoices\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;

class UuidId
{
    public function __construct(
        private string $value
    ) {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("Invalid UUID");
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

}
