<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Timeline_controller extends Controller {

    public function chargement_donnees(Request $formulaire){

        $request = request()->all();

        $donnees = service('echange')->chargement_donnees($request);

        return response()->json($donnees);
    }
}