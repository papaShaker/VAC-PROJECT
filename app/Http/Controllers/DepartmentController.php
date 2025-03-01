<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    //
    public function index()
    {
        // Retrieve only ID and NAME from Department
        $departments = Department::select('id', 'name')->get();

        return Inertia::render('Departments', [
            'departments' => $departments
        ]);
    }
    

    public function getDepartmentNameById($id = null){
        if($id == 'all') {
            return 'Todos los departamentos.';
        } else {
            $department = Department::select('name')->where('id', $id)->first();
            return $department->name;
        }
    }

    public function update(Request $request, $id){
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'users_same_day_holidays' => 'required|integer|min:0',
            'holidays_per_month' => 'required|integer|min:0',
        ]);

        try {
            // Find department by ID
            $department = Department::findOrFail($id);

            // Update department fields
            $department->update([
                'name' => $request->input('name'),
                'users_same_day_holidays' => $request->input('users_same_day_holidays'),
                'holidays_per_month' => $request->input('holidays_per_month'),
            ]);

            return response()->json(['status' => 'Success', 'message' => 'Departmento actualizado con éxito.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'message' => 'Ha surgido un error...', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request){
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'users_same_day_holidays' => 'required|integer|min:0',
            'holidays_per_month' => 'required|integer|min:0',
        ]);

        try {
            // Create a new department
            $department = Department::create([
                'name' => $request->input('name'),
                'users_same_day_holidays' => $request->input('users_same_day_holidays'),
                'holidays_per_month' => $request->input('holidays_per_month'),
            ]);

            return response()->json(['status' => 'Success', 'message' => 'Departamento creado con éxito.', 'department' => $department], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'message' => 'Ha surgido un error...', 'details' => $e->getMessage()], 500);
        }
    }


}
