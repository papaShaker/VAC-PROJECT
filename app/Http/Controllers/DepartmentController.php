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


}
