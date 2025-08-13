<?php

namespace Modules\Invoices\Application\DTOs;

readonly class InvoiceDTO
{
    /**
     * @param InvoiceProductLineDTO[] $productLines
     */
    public function __construct(
        public string $id,
        public string $customerName,
        public string $customerEmail,
        public string $status,
        public array  $productLines,
        public int    $totalPrice,
    ) {}
}
