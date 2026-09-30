<?php

namespace App\Eden\Controllers\Extranet;

use App\Http\Controllers\Controller;

class Accueil_controller extends Controller {

    /**
     * 
     * Page de connection
     * 
     */
    public function accueil() {
        return view('eden::extranet.accueil');
    }

    public function acces_restreint() {
		  return view('eden::extranet.acces_restreint');
    }

    /**
    *
    * Connexion à un compte client sans mdp
    *
    */
    public function changement_contact($id) {

      $utilisateur_extranet = moi_extranet();
      $contact = $utilisateur_extranet->contacts_associes->where('id', $id)->first();

      $utilisateur_extranet->contact_selectionne = $contact;
      management('utilisateur_extranet',$utilisateur_extranet->id)->enregistre_modele(['contact_selectionne_id' => $contact->id]);

      return redirect()->back();
    }

    public function preferences() {
        return view('eden::extranet.preferences');
    }


}
