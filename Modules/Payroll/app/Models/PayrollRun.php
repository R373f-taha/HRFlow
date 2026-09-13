<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Payroll\Database\Factories\PayrollRunFactory;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'status',
        'processed_at',
        'finalized_at',
    ];


    protected static function newFactory(): PayrollRunFactory
    {
        return PayrollRunFactory::new();
    }

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'month' => 'integer',
            'processed_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }
}
