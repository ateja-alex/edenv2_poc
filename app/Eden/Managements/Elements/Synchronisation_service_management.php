<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use Illuminate\Encryption\Encrypter;

class Synchronisation_service_management extends Element_management{

    private $service = null;
    protected $services = [
        1 => 'brevo',
        2 => 'esalink',
    ];

    public function service(){

        if(empty($this->service)){

            $service = service($this->services[$this->modele->service]);

            $service->defini_parametres($this->parametres());

            $this->service = $service;
        }

        return $this->service;
    }

    /**
     *
     * Paramètres de connexion du service, déchiffrés
     *
     */
    public function parametres(){

        if(empty($this->modele->parametres))
            return [];

        return json_decode(self::dechiffre($this->modele->parametres), true) ?? [];
    }

    public function enregistre($modifications = array(), $modele = false){

        if(array_key_exists('parametres', $modifications) && !empty($modifications['parametres']))
            $modifications['parametres'] = self::chiffre($modifications['parametres']);

        return parent::enregistre($modifications, $modele);
    }

    public static function chiffre($valeur){

        return self::chiffreur()->encryptString($valeur);
    }

    /**
     *
     * Une valeur enregistrée avant la mise en place du chiffrement est retournée telle quelle
     *
     */
    public static function dechiffre($valeur){

        try {
            return self::chiffreur()->decryptString($valeur);
        }
        catch(\Throwable $exception) {
            return $valeur;
        }
    }

    private static function chiffreur(){

        return new Encrypter(hash('sha256', env('CLE_CRYPTAGE'), true), 'AES-256-CBC');
    }

    public function tables_externes(){
        return $this->service()->tables_externes();
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        $types_elements = modele('synchronisation_service_element')
            ->where('synchronisation_service_id', $modele->id)
            ->pluck('type_element')->unique();

        foreach($types_elements as $type_element)
            oublie_cache_eden('synchronisations.'.$type_element);

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function services_disponibles(){
        return $this->services;
    }
}
