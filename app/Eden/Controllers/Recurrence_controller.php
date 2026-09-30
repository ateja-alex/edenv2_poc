<?php

namespace App\Eden\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eden\Models\Recurrence;


class Recurrence_controller extends Controller {

	/**
	 * 
	 * Permet de stopper la recurrence
	 * 
	 */
    public function stopper($id_recurrence) {
		
		$recurrence = Recurrence::find($id_recurrence);
		
		$id_element = modele($recurrence->type_element)->where('id_recurrence', $id_recurrence)->orderBy('id', 'DESC')->first();
		
		$management = management($recurrence->type_element, $id_element);
		
		// on retourne un résultat
		return response()->json(array('retour' => $management->stopper_recurrence()));
    }

	
    
}
