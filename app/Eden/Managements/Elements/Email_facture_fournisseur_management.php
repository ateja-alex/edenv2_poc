<?php

namespace App\Eden\Managements\Elements;

class Email_facture_fournisseur_management extends Element_management {

	/**
	 *
	 * Affiche le contenu du mail avec les PJ dans la liste 
	 * 
	 */
	public function liste_affiche_pieces_jointes($modele) {
		
		$pieces_jointes = json_decode($modele->pieces_jointes);
		
		$html = '';
		
		foreach($pieces_jointes as $piece_jointe) {
			
			if(strpos($piece_jointe->fichier, '.pdf') === false)
				continue;
			
			if(!empty($piece_jointe->facture_achat_id) || !empty($piece_jointe->avoir_achat_id)) {
				
				$html .= '<span style="background: #FF5722; position: absolute; width: 300px; margin-left: 0px; text-align: center; color: white; padding: 10px;">Traitée</span>';
			}
			
			if(isset($piece_jointe->image)) {
				
				$html .= '<a href="storage/email_recus/'.$piece_jointe->image.'" target="_blank"><img src="storage/email_recus/'.$piece_jointe->image.'" width="400px" height="600px"></a>';
			}
			else {
				
				$html .= '<iframe src="storage/email_recus/'.$piece_jointe->fichier.'" width="400px" height="200px"></iframe>';
			}
			
		}
		
		return $html;
	}
	
}