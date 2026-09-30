<?php

namespace App\Eden\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Log;

class Google_controller extends Controller {

    public function geocodage_position(Request $requete){

        $cle_api = config('services_eden.cle_api_google_places');
        $data = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$requete->latitude.','.$requete->longitude.'&result_type=street_address|route|locality|country|postal_code&key='.$cle_api);
        $data = json_decode($data);

        return response()->json($data);
    }
}