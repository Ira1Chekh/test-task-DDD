<?php

namespace Modules\Invoices\Application\DTOs;

final readonly class CreateInvoiceDTO
{
    /**
     * @param InvoiceProductLineDTO[] $productLines
     */
    public function __construct(
        public string $customerName,
        public string $customerEmail,
        public array $productLines,
    ) {}
}
