<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\TestCase;

class RoleModelTest extends TestCase
{
    public function test_fillable_columns_are_defined_via_attribute(): void
    {
        $this->assertSame(['name', 'description'], (new Role)->getFillable());
    }
}
