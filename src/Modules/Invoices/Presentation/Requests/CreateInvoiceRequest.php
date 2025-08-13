<?php

namespace Modules\Invoices\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Invoices\Application\DTOs\CreateInvoiceDTO;
use Modules\Invoices\Infrastructure\Factories\InvoiceProductLineDTOFactory;

class CreateInvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'product_lines' => 'array',
            'product_lines.*.name' => 'required|string',
            'product_lines.*.quantity' => 'required|integer|min:1',
            'product_lines.*.unit_price' => 'required|integer|min:1',
        ];
    }

    public function toDTO(): CreateInvoiceDTO
    {
        return new CreateInvoiceDTO(
            customerName: $this->input('customer_name'),
            customerEmail: $this->input('customer_email'),
            productLines: array_map(
                [InvoiceProductLineDTOFactory::class, 'fromArray'],
                $this->input('product_lines')
            )
        );
    }

}
