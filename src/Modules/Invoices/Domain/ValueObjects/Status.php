<?php

namespace Modules\Invoices\Domain\ValueObjects;

enum Status: string
{
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case SENT_TO_CLIENT = 'sent-to-client';
}
