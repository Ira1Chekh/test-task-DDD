<?php

namespace Modules\Invoices\Domain\Exceptions;

use Modules\Invoices\Domain\ValueObjects\Status;

class InvoiceException extends \DomainException
{
    public static function invalidStatusTransition(Status $current, Status $target): self
    {
        return new self(
            sprintf('Cannot transition invoice from %s to %s', $current->value, $target->value)
        );
    }

    public static function cannotSendEmptyInvoice(): self
    {
        return new self('Cannot send an invoice without product lines');
    }
}
