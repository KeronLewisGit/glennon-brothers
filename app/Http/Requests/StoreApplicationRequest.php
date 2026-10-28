<?php

namespace App\Http\Requests;

use App\Enums\Experience;
use App\Enums\JobCategory;
use App\Enums\Plant;
use App\Http\Requests\Concerns\HoldsUploads;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    use HoldsUploads;

    public static function heldForm(): string
    {
        return 'application';
    }

    protected function heldFields(): array
    {
        return ['cv' => false];
    }

    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->restoreHeldUploads();
        // When a job is picked, its category is the truth (so "professional roles need a CV" follows the job).
        $jobId = input_string($this->input('job_posting_id'));
        $job = ctype_digit($jobId) ? JobPosting::open()->find((int) $jobId) : null;
        if ($job) {
            $this->merge(['category' => $job->category->value, 'plant' => $job->plant->value]);
        }
        if (input_string($this->input('plant')) === 'any') {
            $this->merge(['plant' => null]);
        }
        $this->merge(['email' => $this->filled('email') ? strtolower(input_string($this->input('email'))) : null]);
    }

    public function category(): ?JobCategory
    {
        return JobCategory::tryFrom(input_string($this->input('category')));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $needsCv = $this->category()?->needsCv() ?? false;

        return [
            'job_posting_id' => ['nullable', 'integer', Rule::exists('job_postings', 'id')->where('status', 'open')
                ->where(fn ($q) => $q->whereNull('closes_on')->orWhereDate('closes_on', '>=', today()))],
            'category' => ['required', Rule::enum(JobCategory::class)],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^(?:\D*\d){7,15}\D*$/'],
            'email' => [$needsCv ? 'required' : 'nullable', 'email:rfc', 'max:255'],
            'plant' => ['nullable', Rule::enum(Plant::class)],
            'home_area' => ['nullable', 'string', 'max:80'],
            'right_to_work' => ['required', 'boolean'],
            'licences' => [$this->category()?->asksLicence() ? 'required' : 'nullable', 'string', 'max:160'],
            'experience' => ['required', Rule::enum(Experience::class)],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'available_from' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'message' => ['nullable', 'string', 'max:2000'],
            'cv' => [$needsCv ? 'required' : 'nullable', 'file', 'max:5120', 'mimes:'.implode(',', JobApplication::CV_TYPES)],
            'consent' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['first_name' => 'first name', 'last_name' => 'last name', 'available_from' => 'start date', 'cv' => 'CV', 'category' => 'type of work', 'home_area' => 'where you live', 'licences' => 'licences'];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Enter a phone number with its area code, for example 086 123 4567 or 07700 900123.',
            'job_posting_id.exists' => 'That opening has closed. Choose another opening, or send a general application.',
            'category.required' => 'Choose the type of work you are applying for.',
            'experience.required' => 'Choose how much experience you have.',
            'cv.required' => 'Maintenance, electrical and office roles need a CV (PDF or Word).',
            'email.required' => 'Maintenance, electrical and office roles need an email address.',
            'licences.required' => 'Tell us which licences you hold, for example C+E, CPC and digital tachograph card.',
            'right_to_work.required' => 'Tell us whether you have the right to work in Ireland or the UK.',
            'cv.max' => 'Your CV must be 5 MB or smaller.',
            'cv.mimes' => 'Your CV must be a PDF or Word document.',
            'cv.uploaded' => 'Your CV could not be uploaded. It must be 5 MB or smaller.',
            'consent.accepted' => 'Please agree to us keeping your application on file.',
        ];
    }
}
