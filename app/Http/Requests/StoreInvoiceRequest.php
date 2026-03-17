<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Rules\RomanianCuiRule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Metadata
            'series' => ['required', 'string', 'max:10'],
            'number' => ['required', 'string', 'max:20'],
            'issue_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after:2020-01-01'],

            // Supplier (Mandatory for fiscal compliance)
            'supplier.name' => ['required', 'string', 'max:255'],
            'supplier.cui' => ['required', new RomanianCuiRule()],
            'supplier.address' => ['required', 'string', 'max:500'],
            'supplier.registration_number' => ['required', 'string', 'max:50'], // e.g., J40/123/2024

            // Customer
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.cui' => ['required', new RomanianCuiRule()],
            'customer.address' => ['required', 'string', 'max:500'],
            'customer.registration_number' => ['nullable', 'string', 'max:50'], // Nullable for PF (individuals)

            // Line Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gt:0'],
            'items.*.vat_rate' => ['required', 'in:19,9,5'],

            // Final sanity check for the payload
            'total_amount' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['items', 'total_amount'])) {
                return;
            }

            $sumNet = '0.0000';
            $sumVat = '0.0000';

            foreach ($this->input('items', []) as $item) {
                // Line Net = Qty * UnitPrice
                $lineNet = bcmul($item['quantity'], $item['unit_price'], 4);

                // Line VAT = Line Net * (Rate / 100)
                $vatMultiplier = bcdiv($item['vat_rate'], '100', 4);
                $lineVat = bcmul($lineNet, $vatMultiplier, 4);

                $sumNet = bcadd($sumNet, $lineNet, 4);
                $sumVat = bcadd($sumVat, $lineVat, 4);
            }

            $totalCalculated = bcadd($sumNet, $sumVat, 2); // Final total rounded to 2 decimals
            $totalProvided = bcadd($this->input('total_amount'), '0', 2);

            if (bccomp($totalProvided, $totalCalculated, 2) !== 0) {
                $validator->errors()->add(
                    'total_amount',
                    "Inconsistency detected: Total provided is $totalProvided, but calculated sum is $totalCalculated."
                );
            }
        });
    }
}
