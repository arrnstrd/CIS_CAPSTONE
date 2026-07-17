<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_supported_roles_are_defined_as_constants(): void
    {
        $this->assertSame('admin', User::ROLE_ADMIN);
        $this->assertSame('teacher', User::ROLE_TEACHER);
        $this->assertSame('scanner_operator', User::ROLE_SCANNER_OPERATOR);
    }

    public function test_role_helper_methods_return_the_expected_result(): void
    {
        $admin = new User(['role' => User::ROLE_ADMIN]);
        $teacher = new User(['role' => User::ROLE_TEACHER]);
        $scannerOperator = new User(['role' => User::ROLE_SCANNER_OPERATOR]);

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($teacher->isTeacher());
        $this->assertTrue($scannerOperator->isScannerOperator());

        $this->assertFalse($teacher->isAdmin());
        $this->assertFalse($scannerOperator->isTeacher());
    }
}
