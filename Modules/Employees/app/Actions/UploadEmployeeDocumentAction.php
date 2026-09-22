<?php

namespace Modules\Employees\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Employees\Models\Employee;
use Modules\Employees\Models\EmployeeDocument;

class UploadEmployeeDocumentAction
{
    public function execute(Employee $employee, array $data, UploadedFile $file): EmployeeDocument
    {
        return DB::transaction(function () use ($employee, $data, $file) {
            // 1. Generate secure obfuscated random filename & store on private disk
            $extension = $file->getClientOriginalExtension();
            $safeFileName = Str::uuid()->toString() . '.' . $extension;
            $directory = "documents/{$employee->id}";

            // Store in private disk (storage/app/private/documents/{employee_id})
            $path = $file->storeAs($directory, $safeFileName, 'private');

            // 2. Persist metadata into database
            $document = $employee->documents()->create([
                'type' => $data['type'],
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            // 3. Clear employee document and detail cache
            CacheSupport::forget("employees.documents.{$employee->id}");
            CacheSupport::forget("employees.detail.{$employee->id}");

            return $document;
        });
    }
}
