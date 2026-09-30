<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Rapport_suivi_bl_vente_kanban_management extends Listes_management {
	
	/**
	 * 
	 * Retourne l'affichage utilisé pour l'élément en mode Kanban
	 * 
	 */
	protected function affichage_kanban($management, $type_element, $element) {
		
		$management->reload_modele($element->id, $element);
		
		$html = '';
		
		$html .= '<div class="js_deplier_contenu_sous_element css_deplier_contenu_sous_element"><b>'.management('client', $element->client_id)->affichage_dans_kanban().'</b>';
		$html .= '<div class="js_deplier_contenu_sous_element_a_deplier css_deplier_contenu_sous_element_a_deplier">';

		if(!empty($element->projet_id))
			$html .= '<i style="font-style: italic;font-size: 11px;">'.management('projet', $element->projet_id)->affichage_dans_kanban().'</i><br/>';
		
		if(substr($element->date, 0, 4) == date('Y'))
			$html .= $element->reference_document.' ('.formate_date('d/m/Y', $element->date).')<br/>';
		else
			$html .= $element->reference_document.' ('.formate_date('d/m', $element->date).')<br/>';
		
		$html .= '<a href="'.$management->lien_vers_element($element->id).'" target="_blank">Afficher</a>';
			
		$html .= '</div></div>';
		
		return $html;
	}

} 