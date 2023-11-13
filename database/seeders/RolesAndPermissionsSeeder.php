<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        //ottengo un array contenente  tutti i permessi e ruoli relativi ad ogni utente

        if(
            Schema::hasTable('model_has_permissions') && Schema::hasTable('model_has_roles') && Schema::hasTable('role_has_permissions') &&
            Schema::hasTable('roles') && Schema::hasTable('permissions')
        ){

            $users = User::where('deleted_at','=',NULL)->where('type','=','CRM')->get();
            $backup = [];
            foreach($users as $user){
                $permissions = $user->permissions;
                $roles = $user->roles;
                $backup[$user->id] = ['permissions' => [], 'roles' => []];
                foreach($permissions as $perm){
                    array_push($backup[$user->id]['permissions'],$perm->name);
                }
                foreach($roles as $role){
                    array_push($backup[$user->id]['roles'],$role->name);
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('model_has_permissions')->truncate();
                DB::table('model_has_roles')->truncate();
                DB::table('role_has_permissions')->truncate();
                DB::table('roles')->truncate();
                DB::table('permissions')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            app()[PermissionRegistrar::class]->forgetCachedPermissions();

            Permission::create(['name' => 'read_makes']);
            Permission::create(['name' => 'write_makes']);

            Permission::create(['name' => 'read_models']);
            Permission::create(['name' => 'write_models']);

            Permission::create(['name' => 'read_versions']);
            Permission::create(['name' => 'write_versions']);

            Permission::create(['name' => 'read_sides']);
            Permission::create(['name' => 'write_sides']);

            Permission::create(['name' => 'read_categories']);
            Permission::create(['name' => 'write_categories']);

            Permission::create(['name' => 'read_replacements']);
            Permission::create(['name' => 'write_replacements']);

            Permission::create(['name' => 'read_attributes']);
            Permission::create(['name' => 'write_attributes']);

            Permission::create(['name' => 'read_products']);
            Permission::create(['name' => 'write_products']);

            $role = Role::create(['name' => 'baseUser']);
            $role->givePermissionTo(['read_makes','read_versions','read_models','read_sides','read_categories','read_replacements','read_attributes','read_products']);

            $role = Role::create(['name' => 'admin']);
            $role->givePermissionTo(Permission::all());

            //scorrendo per i nuovi ruoli e permessi, li riassegno agli utenti (se uguali a quelli vecchi)

            foreach(Role::all() as $role){
                if(count($backup) > 0){
                    foreach($backup as $key => $user){
                        if(count($user['roles']) > 0){
                            foreach($user['roles'] as $userRole ){
                                if($role->name == $userRole) User::find($key)->assignRole($role->name);
                            }
                        }
                    }
                }
            }
            foreach(Permission::all() as $permission){
                if(count($backup) > 0){
                    foreach($backup as $key => $user){
                        if(count($user['permissions']) > 0){
                            foreach($user['permissions'] as $userPerm ){
                                if($permission->name == $userPerm) User::find($key)->givePermissionTo($permission->name);
                            }
                        }
                    }
                }
            }
        }


        //reset tabelle permessi e ruoli



    }
}
