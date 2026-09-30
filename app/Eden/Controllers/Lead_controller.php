<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Lead_controller extends Controller {
	
	/**
	 * 
	 * Mise à jour du statut du lead
	 * 
	 */
	public function rsvp_oui($lead_id) {
		
		$lead = management('lead', $lead_id);
		
		$retour = $lead->rsvp_oui();
		
		$retour['reponse'] = 'oui';
		$retour['lead_id'] = $lead_id;
		
		return view('eden::interface_lead.rsvp', $retour);
    }
	
	/**
	 * 
	 * Mise à jour du statut du lead
	 * 
	 */
	public function rsvp_non($lead_id) {
		
		$lead = management('lead', $lead_id);
		
		$retour = $lead->rsvp_non();
		
		$retour['reponse'] = 'non';
		$retour['lead_id'] = $lead_id;
		
		return view('eden::interface_lead.rsvp', $retour);
    }
	
	/**
	 * 
	 * Mise à jour du statut du lead
	 * 
	 */
	public function rsvp_plustard($lead_id) {
		
		$lead = management('lead', $lead_id);
		
		$retour = $lead->rsvp_plustard();
		
		$retour['reponse'] = 'plustard';
		$retour['lead_id'] = $lead_id;
		
		return view('eden::interface_lead.rsvp', $retour);
    }
	
	
	/**
	 * 
	 * Mise à jour du statut du lead
	 * 
	 */
	public function rsvp_maj_reponse() {
		
		$lead = management('lead', request()->lead_id);
		
		$retour = $lead->maj_reponse_rsvp(request()->all());
		
		return view('eden::interface_lead.rsvp', $retour);
    }
	
	
}
