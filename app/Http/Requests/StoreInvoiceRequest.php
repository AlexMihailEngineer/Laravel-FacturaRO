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
            'series' => ['required', 'string', 'max:10'],
            'number' => ['required', 'string', 'max:20'],
            'issue_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after:2020-01-01'],
            'supplier.name' => ['required', 'string'],
            'supplier.cui' => ['required', new RomanianCuiRule()],
            'customer.name' => ['required', 'string'],
            'customer.cui' => ['required', new RomanianCuiRule()],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gt:0'],
            'items.*.vat_rate' => ['required', 'in:19,9,5'],
            'total_amount' => ['required', 'numeric'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($validator->errors()->has('items')) {
                return;
            }

            $totalCalculated = '0.00';

            foreach ($this->input('items', []) as $item) {
                $lineNet = bcmul($item['quantity'], $item['unit_price'], 4);
                $vatMultiplier = bcdiv($item['vat_rate'], '100', 4);
                $lineVat = bcmul($lineNet, $vatMultiplier, 4);
                $lineGross = bcadd($lineNet, $lineVat, 4);

                $totalCalculated = bcadd($totalCalculated, $lineGross, 4);
            }

            // Compare with 2 decimal precision for the final total
            if (bccomp(bcadd($this->input('total_amount'), '0', 2), bcadd($totalCalculated, '0', 2), 2) !== 0) {
                $validator->errors()->add('total_amount', "The total amount ({$this->input('total_amount')}) does not match the sum of items (" . bcadd($totalCalculated, '0', 2) . ").");
            }
        });
    }
}
