<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Ibericode\Vat\Validator;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(Country::all());
    }

    /**
     * Valida una partita IVA secondo il paese inserito
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateVatNumber($code, Request $request)
    {

        $vat = $request->post('vat');

        $validator = new Validator();

        return response()->json(["validation" => $validator->validateVatNumberFormat($vat)]);

    }

}
