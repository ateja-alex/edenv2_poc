<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Coupon_reduction_controller extends Controller {

	/**
	 * 
	 * Afficher le PDF d'un coupon réduction
	 * 
	 */
	public function afficher_pdf($id) {
		
		// $coupon_reduction = management('coupon_reduction', $id);
		
		// On retourne une réponse
		return response()->file(storage_path('app/coupon_reduction_'.$id.'.pdf'));
	}
	
}
