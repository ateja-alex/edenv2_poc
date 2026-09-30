<?php

namespace App\Eden\Controllers\Extranet;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Hash;

class Ticket_client_controller extends Controller {

    /*
     * 
     * Affichage de la création de ticket
     * 
     */
    public function lien_unique_vers_ticket($id_ticket, $id_user) {
		
        session()->flush();

		if(!Hash::check('easy'.$id_ticket.'dev',request()->tt ?? ''))
			return redirect()->route('extranet.login');

		if(!Hash::check('easy'.$id_user.'dev',request()->tu ?? ''))
			return redirect()->route('extranet.login');
		
		$compte = modele('contact')->where('id', $id_user)->first();

        if(empty($compte->utilisateur_extranet_id))
            return redirect()->route('extranet.login');

		management('utilisateur_extranet',$compte->utilisateur_extranet_id)->creation_session($id_user);

        return redirect()->route('base_eden.fiche.index', ['ticket_client', $id_ticket]);

    }
}