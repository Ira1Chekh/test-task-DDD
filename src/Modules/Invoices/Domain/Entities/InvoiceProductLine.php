<?php

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\ValueObjects\UuidId;

final class InvoiceProductLine
{
    public function __construct(
        private UuidId $id,
        private string $name,
        private int $quantity,
        private int $unitPrice,
    ) {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive');
        }
        if ($unitPrice <= 0) {
            throw new \InvalidArgumentException('Unit price must be positive');
        }
    }

    public function getId(): UuidId { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getQuantity(): int { return $this->quantity; }
    public function getUnitPrice(): int { return $this->unitPrice; }

    public function getTotalPrice(): int
    {
        return $this->quantity * $this->unitPrice;
    }
}
