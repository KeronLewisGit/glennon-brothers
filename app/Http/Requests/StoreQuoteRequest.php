<?php

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\CustomerType;
use App\Enums\QuoteUnit;
use App\Enums\Treatment;
use App\Http\Requests\Concerns\HoldsUploads;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuoteRequest extends FormRequest
{
    use HoldsUploads;

    public static function heldForm(): string
    {
        return 'quote';
    }

    protected function heldFields(): array
    {
        return ['schedule' => false];
    }

    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->restoreHeldUploads();
        // Blank rows (the form shows a few spare ones) are dropped before validation.
        $lines = [];
        foreach ((array) $this->input('lines', []) as $line) {
            if (! is_array($line)) {
                continue;
            }
            $line = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $line);
            $filled = array_filter([$line['product'] ?? '', $line['quantity'] ?? '', $line['description'] ?? '', $line['thickness_mm'] ?? '', $line['width_mm'] ?? '']);
            if ($filled !== []) {
                $lines[] = $line;
            }
        }
        $this->merge([
            'lines' => $lines,
            'email' => strtolower(input_string($this->input('email'))),
            'postcode' => strtoupper(input_string($this->input('postcode'))) ?: null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_type' => ['required', Rule::enum(CustomerType::class)],
            'company' => ['required', 'string', 'max:160'],
            'account_number' => ['nullable', 'string', 'max:40'],
            'contact_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'country' => ['required', Rule::enum(Country::class)],
            'delivery_address' => ['required', 'string', 'max:300'],
            'postcode' => ['nullable', 'string', 'max:12'],
            'required_by' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:3000'],
            'schedule' => ['nullable', 'file', 'max:10240', 'mimes:'.implode(',', QuoteRequest::SCHEDULE_TYPES)],
            'lines' => ['required', 'array', 'min:1', 'max:'.QuoteRequest::MAX_LINES],
            'lines.*.product' => ['required', Rule::in(array_keys((array) gb('quote_products')))],
            'lines.*.description' => ['nullable', 'string', 'max:160'],
            'lines.*.thickness_mm' => ['nullable', 'integer', 'min:5', 'max:400'],
            'lines.*.width_mm' => ['nullable', 'integer', 'min:10', 'max:600'],
            'lines.*.length_m' => ['nullable', 'numeric', 'min:0.3', 'max:20'],
            'lines.*.treatment' => ['required', Rule::enum(Treatment::class)],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'max:99999'],
            'lines.*.unit' => ['required', Rule::enum(QuoteUnit::class)],
            'lines.*.delivery_site' => ['nullable', 'string', 'max:200'],
        ];
    }

    /** @return list<\Closure(Validator): void> */
    public function after(): array
    {
        return [function (Validator $v): void {
            // An Eircode for Republic of Ireland deliveries, a UK postcode for the rest.
            $postcode = input_string($this->input('postcode'));
            $country = Country::tryFrom(input_string($this->input('country')));
            if ($postcode === '' || ! $country || $v->errors()->has('postcode')) {
                return;
            }
            $ok = $country === Country::Ireland
                ? preg_match('/^[AC-FHKNPRTV-Y]\d{2}\s?[0-9AC-FHKNPRTV-Y]{4}$/', $postcode)
                : preg_match('/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/', $postcode);
            if (! $ok) {
                $v->errors()->add('postcode', $country === Country::Ireland ? 'That does not look like an Eircode, for example N39 AX73.' : 'That does not look like a UK postcode, for example KA10 6DN.');
            }
        }];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'customer_type' => 'type of business',
            'contact_name' => 'your name',
            'delivery_address' => 'delivery address',
            'required_by' => 'date needed',
            'lines.*.product' => 'product',
            'lines.*.thickness_mm' => 'thickness',
            'lines.*.width_mm' => 'width',
            'lines.*.length_m' => 'length',
            'lines.*.treatment' => 'treatment',
            'lines.*.quantity' => 'quantity',
            'lines.*.unit' => 'unit',
            'lines.*.delivery_site' => 'delivery site',
            'schedule' => 'cutting list',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number with its area code, for example 043 335 0800 or 01292 800600.',
            'lines.required' => 'Add at least one product line: what you need and how much.',
            'lines.min' => 'Add at least one product line: what you need and how much.',
            'lines.max' => 'Up to '.QuoteRequest::MAX_LINES.' lines per request. Attach a cutting list for more.',
            'lines.*.quantity.gt' => 'The quantity must be more than zero.',
            'required_by.after_or_equal' => 'The date needed cannot be in the past.',
            'schedule.max' => 'The cutting list must be 10 MB or smaller.',
            'schedule.mimes' => 'The cutting list can be a PDF, Excel, CSV or Word file.',
            'schedule.uploaded' => 'The cutting list could not be uploaded. It must be 10 MB or smaller.',
            'customer_type.required' => 'Choose the type of business.',
            'country.required' => 'Choose where the timber is going.',
        ];
    }
}
