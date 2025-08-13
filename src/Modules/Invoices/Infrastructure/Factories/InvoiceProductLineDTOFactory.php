<?php

namespace Modules\Invoices\Infrastructure\Factories;

use Modules\Invoices\Application\DTOs\InvoiceProductLineDTO;

final class InvoiceProductLineDTOFactory
{
    /**
     * @param array{name: string, quantity: int, unit_price: int} $data
     */
    public static function fromArray(array $data): InvoiceProductLineDTO
    {
        return new InvoiceProductLineDTO(
            name: $data['name'],
            quantity: $data['quantity'],
            unitPrice: $data['unit_price'],
            totalUnitPrice: $data['quantity'] * $data['unit_price'],
        );
    }
}
