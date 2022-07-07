<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // cleanup tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'employee_access',
            'employee_create',
            'employee_edit',
            'employee_show',
            'employee_delete',
            'vessel_access',
            'vessel_create',
            'vessel_edit',
            'vessel_show',
            'vessel_delete',
            'machinery_access',
            'machinery_create',
            'machinery_edit',
            'machinery_show',
            'machinery_delete',
            'machinery_import',
            'sub_category_create',
            'sub_category_delete',
            'sub_category_import',
            'vessel_machinery_access',
            'vessel_machinery_create',
            'vessel_machinery_edit',
            'vessel_machinery_show',
            'vessel_machinery_delete',
            'vessel_machinery_import',
            'vessel_machinery_export',
            'vessel_sub_category_edit',
            'vessel_sub_category_import',
            'vessel_sub_category_export',
            'interval_access',
            'interval_create',
            'interval_edit',
            'interval_show',
            'interval_delete',
            'interval_import',
            'running_hours_access',
            'running_hours_show',
            'running_hours_create',
            'running_hours_import',
            'running_hours_export',
            'running_hours_history_show',
            'running_hours_history_export',
            'jobs_access',
            'jobs_show',
            'jobs_create',
            'jobs_import',
            'jobs_export',
            'jobs_download_file',
            'jobs_history_show',
            'jobs_history_export',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // create roles
        $default_roles = config('user.roles');

        $superAdminRole = Role::create(['name' => $default_roles['admin']]);

        // assign admin all permissions
        $superAdminRole->givePermissionTo(Permission::all());

        $employeeRole = Role::create(['name' => $default_roles['employee']]);

        $employeePermissions = [
            'vessel_access',
            'vessel_show',
            'running_hours_access',
            'running_hours_show',
            'running_hours_create',
            'running_hours_history_show',
            'jobs_access',
            'jobs_show',
            'jobs_create',
            'jobs_history_show',
        ];

        foreach ($employeePermissions as $permission) {
            $employeeRole->givePermissionTo($permission);
        }
    }
}
