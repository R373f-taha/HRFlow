<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Payroll\Database\Factories\PayslipDeductionFactory;

class PayslipDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payslip_id',
        'name',
        'reason',
        'amount',
    ];


    protected static function newFactory(): PayslipDeductionFactory
    {
        return PayslipDeductionFactory::new();
    }
    protected function casts(): array
    {
        return [
            'payslip_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    public function payslip(): BelongsTo
    {
        return $this->belongsTo(Payslip::class);
    }
}
