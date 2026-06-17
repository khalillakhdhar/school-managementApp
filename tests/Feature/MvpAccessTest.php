<?php

namespace Tests\Feature;

use App\Models\SchoolParent;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class MvpAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_api_is_disabled_by_default(): void
    {
        $this->getJson('/api/students')->assertNotFound();
    }

    public function test_parent_can_only_view_linked_students(): void
    {
        $user = User::factory()->create(['role' => 'parent']);

        $parent = SchoolParent::create([
            'first_name' => 'Demo',
            'last_name' => 'Parent',
            'phone' => '00000000',
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        $linkedStudent = Student::create([
            'first_name' => 'Linked',
            'last_name' => 'Student',
            'date_of_birth' => '2015-01-01',
            'class' => 'A',
            'level' => '1',
            'status' => 'active',
        ]);

        $otherStudent = Student::create([
            'first_name' => 'Other',
            'last_name' => 'Student',
            'date_of_birth' => '2015-01-01',
            'class' => 'B',
            'level' => '1',
            'status' => 'active',
        ]);

        $parent->students()->attach($linkedStudent->id, ['relation' => 'guardian']);

        $this->assertTrue(Gate::forUser($user)->allows('view', $linkedStudent));
        $this->assertFalse(Gate::forUser($user)->allows('view', $otherStudent));
    }
}
