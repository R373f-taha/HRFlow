<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Organization\Models\Department;
use Tests\TestCase;

uses(TestCase::class);

test('it defines a belongsTo manager relationship', function () {
    $department = new Department();

    $relation = $department->manager();

    expect($relation)->toBeInstanceOf(BelongsTo::class)
        ->and($relation->getForeignKeyName())->toBe('manager_id');
});

test('it defines a hasMany children relationship', function () {
    $department = new Department();

    $relation = $department->children();

    expect($relation)->toBeInstanceOf(HasMany::class)
        ->and($relation->getForeignKeyName())->toBe('parent_id');
});

test('it formats the department display code correctly', function () {
    $department = new Department([
        'name' => 'Human Resources',
        'code' => 'hr-01',
    ]);

    expect(strtoupper($department->code))->toBe('HR-01');
});
