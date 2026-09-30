<?php

namespace App\Eden\Managements;

use Log;
use Cookie;

use App\Eden\Models\Utilisateur;
use App\Eden\Models\Table_libre;

class Authentification_management {

    protected $support;
    protected $navigateur;

    /**
     * On instancie les variable qu'on va utiliser à plusieurs endroits
     *
     * @return void
     */
    public function __construct() {
        $this->support = est_un_mobile() ? "Téléphone mobile" : "Ordinateur";
        $this->navigateur = $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu';
    }

	/**
	 *
	 * Gère la connexion d'un utilisateur
	 *
	 */
	public function connexion($email, $mot_de_passe, $verifier_mot_de_passe = true) {

        // on cherche l'utilisateur via son mail
        $utilisateur = Utilisateur::where('email', $email)->where(function ($r) {
            $r->where('inactif', 0)->orWhereNull('inactif');
        });

        if($verifier_mot_de_passe === true)
            $utilisateur->where('mot_de_passe', '!=', '""')->whereNotNull('mot_de_passe');

        $utilisateur = $utilisateur->first();
        $adresse_ip = $this->obtenir_adresse_ip();

        if ($utilisateur === null || empty($utilisateur->autorise_a_se_connecter))
            queue('enregistre_log_connexion')::dispatch($adresse_ip, $email, 0, $this->support, $this->navigateur);

		if($utilisateur === null)
			return array(false,traduction('messages.php.connexion.email_introuvable'));
        
        if(empty($utilisateur->autorise_a_se_connecter))
            return array(false, traduction('messages.php.connexion.utilisateur_non_autorise_connexion'));

        management('utilisateur',$utilisateur->id,$utilisateur)->chargement_entites();
        
		// on ne vérifie pas le mot de passe (cas par exemple des connexion oAuth via des services comme Microsoft)
		if($verifier_mot_de_passe === false)
			return array(true, $utilisateur);

        if($utilisateur->mot_de_passe == md5('easy'.$mot_de_passe.'dev'))
            $verification_mot_de_passe = 1;
		else
            $verification_mot_de_passe = 0;
        
        // On gère le blocage des robots / tentatives de hack
        $verification_robot = $this->verification_robot($email, $verification_mot_de_passe, $adresse_ip);

        if ($verification_robot !== true)
            return array(false, $verification_robot);

        if($verification_mot_de_passe)
		    return array(true, $utilisateur);

        return array(false, traduction('messages.php.connexion.mdp_incorrect'));
	}

	/**
	 *
	 * Gère la déconnexion d'un utilisateur
	 *
	 */
	public function deconnexion() {

		// on supprime le cookie
		Cookie::forget('remember_token');

		setcookie('remember_token', '', time() - 3600, '/');

		// on vide la session
		session()->flush();

		return true;
	}

    /**
     *
     * Gère le blocage des robots / tentatives de hack
     *
     */
    public function verification_robot($email, $verification_mot_de_passe, $adresse_ip){

        // On va vérifier si cette IP à déjà essayé de se connecté x fois ou plus sans succès
        $dernieres_tentatives_connexion = modele('log_tentative_connexion')
                                            ->where('adresse_email',$email)
                                            ->where('ip',$adresse_ip)
                                            ->orderBy('id', 'desc')
                                            ->get();

        $derniere_tentative_connexion = $dernieres_tentatives_connexion->first();

        // mdp rentré correct + la dernière tentative est un succès ou il n'y en a jamais eu ( on évite des requêtes inutiles )
        if($verification_mot_de_passe === 1 && (empty($derniere_tentative_connexion) || $derniere_tentative_connexion->succes == 1)){

            queue('enregistre_log_connexion')::dispatch($adresse_ip, $email, $verification_mot_de_passe, $this->support, $this->navigateur);
            return true;
        }

        $date_derniere_connexion_ok = "2000-01-01 00:00:00";
        $date_derniere_connexion_fail = false;

        if(!empty($derniere_tentative_connexion) && $derniere_tentative_connexion->succes == 1)
            $date_derniere_connexion_ok = $derniere_tentative_connexion->date;
        else {

            $derniere_tentative_connexion_ok = $dernieres_tentatives_connexion->where('succes', 1)->first();

            if($derniere_tentative_connexion_ok !== null)
                $date_derniere_connexion_ok = $derniere_tentative_connexion_ok->date;

            if($derniere_tentative_connexion !== null)
                $date_derniere_connexion_fail = $derniere_tentative_connexion->date;
        }

        $nombre_tentatives_fail = $dernieres_tentatives_connexion->where('succes',0)
                                ->where('date', '>' ,$date_derniere_connexion_ok)
                                ->count();

        $nombre_tentatives_maximum_avant_blocage = fonctionnalite('nombre_de_tentatives_connexion_maximum_avant_blocage');

        if($nombre_tentatives_fail < $nombre_tentatives_maximum_avant_blocage){

            queue('enregistre_log_connexion')::dispatch($adresse_ip, $email, $verification_mot_de_passe, $this->support, $this->navigateur);
            return true;
        }

        if($date_derniere_connexion_fail !== false) {

            $nombre_minutes_blocage = fonctionnalite('nombre_de_minutes_blocage');

            // Cas où on doit vérifier si on est encore bloqué
            $minutes_blocage = ($nombre_tentatives_fail - $nombre_tentatives_maximum_avant_blocage) * $nombre_minutes_blocage;

            $to_time = strtotime($date_derniere_connexion_fail);
            $from_time = strtotime(date('Y-m-d H:i:s'));

            $temps_depuis_derniere_connexion = round(abs($to_time - $from_time) / 60, 2);

            $temps_restant = $minutes_blocage - $temps_depuis_derniere_connexion;

            // On bloque encore pour x minutes
            if ($temps_depuis_derniere_connexion < $minutes_blocage)
                return traduction('messages.php.connexion.compte_bloque',null,[$temps_restant]);
        }

        queue('enregistre_log_connexion')::dispatch($adresse_ip, $email, $verification_mot_de_passe, $this->support, $this->navigateur);

        if($verification_mot_de_passe === 1)
            return true;
        else
            return traduction('messages.php.connexion.mdp_incorrect');
    }

    /**
     *
     * Retourne l'adresse IP du client
     *
     */
    public function obtenir_adresse_ip(){

        $ipaddress = '';

        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];

        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

        else if (isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];

        else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];

        else if (isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];

        else if (isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];

        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    public function verification_utilisateur_et_token($id,$token){

        $utilisateur = Utilisateur::find($id);
		
		// aucun utilisateur trouvé
		if($utilisateur === null)
			return null;

		$email_utilisateur = $utilisateur->email;

        if(!empty($utilisateur->email_mot_de_passe_oublie))
			$email_utilisateur = $utilisateur->email_mot_de_passe_oublie;

		// on doit calculer un token via l'utilisateur
		$token_base_de_donnees = md5($email_utilisateur.$utilisateur->id.$utilisateur->mot_de_passe);
		
		if($token_base_de_donnees != $token) 
			return null;

        return $utilisateur;
    }
}
