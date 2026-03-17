<?php

namespace App\Domain\Invoice\Exceptions;

use Exception;

class InvoiceException extends Exception
{
    public static function emptyInvoiceLines(): self
    {
        return new self("Invoice must have at least one line.");
    }

    public static function missingRenderer(): self
    {
        return new self("No renderer provided for the invoice generation.");
    }

    public static function invalidData(string $message): self
    {
        return new self("Invalid invoice data: {$message}");
    }
}
