<?php

namespace Modules\Invoices\Application\Services;

use Modules\Invoices\Application\DTOs\CreateInvoiceDTO;
use Modules\Invoices\Application\DTOs\InvoiceDTO;
use Modules\Invoices\Application\DTOs\InvoiceProductLineDTO;
use Modules\Invoices\Domain\Entities\Invoice;
use Modules\Invoices\Domain\Entities\InvoiceProductLine;
use Modules\Invoices\Domain\Repositories\InvoiceRepositoryInterface;
use Modules\Invoices\Domain\ValueObjects\Status;
use Modules\Invoices\Domain\ValueObjects\UuidId;
use Modules\Invoices\Infrastructure\DataMappers\InvoiceDataMapper;
use Modules\Notifications\Api\Dtos\NotifyData;
use Modules\Notifications\Api\Events\ResourceDeliveredEvent;
use Modules\Notifications\Api\NotificationFacadeInterface;
use Ramsey\Uuid\Uuid;

class InvoiceService
{
    public function __construct(
        private InvoiceRepositoryInterface  $invoiceRepository,
        private InvoiceDataMapper           $mapper,
        private NotificationFacadeInterface $notificationFacade,
    ) {}


    public function createInvoice(CreateInvoiceDTO $invoiceDTO): InvoiceDTO {
        $uuidId = new UuidId(Uuid::uuid4()->toString());
        $invoice = new Invoice(
            id: $uuidId,
            customerName: $invoiceDTO->customerName,
            customerEmail: $invoiceDTO->customerEmail,
            status: Status::DRAFT,
            productLines: array_map(
                function (InvoiceProductLineDTO $dto) {
                    $uuidId = new UuidId(Uuid::uuid4()->toString());
                    return new InvoiceProductLine(
                        id: $uuidId,
                        name: $dto->name,
                        quantity: $dto->quantity,
                        unitPrice: $dto->unitPrice,
                    );
                },
                $invoiceDTO->productLines
            ),
        );

        $this->invoiceRepository->save($invoice);

        return $this->mapper->mapToDTO($invoice);
    }

    public function getInvoice(string $id): ?InvoiceDTO
    {
        $invoice = $this->invoiceRepository->findById($id);

        return $invoice ? $this->mapper->mapToDTO($invoice) : null;
    }

    public function sendInvoice(string $id): InvoiceDTO
    {
        $invoice = $this->invoiceRepository->findById($id);

        if (!$invoice) {
            throw new \InvalidArgumentException('Invoice not found');
        }

        $invoice->markAsSending();
        $this->invoiceRepository->updateStatus($invoice);

        try {
            $this->sendNotification($invoice);

            return $this->mapper->mapToDTO($invoice);
        } catch (\Exception $e) {
            $invoice->markAsDraft();
            $this->invoiceRepository->updateStatus($invoice);

            throw $e;
        }
    }

    private function sendNotification(Invoice $invoice): void
    {
        $this->notificationFacade->notify(new NotifyData(
            resourceId: Uuid::fromString($invoice->getId()->getValue()),
            toEmail: $invoice->getCustomerEmail(),
            subject: 'Your Invoice #' . substr($invoice->getId()->getValue(), 0, 8),
            message: $this->generateInvoiceEmail($invoice),
        ));
    }

    private function generateInvoiceEmail(Invoice $invoice): string
    {
        $lines = array_map(
            fn($line) => sprintf(
                "%s: %d x %d = %d",
                $line->getName(),
                $line->getQuantity(),
                $line->getUnitPrice(),
                $line->getTotalPrice()
            ),
            $invoice->getProductLines()
        );

        return sprintf(
            "Dear %s,\n\nYour invoice details:\n\n%s\n\nTotal: %d\n\nThank you!",
            $invoice->getCustomerEmail(),
            implode("\n", $lines),
            $invoice->totalPrice()
        );
    }

    public function handleResourceDelivered(ResourceDeliveredEvent $event): void
    {
        $invoice = $this->invoiceRepository->findById($event->resourceId->toString());

        if (!$invoice) {
            throw new \InvalidArgumentException('Invoice not found');
        }

        $invoice->markAsSentToClient();
        $this->invoiceRepository->updateStatus($invoice);
    }
}
