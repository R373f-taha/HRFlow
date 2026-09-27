<?php

namespace Modules\Leave\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use Modules\Leave\Models\LeaveRequest;

/**
 * Custom validation rule to ensure an employee cannot submit overlapping leave requests.
 *
 * Prevents double-booking by checking existing 'pending' or 'approved' leave requests
 * against the proposed start and end dates.
 */
class NoOverLappingLeave implements ValidationRule
{
    /**
     * @param  int  $employeeId  The ID of the employee requesting leave.
     * @param  string  $startDate  Proposed leave start date (Y-m-d).
     * @param  string  $endDate  Proposed leave end date (Y-m-d).
     * @param  int|null  $ignoreRequestId  Optional leave request ID to exclude (useful when updating an existing request).
     */
    public function __construct(
        private int $employeeId,
        private string $startDate,
        private string $endDate,
        private ?int $ignoreRequestId = null
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute  Form input field name being validated.
     * @param  mixed  $value  Field value.
     * @param  Closure(string): PotentiallyTranslatedString  $fail  Failure callback.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Guard against execution if dates are missing or incomplete in request payload
        if (! $this->startDate || ! $this->endDate) {
            return;
        }

        // Query the database for active leave requests belonging to this employee that intersect the proposed dates
        $exists = LeaveRequest::query()
            // 1. Scope query strictly to the current employee
            ->where('employee_id', $this->employeeId)

            // 2. Only conflict with active requests (ignore already rejected or cancelled requests)
            ->whereIn('status', ['pending', 'approved'])

            // 3. Exclude the current request ID if updating an existing record
            ->when($this->ignoreRequestId, fn ($q) => $q->where('id', '!=', $this->ignoreRequestId))

            // 4. Check for range overlap:
            // Two date ranges (A..B) and (C..D) overlap if:
            // Case A: Existing start date falls within the new range
            // Case B: Existing end date falls within the new range
            // Case C: New range is completely enclosed inside an existing range
            ->where(function ($query) {
                $query->whereBetween('start_date', [$this->startDate, $this->endDate])
                    ->orWhereBetween('end_date', [$this->startDate, $this->endDate])
                    ->orWhere(function ($q) {
                        $q->where('start_date', '<=', $this->startDate)
                            ->where('end_date', '>=', $this->endDate);
                    });
            })
            // Execute optimized SQL query using composite index (employee_id, start_date, end_date)
            ->exists();

        // If an overlapping request exists, trigger validation failure
        if ($exists) {
            $fail('There is already an existing leave request that overlaps with the selected dates. Please choose different dates.');
        }
    }
}
