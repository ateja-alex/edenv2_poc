<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Eden\Managements\Ecommerce\Auth_management;

class Auth_controller extends Controller {

    /**
    * Create a new controller instance.
    *
    * @return void
    */
    public function __construct(Auth_management $auth_management) {

        // On récupère les managements
        $this->auth_management = $auth_management;
    }

    /**
    *
    * @return Response
    */
    public function connexion() {

        // On retourne la vue
        return view('eden::auth/connexion');
    }

    /**
    *
    * @return Response
    */
    public function post_connexion($requete) {

        // On appelle le management
        $resultat = $this->auth_management->connexion($requete);

        // On vérifie le résultat
        if(!$resultat['succes']) {

            // On retourne un message
    		return back()->with('erreur', $resultat['message']);
        }

        // On enregistre le client en session
        session(['ecommerce.utilisateur' => base64_encode(serialize($resultat['client']))]);
    }

    /**
    *
    * @return Response
    */
    public function inscription() {

        // On retourne la vue
        return view('eden::auth/inscription');
    }

    /**
    *
    * @return Response
    */
    public function post_inscriptionn($requete) {

        // On appelle le management
        $resultat = $this->auth_management->inscription($requete);

        // On enregistre le client en session
        session(['ecommerce.utilisateur' => base64_encode(serialize($resultat['client']))]);
    }

    /**
    *
    * @return Response
    */
    public function motdepasse() {

        // On retourne la vue
        return view('eden::auth/motdepasse');
    }

    /**
    *
    * @return Response
    */
    public function post_motdepasse($requete) {

        // On appelle le management
        $resultat = $this->auth_management->motdepasse($requete);

        // On vérifie le résultat
        if(!$resultat['succes']) {

            // On retourne un message
    		return back()->with('erreur', $resultat['message']);
        }

        // On enregistre un message de succès
        $request->session()->flash('succes', traduction('messages.php.ecommerce.auth.reinitialisation_mdp'));
    }

    /**
    *
    * @return Response
    */
    public function reinitialisation($token) {

        // On appelle le management
        $resultat = $this->auth_management->reinitialisation($token);

        // On vérifie le résultat
        if(!$resultat['succes']) {

            // On retourne un message
    		return back()->with('erreur', $resultat['message']);
        }

        // On retourne la vue
        return view('eden::auth/reinitialisation', ['client' => $resultat['client']]);
    }

    /**
    *
    * @return Response
    */
    public function post_reinitialisation($requete) {

        // On appelle le management
        $resultat = $this->auth_management->post_reinitialisation($requete);

        // On vérifie le résultat
        if(!$resultat['succes']) {

            // On retourne un message
    		return back()->with('erreur', $resultat['message']);
        }

        // On enregistre le client en session
        session(['ecommerce.utilisateur' => base64_encode(serialize($resultat['client']))]);

        // On enregistre un message de succès
        $request->session()->flash('succes', traduction('messages.php.ecommerce.auth.maj_mdp'));
    }

    /**
    *
    * @return Response
    */
    public function deconnexion() {

        // On vide la session
        session()->flush();
    }
}
