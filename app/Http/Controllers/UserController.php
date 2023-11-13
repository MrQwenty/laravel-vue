<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMail;
use App\Models\User;
use App\Notifications\VerifyEmailQueued;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request){
        if($request->get('search')){
            return Inertia::render('Admininstration/User',[
                'users' => User::query()
                    ->where('type' ,'=','CRM')
                    ->where(function(Builder $query) use ($request){
                        $query->where('name','like','%%'.$request->get('search','').'%%')
                            ->orWhere('email','like','%%'.$request->get('search','').'%%');})
                    ->paginate(10),
                'tab' => 'users',
                'filters' => [
                    [
                        'key' => __('strings.searchFilter'),
                        'value' => $request->get('search') ?: null,
                    ],
                ]
            ]);
        }else{
            return Inertia::render('Admininstration/User',[
                'users' => User::query()
                    ->where('type' ,'=','CRM')
                    ->paginate(10),
                'tab' => 'users',
            ]);

        }

    }
    public function newUser(Request $request){
        return Inertia::render('Admininstration/User',[
            'tab' => 'users/new'
        ]);
    }
    public function showEditUser(Request $request){

        $user = User::find($request->get('id'));
        return Inertia::render('Admininstration/User',[
            'userEdit' => $user,
            'tab' => 'users/edit',
            'roles' => $user->roles,
            'permissions' => $user->permissions,
        ]);
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required|max:255|string',
            'email' => 'required|unique:users,email',
        ]);

        $user = User::create([
            'type' => 'CRM',
            'company_id' => $request->get('company_id'),
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make(Str::random(8)),
            'is_admin' => $request->get('is_admin') == true ? 1 : 0,
        ]);
        $user->is_admin = $request->get('is_admin');
        $user->save();
        if($user->is_admin){
            $user->syncRoles('admin');
        }else{
            $user->syncRoles('baseUser');

            $request->get('makes_perm') == 'rw' ? $user->givePermissionTo('write_makes') : null;

            $request->get('models_perm') == 'rw' ? $user->givePermissionTo('write_models') : null;

            $request->get('versions_perm') == 'rw' ? $user->givePermissionTo('write_versions') : null;

            $request->get('sides_perm') == 'rw' ? $user->givePermissionTo('write_sides') : null;

            $request->get('categories_perm') == 'rw' ? $user->givePermissionTo('write_categories') : null;

            $request->get('attributes_perm') == 'rw' ? $user->givePermissionTo('write_attributes') : null;

            $request->get('products_perm') == 'rw' ? $user->givePermissionTo('write_products') : null;

            $request->get('replacements_perm') == 'rw' ? $user->givePermissionTo('write_replacements') : null;
        }
        event(new Registered($user));

        return redirect()->route('admininstration.users.index')->with('message',__('user.addedUser'));
    }
    public function update(Request $request){
        $request->validate([
            'name' => 'required|max:255|string',
            'email' => 'required',
        ]);
        $user = User::find($request->route()->parameter('user'));

        $user->name = $request->get('name');
        $user->company_id = $request->get('company_id');
        $user->email = $request->get('email');
        $user->is_admin = $request->get('is_admin');

        $user->save();

        if($user->is_admin){
            $user->syncRoles('admin');
        }else{
            $user->syncRoles('baseUser');

            $request->get('makes_perm') == 'rw' ? $user->givePermissionTo('write_makes') : null;

            $request->get('models_perm') == 'rw' ? $user->givePermissionTo('write_models') : null;

            $request->get('versions_perm') == 'rw' ? $user->givePermissionTo('write_versions') : null;

            $request->get('sides_perm') == 'rw' ? $user->givePermissionTo('write_sides') : null;

            $request->get('categories_perm') == 'rw' ? $user->givePermissionTo('write_categories') : null;

            $request->get('attributes_perm') == 'rw' ? $user->givePermissionTo('write_attributes') : null;

            $request->get('products_perm') == 'rw' ? $user->givePermissionTo('write_products') : null;

            $request->get('replacements_perm') == 'rw' ? $user->givePermissionTo('write_replacements') : null;
        }

        return redirect()->route('admininstration.users.index')->with('message',__('user.editedUser'));
    }
    public function destroy(Request $request){
        if($request->user()->id == $request->get('id')){
            return redirect()->back();
        }
        $user = User::find($request->get('id'));

        $user->delete();
        return redirect()->back()->with('message',__('user.deletedUser'));
    }
    public function destroyBulk(Request $request){
        $ids = $request->route()->parameter('ids');

        $ids = explode(',',$ids);

        if(in_array($request->user()->id,$ids)){
            unset($ids[array_search($request->user()->id,$ids)]);
        }

        User::destroy($ids);

        return redirect()->back()->with('message',__('user.deletedUsers'));
    }
}
