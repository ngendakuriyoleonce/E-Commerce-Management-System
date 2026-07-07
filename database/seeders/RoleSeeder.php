<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'employee-view', 'employee-create', 'employee-edit', 'employee-delete',
            'department-view', 'department-create', 'department-edit', 'department-delete',
            'position-view', 'position-create', 'position-edit', 'position-delete',
            'attendance-view', 'attendance-create', 'attendance-edit', 'attendance-delete',
            'leave-view', 'leave-create', 'leave-edit', 'leave-delete',
            'payroll-view', 'payroll-create', 'payroll-edit', 'payroll-delete',
            'user-view', 'user-create', 'user-edit', 'user-delete',
            'role-view', 'role-create', 'role-edit', 'role-delete',
        ];

        $created = collect();
        foreach ($permissions as $permission) {
            $created->push(Permission::create(['name' => $permission]));
        }

        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo($created);

        $customer = Role::create(['name' => 'Customer']);
        $customer->givePermissionTo([
            Permission::findByName('employee-view'),
            Permission::findByName('department-view'),
            Permission::findByName('position-view'),
            Permission::findByName('attendance-view'),
            Permission::findByName('attendance-create'),
            Permission::findByName('leave-view'),
            Permission::findByName('leave-create'),
            Permission::findByName('payroll-view'),
        ]);
    }
}
