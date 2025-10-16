<?php

namespace App\Services\Placeholders;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Example context class for invoice generation
 */
class InvoiceContext
{
    public static function build($invoice): PlaceholderHandler
    {
        $handler = new PlaceholderHandler;

        return $handler
            ->add('invoice_no', $invoice->invoice_number)
            ->addFormatted('total', $invoice->total, 'currency', 'MYR')
            ->addFormatted('subtotal', $invoice->subtotal, 'currency', 'MYR')
            ->addDate('invoice_date', $invoice->created_at, 'd/m/Y')
            ->addDate('due_date', $invoice->due_date, 'd/m/Y')
            ->useContext('customer', $invoice->customer, 'customer')
            ->addLazy('items_list', function () use ($invoice) {
                return $invoice->items
                    ->map(fn ($i) => $i->description.' - RM'.number_format($i->amount, 2))
                    ->join("\n");
            });
    }
}
