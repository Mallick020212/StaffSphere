<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_an_employee()
    {
        $department = Department::create(['name' => 'IT']);

        $data = [
            'first_name' => 'Harihar',
            'last_name' => 'Mallick',
            'email' => 'harihar@example.com',
            'department_id' => $department->id,
            'contacts' => ['9999999999', '8888888888'],
            'addresses' => [['address_line' => 'Street 1']]
        ];

        $response = $this->postJson('/api/employees', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['first_name' => 'Harihar']);

        $this->assertDatabaseHas('employees', ['email' => 'harihar@example.com']);
        $this->assertDatabaseHas('employee_contacts', ['phone_number' => '9999999999']);
        $this->assertDatabaseHas('employee_addresses', ['address_line' => 'Street 1']);
    }

    /** @test */
    public function it_can_update_an_employee()
    {
        $department = Department::create(['name' => 'IT']);
        $employee = Employee::create([
            'first_name' => 'Harihar',
            'last_name' => 'Mallick',
            'email' => 'hari@example.com',
            'department_id' => $department->id
        ]);

        $data = [
            'first_name' => 'Hari',
            'last_name' => 'M.',
            'email' => 'hari_updated@example.com',
            'department_id' => $department->id,
            'contacts' => ['7777777777'],
            'addresses' => [['address_line' => 'New Street']]
        ];

        $response = $this->putJson("/api/employees/{$employee->id}", $data);

        $response->assertStatus(200)
                 ->assertJsonFragment(['first_name' => 'Hari']);

        $this->assertDatabaseHas('employees', ['email' => 'hari_updated@example.com']);
        $this->assertDatabaseHas('employee_contacts', ['phone_number' => '7777777777']);
        $this->assertDatabaseHas('employee_addresses', ['address_line' => 'New Street']);
    }

    /** @test */
    public function it_can_delete_an_employee()
    {
        $department = Department::create(['name' => 'IT']);
        $employee = Employee::create([
            'first_name' => 'Harihar',
            'last_name' => 'Mallick',
            'email' => 'hari@example.com',
            'department_id' => $department->id
        ]);

        $response = $this->deleteJson("/api/employees/{$employee->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    /** @test */
    public function it_can_search_employees()
    {
        $department = Department::create(['name' => 'IT']);
        Employee::create([
            'first_name' => 'Harihar',
            'last_name' => 'Mallick',
            'email' => 'hari@example.com',
            'department_id' => $department->id
        ]);

        $response = $this->getJson('/api/employees/search?query=Harihar');
        $response->assertStatus(200)
                 ->assertJsonFragment(['first_name' => 'Harihar']);
    }
}
