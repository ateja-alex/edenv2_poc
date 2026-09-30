<?php

namespace App\Eden\Managements\Services;
use http\Env\Request;

class Siret_v2_api_service {

    # Constructor function
    private string $baseUrl;
    private mixed $_response;

    public function __construct() {
        $this->baseUrl = env("SIREN_URL");
    }

    public function sendRequest($recherche) {
        # Build request URL
        $url = $this->baseUrl . 'search?query=' . urlencode($recherche);
        $response = file_get_contents($url);
        $this->_response = json_decode($response);

        return true;
    }


    public function execute($recherche) {
        $retour = array();
        $retour['success'] = $this->sendRequest($recherche);
        if ($retour['success'] === true)
            $retour['retour'] = $this->_response ;

        return $retour;
    }
}

