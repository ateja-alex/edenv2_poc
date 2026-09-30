<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use \GuzzleHttp\Client;

class Adresse_controller extends Controller {

    /**
     * 
     * Permet de charger les adresses via l'API Google Places
     * 
     */
    public function chargement_adresse(){

        $valeur = request()->input('valeur');

        $apiKey = config('services_eden.cle_api_google_places');
        $url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';
        $params = [
            'input' => $valeur,
            'components' => 'country:fr|country:be',
            'language' => 'fr',
            'key' => config('services_eden.cle_api_google_places'),
        ];

        $client = new Client();
        $response = $client->get($url, ['query' => $params]);
        $data = json_decode($response->getBody(), true);

        return response()->json($data);
    }

    /**
     * 
     * Permet de charger les informations détaillées d'une adresse via l'API Google Places
     * 
     */
    public function detail_adresse(){

        $place_id = request()->input('place_id');

        $apiKey = config('services_eden.cle_api_google_places');
        $url = 'https://maps.googleapis.com/maps/api/place/details/json';
        $params = [
            'placeid' => $place_id,
            'fields' => "address_component",
            'key' => config('services_eden.cle_api_google_places'),
        ];

        $client = new Client();
        $response = $client->get($url, ['query' => $params]);
        $data = json_decode($response->getBody(), true);

        return response()->json($data['result']);
    }
}