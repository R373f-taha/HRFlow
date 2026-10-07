<?php

namespace Modules\Payroll\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = (int) date('Y');

        return [
            'year' => [
                'required',
                'integer',
                'min:' . ($currentYear - 1), // Allows processing starting from last year
                'max:' . ($currentYear + 1), // Restricts run creation beyond next year
            ],
            'month' => [
                'required',
                'integer',
                'between:1,12',
                // Ensures that the combination of year and month is unique for payroll runs
                Rule::unique('payroll_runs')->where(fn ($q) => $q->where('year', $this->year)->where('month', $this->month)),
                // Custom validation to prevent processing payroll runs too far in the past
                //it checks if the year and month are set, then constructs a date string for the first day of that month and year.
                //It also calculates a minimum date that is one year in the past from the current date.
                //If the constructed run date is earlier than this minimum date, it fails the validation with a specific message.
                function ($attribute, $value, $fail) {
                    if ($this->year && $this->month) {
                        $runDate = sprintf('%04d-%02d-01', $this->year, $this->month);
                        $minDate = date('Y-m-01', strtotime('-1 year')); // Limit past runs to 1 year back

                        if (strtotime($runDate) < strtotime($minDate)) {
                            $fail('The selected payroll run period is too far in the past.');
                        }
                    }
                },
            ],
        ];
    }
}
