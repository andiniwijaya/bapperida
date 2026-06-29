<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\User;
use App\Policies\IncomingLetterPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesIncomingLetters;
use Tests\TestCase;

/**
 * Unit tests for IncomingLetterPolicy role-based authorization rules.
 */
class IncomingLetterPolicyTest extends TestCase
{
    use CreatesIncomingLetters;
    use RefreshDatabase;

    public function test_view_any_and_create_are_allowed_for_superadmin_admin_and_staff(): void
    {
        $policy = new IncomingLetterPolicy();

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);

        foreach ([$superadmin, $admin, $staff] as $user) {
            $this->assertTrue($policy->viewAny($user));
            $this->assertTrue($policy->create($user));
        }
    }

    public function test_staff_can_update_and_delete_only_own_incoming_letters(): void
    {
        $policy = new IncomingLetterPolicy();
        $department = Department::create(['code' => 'DPT', 'name' => 'Departemen Test', 'is_active' => true]);

        $staff = User::factory()->create(['role' => 'staff']);
        $otherStaff = User::factory()->create(['role' => 'staff']);

        $ownLetter = $this->createIncomingLetter($staff, $department, [
            'letter_number' => 'OWN/001',
        ]);

        $otherLetter = $this->createIncomingLetter($otherStaff, $department, [
            'letter_number' => 'OTHER/001',
        ]);

        $this->assertTrue($policy->update($staff, $ownLetter));
        $this->assertFalse($policy->delete($staff, $ownLetter));

        $this->assertFalse($policy->update($staff, $otherLetter));
        $this->assertFalse($policy->delete($staff, $otherLetter));
    }

    public function test_admin_can_manage_staff_created_letters_but_not_other_admin_letters(): void
    {
        $policy = new IncomingLetterPolicy();
        $department = Department::create(['code' => 'DPT', 'name' => 'Departemen Test', 'is_active' => true]);

        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $staffLetter = $this->createIncomingLetter($staff, $department, [
            'letter_number' => 'STAFF/001',
        ]);

        $adminLetter = $this->createIncomingLetter($otherAdmin, $department, [
            'letter_number' => 'ADMIN/001',
        ]);

        $this->assertTrue($policy->update($admin, $staffLetter));
        $this->assertTrue($policy->delete($admin, $staffLetter));

        $this->assertFalse($policy->update($admin, $adminLetter));
        $this->assertFalse($policy->delete($admin, $adminLetter));
    }

    public function test_superadmin_can_manage_all_letters_but_force_delete_is_disabled(): void
    {
        $policy = new IncomingLetterPolicy();
        $department = Department::create(['code' => 'DPT', 'name' => 'Departemen Test', 'is_active' => true]);

        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $staff = User::factory()->create(['role' => 'staff']);

        $incomingLetter = $this->createIncomingLetter($staff, $department, [
            'letter_number' => 'SUPER/001',
        ]);

        $this->assertTrue($policy->update($superadmin, $incomingLetter));
        $this->assertTrue($policy->delete($superadmin, $incomingLetter));
        $this->assertTrue($policy->restore($superadmin, $incomingLetter));
        $this->assertFalse($policy->forceDelete($superadmin, $incomingLetter));
    }
}
