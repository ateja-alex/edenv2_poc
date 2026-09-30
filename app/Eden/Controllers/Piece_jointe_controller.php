<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;

use App\Eden\Models\Element_piece_jointe;

use App\Http\Controllers\Controller;


class Piece_jointe_controller extends Controller {

    /**
	 *
     * Récupère une PJ hors connexion     
	 *	 
     */
    public function recuperer_hors_connexion($id, $token) {

		if(md5('eden-'.$id) != $token)
			exit;
		
		$piece_jointe = Element_piece_jointe::find($id);
		
		return response()->download(storage_path('app/'.$piece_jointe->chemin));
		
    }
	
	
	
}
