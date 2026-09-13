<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Database\Factories\EmployeeDocumentFactory;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'file_size' => 'integer',
        ];
    }

        protected static function newFactory(): EmployeeDocumentFactory
    {
        return EmployeeDocumentFactory::new();
    }


    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
