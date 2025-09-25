<?php


namespace App\Http\Controllers;


use App\Models\Department;
use Illuminate\Http\Request;


class DepartmentController extends Controller
{
public function index()
{
return response()->json(Department::all());
}


public function store(Request $request)
{
$data = $request->validate([
'name' => 'required|string|max:255',
]);


$department = Department::create($data);


return response()->json($department, 201);
}


public function show($id)
{
$department = Department::with('employees')->findOrFail($id);
return response()->json($department);
}


public function update(Request $request, $id)
{
$department = Department::findOrFail($id);


$department->update($request->validate([
'name' => 'required|string|max:255',
]));


return response()->json($department);
}


public function destroy($id)
{
Department::findOrFail($id)->delete();
return response()->json(null, 204);
}
}