<?php

namespace App\Eden\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class Ai_controller extends Controller{

    public function requete_open_ai(Request $requete,$cle_contexte){

        $reponse = service('open_ia')->requete($requete->all(), $cle_contexte);

        return response()->json([
            'reponse' => $reponse
        ]);
    }
}