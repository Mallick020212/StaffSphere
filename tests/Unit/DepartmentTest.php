<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_department()
    {
        $data = ['name' => 'IT Department'];

        $response = $this->postJson('/api/departments', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'IT Department']);

        $this->assertDatabaseHas('departments', $data);
    }

    /** @test */
    public function it_can_update_a_department()
    {
        $department = Department::create(['name' => 'HR']);

        $response = $this->putJson("/api/departments/{$department->id}", ['name' => 'Finance']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Finance']);

        $this->assertDatabaseHas('departments', ['name' => 'Finance']);
    }

    /** @test */
    public function it_can_delete_a_department()
    {
        $department = Department::create(['name' => 'Admin']);

        $response = $this->deleteJson("/api/departments/{$department->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    /** @test */
    public function it_can_list_departments()
    {
        Department::create(['name' => 'IT']);
        Department::create(['name' => 'HR']);

        $response = $this->getJson('/api/departments');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }
}
