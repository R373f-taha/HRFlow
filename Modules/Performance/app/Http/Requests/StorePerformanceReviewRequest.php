<?php


namespace Modules\Performance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Employees\Models\Employee;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;

class StorePerformanceReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
       return true;
    }

    public function rules(): array
    {
        return [
            'performance_cycle_id' => ['required', 'exists:performance_cycles,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'overall_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'strengths' => ['required', 'string','min:8','max:255'],
            'areas_for_improvement' => ['required', 'string','min:8','max:255'],
            'goals' => ['required', 'string','min:8','max:255'],
        ];
    }
}
