<?php

namespace Modules\Invoices\Infrastructure\DataMappers;

use Modules\Invoices\Application\DTOs\InvoiceDTO;
use Modules\Invoices\Application\DTOs\InvoiceProductLineDTO;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;

class InvoiceDataMapper
{
    public function mapToDTO(Invoice $invoice): InvoiceDTO
    {
        return new InvoiceDTO(
            id: $invoice->getId()->getValue(),
            customerName: $invoice->getCustomerName(),
            customerEmail: $invoice->getCustomerEmail(),
            status: $invoice->getStatus()->value,
            productLines: array_map(
                fn(InvoiceProductLine $line) => new InvoiceProductLineDTO(
                    name: $line->getName(),
                    quantity: $line->getQuantity(),
                    unitPrice: $line->getUnitPrice(),
                    totalUnitPrice: $line->getTotalPrice(),
                ),
                $invoice->getProductLines()
            ),
            totalPrice: $invoice->totalPrice(),
        );
    }
}
