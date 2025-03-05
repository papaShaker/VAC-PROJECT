<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view pending vacations table',
            'view department selector',
            'view month selector',
            'view administrar page',
            'view schedule templates',
            'edit schedules',
            'edit schedules templates',
            'eliminate template',
            'add template',
            'view employees',
            'view departments',
            'view contracts',
            'view contract types',
            'view zones',
            'view non working days',
            'view extra days',
            'view extra day types',
            'edit employees',
            'edit departments',
            'edit contracts',
            'edit contract types',
            'edit zones',
            'edit non working days',
            'edit extra days',
            'edit extra day types',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $superUser=Role::firstOrCreate(['name' => 'super_user']);
        $departmentBoss = Role::firstOrCreate(['name' => 'department_boss']);
        $rrhh = Role::firstOrCreate(['name' => 'rrhh']);
        $employee = Role::firstOrCreate(['name' => 'employee']);

        // Assign all permissions to department_boss
        $superUser->syncPermissions(Permission::all());
        $departmentBoss->syncPermissions([
        'view pending vacations table',
        'view administrar page',
        'view month selector',
        'view schedule templates',
        'edit schedules',
        'edit schedules templates',
        'eliminate template',
        'add template',
        'view employees',
        'view departments',
        'view contracts',
        'view contract types',
        'view zones',
        'view non working days',
        'view extra days',
        'view extra day types',
        'edit employees',
        'edit departments',
        'edit contracts',
        'edit extra days',
        ]);

        // Assign specific permissions to rrhh
        $rrhh->syncPermissions([
        'view pending vacations table',
        'view schedule templates',
        'view administrar page',
        'view employees',
        'view departments',
        'view contracts',
        'view contract types',
        'view zones',
        'view non working days',
        'view extra days',
        'view extra day types',
        'edit contract types',
        'edit zones',
        'edit non working days',
        'edit extra day types',
        ]);

        // Assign specific permissions to rrhh
        $employee->syncPermissions([
            'view schedule templates',
        ]);


        // Assign roles to specific users (update these user IDs as needed)
        $superUser1 = User::find(2);
        $boss1Informatica = User::find(28);
        $boss2Booking = User::find(9);
        $boss3CustomerService = User::find(8);
        $rrhhUser = User::find(15);
        $employee1 = User::find(5);

        if ($superUser1) {
            $superUser1 ->assignRole($superUser);
        }
        if ($boss1Informatica) {
            $boss1Informatica ->assignRole($departmentBoss);
        }
        if ($boss2Booking) {
            $boss2Booking ->assignRole($departmentBoss);
        }
        if ($boss3CustomerService) {
            $boss3CustomerService ->assignRole($departmentBoss);
        }
        if ($rrhhUser) {
            $rrhhUser->assignRole($rrhh);
        }
        if ($employee1) {
            $employee1 ->assignRole($employee);
        }
    }
   
}
