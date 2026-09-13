<?php

namespace Modules\Employees\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Employees\Models\EmployeeDocument;

/**
 * @extends Factory<EmployeeDocument>
 */
class EmployeeDocumentFactory extends Factory
{
    protected $model = EmployeeDocument::class;

    public function definition(): array
    {
        $extension = fake()->randomElement([
            'pdf',
            'jpg',
            'png',
        ]);

        return [
            'employee_id' => null,

            'type' => fake()->randomElement([
                'contract',
                'certificate',
                'identification',
                'other',
            ]),

            'file_path' => 'employees/documents/'
                . fake()->uuid()
                . ".{$extension}",

            'original_name' => fake()->word()
                . '_document.'
                . $extension,

            'mime_type' => match ($extension) {
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
            },

            'file_size' => fake()->numberBetween(
                50_000,
                5_000_000
            ),
        ];
    }
}
