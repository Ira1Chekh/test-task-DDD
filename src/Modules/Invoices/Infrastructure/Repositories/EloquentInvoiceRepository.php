<?php

namespace Modules\Invoices\Infrastructure\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\UuidId;
use Modules\Invoices\Infrastructure\Models\Invoice as InvoiceModel;
use Modules\Invoices\Infrastructure\Models\InvoiceProductLine as InvoiceProductLineModel;
use Modules\Invoices\Domain\ValueObjects\Status;

class EloquentInvoiceRepository implements InvoiceRepositoryInterface
{
    public function findById(string $id): ?Invoice
    {
        $invoiceModel = InvoiceModel::with('productLines')->find($id);

        if (!$invoiceModel) {
            return null;
        }

        $invoiceId = new UuidId($invoiceModel->id);

        return new Invoice(
            id: $invoiceId,
            customerName: $invoiceModel->customer_name,
            customerEmail: $invoiceModel->customer_email,
            status: Status::tryFrom($invoiceModel->status) ?? throw new \RuntimeException('Invalid status'),
            productLines: $invoiceModel->productLines->map(
                function(InvoiceProductLineModel $line) {
                    $lineId = new UuidId($line->id);

                    return new InvoiceProductLine(
                        id: $lineId,
                        name: $line->name,
                        quantity: $line->quantity,
                        unitPrice: $line->price,
                    );
                }
            )->toArray(),
        );
    }

    public function save(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoiceModel = InvoiceModel::create([
                'id' => $invoice->getId()->getValue(),
                'customer_name' => $invoice->getCustomerName(),
                'customer_email' => $invoice->getCustomerEmail(),
                'status' => $invoice->getStatus()->value,
            ]);

            if (!empty($invoice->getProductLines())) {
                $linesData = array_map(
                    fn(InvoiceProductLine $line) => [
                        'id' => $line->getId()->getValue(),
                        'name' => $line->getName(),
                        'quantity' => $line->getQuantity(),
                        'price' => $line->getUnitPrice(),
                    ],
                    $invoice->getProductLines()
                );

                $invoiceModel->productLines()->createMany($linesData);
            }
        });
    }

    public function updateStatus(Invoice $invoice): void
    {
        InvoiceModel::where('id', $invoice->getId()->getValue())
            ->update(['status' => $invoice->getStatus()->value]);
    }
}
