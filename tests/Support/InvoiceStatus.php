<?php

declare(strict_types=1);

namespace Svnjn\Standards\Tests\Support;

enum InvoiceStatus: string
{
    case Paid = 'paid';
    case Open = 'open';
}
