<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // List all employees
    public function index()
    {
        return response()->json(
            Employee::with(['department','contacts','addresses'])->get()
        );
    }

    // Create employee
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => 'required|email|unique:employees,email',
            'department_id'  => 'required|exists:departments,id',
            'contacts'       => 'sometimes|array',
            'contacts.*'     => 'string',
            'addresses'      => 'sometimes|array',
            'addresses.*.address_line' => 'required_with:addresses|string',
        ]);

        // Create employee
        $employee = Employee::create(
            collect($data)->only(['first_name','last_name','email','department_id'])->toArray()
        );

        // Save contacts
        foreach ($request->input('contacts', []) as $phone) {
            $employee->contacts()->create(['phone_number' => $phone]);
        }

        // Save addresses
        foreach ($request->input('addresses', []) as $addr) {
            $employee->addresses()->create($addr);
        }

        return response()->json(
            $employee->load(['department','contacts','addresses']), 
            201
        );
    }

    // Show employee by ID
    public function show($id)
    {
        $employee = Employee::with(['department','contacts','addresses'])->findOrFail($id);
        return response()->json($employee);
    }

    // Update employee
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'first_name'     => 'sometimes|required|string|max:255',
            'last_name'      => 'sometimes|required|string|max:255',
            'email'          => 'sometimes|required|email|unique:employees,email,' . $employee->id,
            'department_id'  => 'sometimes|required|exists:departments,id',
            'contacts'       => 'sometimes|array',
            'contacts.*'     => 'string',
            'addresses'      => 'sometimes|array',
            'addresses.*.address_line' => 'required_with:addresses|string',
        ]);

        $employee->update(
            collect($data)->only(['first_name','last_name','email','department_id'])->toArray()
        );

        // Update contacts (delete + recreate for simplicity)
        if ($request->has('contacts')) {
            $employee->contacts()->delete();
            foreach ($request->input('contacts', []) as $phone) {
                $employee->contacts()->create(['phone_number' => $phone]);
            }
        }

        // Update addresses (delete + recreate for simplicity)
        if ($request->has('addresses')) {
            $employee->addresses()->delete();
            foreach ($request->input('addresses', []) as $addr) {
                $employee->addresses()->create($addr);
            }
        }

        return response()->json(
            $employee->load(['department','contacts','addresses'])
        );
    }

    // Delete employee
    public function destroy($id)
    {
        Employee::findOrFail($id)->delete();
        return response()->json(null, 204);
    }

    // Search employees by query string
    public function search(Request $request)
    {
        $q = $request->query('query');

        $employees = Employee::with(['department', 'contacts', 'addresses'])
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    if (is_numeric($q)) {
                        // Search by ID
                        $q2->where('id', $q);
                    } else {
                        // Search by first or last name
                        $q2->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%");
                    }
                });
            })
            ->get();

        return response()->json($employees);
    }
}
