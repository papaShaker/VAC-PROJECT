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
        ->select('id', 'email', 'name', 'department', 'holiday_zone')
        ->get()
        ->first();

        return response()->json($user);
    }

    public function getAllUsers()
    {
        $users = User::select('id', 'name', 'department', 'holiday_zone')
            ->with([
                'department:id,name',
                'nonworkingdayzone:id,zone'
            ])
            ->orderBy('name')
            ->get();

        return $users;
    }

    public function updateUser(int $id, Request $request)
    {
        $user = User::find($id);
        $user->department = $request->department_id;
        $user->holiday_zone = $request->nonworkingdayzone_id;
        $user->save();

        if (is_null($id) || is_null($request)) { // + is_null(rotation)
            
            return response()->json(['status' => 'Error', 'message' => 'Faltan parámetros.'], 400);
        }

        return response()->json(['status' => 'Success', 'message' => 'El usuario ha sido actualizado con exito.'], 200);
    }
}
