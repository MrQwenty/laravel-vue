<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Password;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            if ($request->is('verify-email/**/**') === true) {
                /*return route('login', [
                    'emailToken' => Crypt::encryptString($request->getRequestUri())
                ]);*/
                if($request->route()->originalParameter('id')){
                    $user = User::find($request->route()->originalParameter('id'));
                    if($user->type == 'CRM' && $user->is_pending && !$user->hasVerifiedEmail()){
                        $token = Password::createToken($user);

                        return route('password.reset',['token' => $token,'pending' => true,'u'=>base64_encode($user->email)]);
                        //return redirect('/reset-password/'.$token.'?pending=true')->with('message','Inserisci una nuova password per confermare il tuo account');
                    }
                }
            }

            return route('login');
        }
    }
}
