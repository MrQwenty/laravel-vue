<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{

    /**
     * Registra una nuova Company e crea un utente associato di tipo C
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {

        DB::transaction(function () use ($request) {

            $user = $request->post('user');
            $company = $request->post('company');

//            // Valido la richiesta Company
//            $companyVal = Validator::make($company, [
//                'vatNumber' =>
//                    'required|string|max:255|unique:companies'
//            ]);
//            $companyVal->validate();
//
//            // Valido la richiesta User
//            $userVal = Validator::make($user, [
//                'email' =>
//                    'required|string|email|max:255|unique:users'
//            ]);
//            $userVal->validate();

            // Registro la Company
            $companyModel = Company::create([
                "name" => $company['companyName'],
                "sdi" => $company['sdi'],
                "vatNumber" => $company['vatNumber'],
                "pec" => $company['pec'],
                "address" => $company['address'],
                "addrNum" => $company['addrNumb'],
                "zip" => $company['zip'],
                "town" => $company['town'],
                "province" => $company['province'],
            ]);
            $companyModel->save();
            $companyModel->refresh();

            $userModel = User::create([
                'name' => $company['companyName'],
                'email' => $user['email'],
                "companyId" => $companyModel->id,
                'password' => Hash::make($user['password']),
                'type' => $company['type']
            ]);
            $userModel->save();

            event(new Registered($userModel));

        });


        return response()->json("Ok");
    }
}
