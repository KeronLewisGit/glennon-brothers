<?php

namespace App\Http\Requests;

use App\Enums\FrameClientType;
use App\Enums\FrameProjectType;
use App\Enums\PlanningStatus;
use App\Http\Requests\Concerns\HoldsUploads;
use App\Models\FrameEnquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFrameEnquiryRequest extends FormRequest
{
    use HoldsUploads;

    public static function heldForm(): string
    {
        return 'frame';
    }

    protected function heldFields(): array
    {
        return ['drawings' => true];
    }

    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->restoreHeldUploads();
        $eircode = strtoupper((string) preg_replace('/\s+/', '', input_string($this->input('eircode'))));
        $this->merge([
            'email' => strtolower(input_string($this->input('email'))),
            'eircode' => $eircode !== '' ? substr($eircode, 0, 3).' '.substr($eircode, 3) : null,
        ]);
    }

    /** @return list<string> */
    public static function counties(): array
    {
        return array_merge(...array_values((array) gb('counties')));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $scheme = FrameProjectType::tryFrom(input_string($this->input('project_type')))?->hasUnits() ?? false;

        return [
            'client_type' => ['required', Rule::enum(FrameClientType::class)],
            'project_type' => ['required', Rule::enum(FrameProjectType::class)],
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'site_address' => ['required', 'string', 'max:300'],
            'county' => ['required', Rule::in(self::counties())],
            'eircode' => ['nullable', 'regex:/^[AC-FHKNPRTV-Y]\d{2} [0-9AC-FHKNPRTV-Y]{4}$/'],
            'planning_status' => ['required', Rule::enum(PlanningStatus::class)],
            'start_month' => ['required', 'date_format:Y-m', 'after_or_equal:'.now()->format('Y-m'), 'before_or_equal:'.now()->addYears(3)->format('Y-m')],
            'floor_area_m2' => ['nullable', 'integer', 'min:10', 'max:20000'],
            'units' => [$scheme ? 'required' : 'nullable', 'integer', 'min:2', 'max:500'],
            'message' => ['nullable', 'string', 'max:3000'],
            'drawings' => ['nullable', 'array', 'max:'.FrameEnquiry::MAX_DRAWINGS],
            'drawings.*' => ['file', 'max:'.FrameEnquiry::MAX_DRAWING_KB, 'mimes:'.implode(',', FrameEnquiry::DRAWING_TYPES)],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'client_type' => 'who you are',
            'project_type' => 'type of project',
            'site_address' => 'site address',
            'planning_status' => 'planning permission',
            'start_month' => 'start month',
            'floor_area_m2' => 'floor area',
            'units' => 'number of homes',
            'drawings.*' => 'drawing',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number with its area code, for example 086 123 4567.',
            'eircode.regex' => 'That does not look like an Eircode, for example N39 AX73. Leave it blank if the site has none yet.',
            'start_month.after_or_equal' => 'Choose this month or a later one.',
            'start_month.before_or_equal' => 'Choose a month within the next three years.',
            'units.required' => 'Tell us how many homes are in the scheme.',
            'drawings.max' => 'Attach up to '.FrameEnquiry::MAX_DRAWINGS.' drawings. You can send more with the link in your confirmation email.',
            'drawings.*.max' => 'Each drawing must be 20 MB or smaller.',
            'drawings.*.mimes' => 'Drawings can be PDF, DWG, DXF, JPG, PNG or ZIP files.',
            'drawings.*.uploaded' => 'A drawing could not be uploaded. Each must be 20 MB or smaller.',
        ];
    }
}
