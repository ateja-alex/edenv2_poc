<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Hash;
use Mail;

class Extranet_service {

	/**
     *
     * Retourne la bonne url après la connexion
     *
	 */
	public function retourne_url_post_connexion() {

        if(session()->has('redirection_post_login')){

            $redirection = session()->get('redirection_post_login');

			session()->forget('redirection_post_login');

            return redirect($redirection);
        }

        return redirect(maquette('page_accueil'));

	}

	/**
	 *
	 * Retourne page accueil extranet
	 *
	 */
	public function retourne_url_page_accueil_extranet() {

		$maquette = fonctionnalite('maquette_extranet');

		if(empty($maquette))
			return '/eden/accueil';

		$maquette = modele('maquette', $maquette);

		if(empty($maquette->page_accueil))
			return '/eden/accueil';

		if(substr($maquette->page_accueil, 0, 1) == '/')
			return $maquette->page_accueil;
		else
			return '/'.$maquette->page_accueil;

        return redirect(maquette('page_accueil'));
	}

    public function champs_disponible_pour_extranet($type, $id){

        $champs_disponibles = [
            'liste_formatee' => [
            ],
            'liste_libre' => [
                3232,
                2130,
                2131,
                2522,
            ],
        ];

        if(in_array($id, $champs_disponibles[$type]))
            return true;

        return false;

    }

    /**
     *
     * Correspondance des tokens pour autoriser la connexion
     *
     */
    public function correspondance_token_connexion($token,$adresse_email){

        $config = config('services.extranet.token_log_externe',null);

        if($config === null)
            return false;

        //On construit notre token
        $token_eden = $adresse_email.$config.date('Y-m-d');

        if(Hash::check($token_eden,$token))
            return true;

        return false;
    }

    /**
     *
     * Gére la route retourné suite à la connexion
     *
     */
    public function retour_connexion_externe($parametres){
        return redirect(service('extranet')->retourne_url_page_accueil_extranet(false));
    }
}
