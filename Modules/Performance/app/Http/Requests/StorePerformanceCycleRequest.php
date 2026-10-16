<?php

namespace Modules\Performance\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerformanceCycleRequest  extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'starts_at' => ['required', 'date', 'after:now'],
        'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
    ];
}

}
