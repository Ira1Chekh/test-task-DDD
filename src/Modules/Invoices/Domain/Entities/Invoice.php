<?php

namespace Modules\Invoices\Domain\Entities;

use Modules\Invoices\Domain\Exceptions\InvoiceException;
use Modules\Invoices\Domain\ValueObjects\Status;
use Modules\Invoices\Domain\ValueObjects\UuidId;

class Invoice
{
    /** @var InvoiceProductLine[] */
    private array $productLines = [];

    public function __construct(
        private UuidId $id,
        private string $customerName,
        private string $customerEmail,
        private Status $status,
        array $productLines = [],
    ) {
        $this->setCustomerEmail($customerEmail);
        foreach ($productLines as $productLine) {
            $this->addProductLine($productLine);
        }
    }

    public function getId(): UuidId { return $this->id; }
    public function getCustomerName(): string { return $this->customerName; }
    public function getCustomerEmail(): string { return $this->customerEmail; }
    public function getStatus(): Status { return $this->status; }
    public function setCustomerEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email");
        }
        $this->customerEmail = $email;
    }

    public function addProductLine(InvoiceProductLine $productLine): void
    {
        $this->productLines[] = $productLine;
    }

    /**
     * @return InvoiceProductLine[]
     */
    public function getProductLines(): array
    {
        return $this->productLines;
    }

    public function totalPrice(): int
    {
        return array_reduce(
            $this->productLines,
            fn(int $total, InvoiceProductLine $line) => $total + $line->getTotalPrice(),
            0
        );
    }

    public function markAsSending(): void
    {
        if ($this->status !== Status::DRAFT) {
            throw InvoiceException::invalidStatusTransition($this->status, Status::SENDING);
        }

        if (empty($this->productLines)) {
            throw InvoiceException::cannotSendEmptyInvoice();
        }

        $this->status = Status::SENDING;
    }

    public function markAsSentToClient(): void
    {
        if ($this->status !== Status::SENDING) {
            throw InvoiceException::invalidStatusTransition($this->status, Status::SENT_TO_CLIENT);
        }

        $this->status = Status::SENT_TO_CLIENT;
    }

    public function markAsDraft(): void
    {
        $this->status = Status::DRAFT;
    }
}
