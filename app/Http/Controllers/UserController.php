<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function getUsersByDepartament($departament_id)
    {
        // Fetch the users for the given department
        $users = User::where('department', $departament_id)
        ->select('id', 'email', 'name')
        ->get();
        
        return response()->$users;
    }

    public function getUserById(int $id)
    {
        $user = User::where('id', $id)
        ->select('id', 'email', 'name', 'department', 'holidayzone')
        ->get()
        ->first();

        return response()->json($user);
    }
}
