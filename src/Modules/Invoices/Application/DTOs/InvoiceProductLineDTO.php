<?php

namespace Modules\Invoices\Application\DTOs;

readonly class InvoiceProductLineDTO
{
    public function __construct(
        public string $name,
        public int    $quantity,
        public int    $unitPrice,
        public int    $totalUnitPrice
    ) {}
}
