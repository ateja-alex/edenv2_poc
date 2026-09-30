<?php

namespace App\Eden\Managements\Elements;

use app\Eden\Models\Champ_libre;

class Workflow_management extends Element_management {

	/**
	 *
	 * @cf description sur Element_management
	 * 
	 * On json_encode le paramétrage si nécessaire
	 *
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		if(isset($modifications['parametrage']) && is_array($modifications['parametrage']))
			$modifications['parametrage'] = json_encode($modifications['parametrage']);
		
		return parent::enregistre($modifications);
	}
	
	/**
	 * 
	 * Cf Element_management::modele_par_defaut()
	 * 
	 */
	public function modele_par_defaut() {
		
		$modele = parent::modele_par_defaut();
		
		$modele->parametrage = [
			
			'type_element' => '',
		];
		
		return $modele;
	}
	
	/**
	 * 
	 * Execute le workflow
	 * 
	 */
	public function execute($management, $modele, $modele_avant, $modifications) {
		
		if(empty($this->modele->parametrage))
			return false;
		
		$parametrage = json_decode($this->modele->parametrage);
		
		// on vérifie les conditions
		$champs_libres = Champ_libre::where('type_element', $management->_type_element)->get();
		
		foreach($champs_libres as $champ_libre) {
			
			$filtre_avant = null;
			if(isset($parametrage->{'filtre_avant_'.$champ_libre->nom_sql}))
				$filtre_avant = $parametrage->{'filtre_avant_'.$champ_libre->nom_sql};
			
			if($filtre_avant !== null && $filtre_avant !== '') {
				
				if($champ_libre->type == 20 && $filtre_avant == 0) {
					
					if($modele_avant->{$champ_libre->nom_sql} != $filtre_avant && $modele_avant->{$champ_libre->nom_sql} !== null) {
						return false;
					}
				}
				elseif(in_array($champ_libre->type, array(2,3)) && in_array($filtre_avant, array('egal_a_0', 'superieur_a_0'))) {
					
					if(empty($management->modele->{$champ_libre->nom_sql}) && $filtre_avant == 'superieur_a_0') {
						return false;
					}
					
					if(!empty($management->modele->{$champ_libre->nom_sql}) && $filtre_avant == 'egal_a_0') {
						return false;
					}
				}
				else {
					
					if($modele_avant->{$champ_libre->nom_sql} != $parametrage->{'filtre_avant_'.$champ_libre->nom_sql}) {
						return false;
					}
				}
			}
			
			$filtre_apres = null;
			if(isset($parametrage->{'filtre_apres_'.$champ_libre->nom_sql}))
				$filtre_apres = $parametrage->{'filtre_apres_'.$champ_libre->nom_sql};
			
			if($filtre_apres !== null && $filtre_apres !== '') {
				
				if($champ_libre->type == 20 && $filtre_apres == 0) {
					
					if($management->modele->{$champ_libre->nom_sql} != $filtre_apres && $management->modele->{$champ_libre->nom_sql} !== null) {
						return false;
					}
				}
				elseif(in_array($champ_libre->type, array(2,3)) && in_array($filtre_apres, array('egal_a_0', 'superieur_a_0'))) {
					
					if(empty($management->modele->{$champ_libre->nom_sql}) && $filtre_apres == 'superieur_a_0') {
						return false;
					}
					
					if(!empty($management->modele->{$champ_libre->nom_sql}) && $filtre_apres == 'egal_a_0') {
						return false;
					}
				}
				else {
					
					if($management->modele->{$champ_libre->nom_sql} != $parametrage->{'filtre_apres_'.$champ_libre->nom_sql}) {
						return false;
					}
				}
			}
		}
		
		// on envoie un mail
		if($this->modele->type_action == 1) {
			
			$this->workflow_envoi_mail($parametrage, $management);
		}
		
		// on modifie un élément
		if($this->modele->type_action == 2) {
			
			$this->workflow_modifier_element($parametrage, $management);
		}
		
		// on crée un élément
		if($this->modele->type_action == 3) {
			
			$this->workflow_creer_element($parametrage, $management);
		}
		
		// notifier
		if($this->modele->type_action == 4) {
			
			$this->workflow_notifier($parametrage, $management);
		}
		
		return true;
	}
	
	/**
	 * 
	 * Action de workflow #1 : envoyer un mail
	 * 
	 */
	protected function workflow_envoi_mail($parametrage, $management) {
		
		if(empty($parametrage->destinataire_1) && empty($parametrage->type_destinataire_1))
			return false;
		
		if(empty($parametrage->modele_email))
			return false;
		
		$destinataires = array('to' => array());
		
		
		
		for($i=1; $i<=3; $i++) {
			
			if(isset($parametrage->{'type_destinataire_'.$i}) && $parametrage->{'type_destinataire_'.$i} == 'utilisateur_connecte') {
				
				$destinataires['to'][] = moi()->adresse_email;
			}

			elseif(isset($parametrage->{'type_destinataire_'.$i}) && $parametrage->{'type_destinataire_'.$i} == 'mail_associe') {

				$recuperation_adresse_email = explode('.',$parametrage->{'destinataire_'.$i});
				
				
				if(count($recuperation_adresse_email) == 1) {
					
					$nom_sql = $parametrage->{'destinataire_'.$i};
					
					$adresse_email = $management->modele->$nom_sql;
				}
				else {
					
					$adresse_email = $this->recupere_valeur_champ($management, $recuperation_adresse_email, $management->_type_element);
				}
				
				if(!empty($adresse_email))
					$destinataires['to'][] = $adresse_email;
			}

			else {
				
				if(isset($parametrage->{'destinataire_'.$i}) && !empty($parametrage->{'destinataire_'.$i}))
					$destinataires['to'][] = $parametrage->{'destinataire_'.$i};
			}
		}
		
		if(!empty($destinataires['to'])) {
			
			service('email')->envoyer_modele($parametrage->modele_email, $destinataires, $management->_type_element, $management->modele->id);
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function recupere_valeur_champ($management, $tableau_des_champs, $type_element) {
		
		$nom_sql = $tableau_des_champs[0];
		
		$champ_libre = Champ_libre::where('type_element',$type_element)->where('nom_sql',$nom_sql)->first();
		
		// un élément de recherche ajax
		if($champ_libre->type == 42) {
			
			// on récupère le type element
			$type_element_vise = $champ_libre->type_element_ajax;
			
			// on récupère l'id de l'élément
			$id_element = $management->modele->$nom_sql;
			
			$nouveau_management = management($type_element_vise, $id_element);
			
			array_shift($tableau_des_champs);
			
			return $this->recupere_valeur_champ($nouveau_management, $tableau_des_champs, $type_element_vise);
		}
		
		// a priori c'est un champ texte
		return $management->modele->$nom_sql;
		
	}
	
	/**
	 * 
	 * Action de workflow #2 : modifier un élément
	 * 
	 */
	protected function workflow_modifier_element($parametrage, $management) {
		
		if(empty($parametrage->type_element))
			return false;
		
		if($parametrage->type_element == 'lui_meme') {
			
			$management_cible = $management;
		}
		else {
			
			// on n'a pas d'infos
			if(empty($management->modele->{$parametrage->type_element}))
				return false;
			
			// un élément lié par un champ
			$champ_libre = Champ_libre::where('type_element', $management->_type_element)->where('nom_sql', $parametrage->type_element)->first();
			
			$management_cible = management($champ_libre->type_element_ajax, $management->modele->{$parametrage->type_element});
		}
		
		$infos = array();
		
		$elements_lies = Champ_libre::where('type_element', $management_cible->_type_element)->whereIn('type', array(42))->get();
		
		$variables_elements_lies = array();
		
		foreach($elements_lies as $element_lie) {
			
			if(empty($management_cible->modele->{$element_lie->nom_sql}))
				continue;
			
			$management_tmp = management($element_lie->type_element_ajax, $management_cible->modele->{$element_lie->nom_sql});
			
			$champs_libres_de_lelement_lie = Champ_libre::where('type_element', $element_lie->type_element)->get();
			
			foreach($champs_libres_de_lelement_lie as $champ_libre) {
				
				$variables_elements_lies['#'.$element_lie->nom_sql.'['.$champ_libre->nom_sql.']#'] = $management_tmp->modele->{$champ_libre->nom_sql};
			}
		}
		
		for($i=1; $i<=3; $i++) {
			
			if(!empty($parametrage->{'modification_'.$i}) && !empty($parametrage->{'valeur_'.$i})) {
				
				// on traite les variables, on se base sur les champs 42
				$infos[$parametrage->{'modification_'.$i}] = str_replace(array_keys($variables_elements_lies), $variables_elements_lies, $parametrage->{'valeur_'.$i});
			}
		}
		
		// pour éviter les boucles infinies quand l'élément se modifie lui même
		foreach($infos as $champ => $valeur) {
			
			if($management_cible->modele->$champ == $valeur)
				unset($infos[$champ]);
		}
		
		if(!empty($infos))
			$management_cible->enregistre($infos);
		
		return true;
	}
	
	/**
	 * 
	 * Action de workflow #3 : créer un élément
	 * 
	 */
	protected function workflow_creer_element($parametrage, $management) {
		
		if(empty($parametrage->type_element))
			return false;
		
		$nouvel_element = management($parametrage->type_element);
		
		// on va lister les champs de l'élément de destination
		$champs_libres = Champ_libre::where('type_element', $parametrage->type_element)->get();
		
		$infos = array();
		
		// on prépare les variables
		$champs_libres_element_source = Champ_libre::where('type_element', $management->_type_element)->get();
		
		$variables = array();
		
		foreach($champs_libres_element_source as $champ_libre) {
			
			$variables['#'.$champ_libre->nom_sql.'#'] = $management->champ($champ_libre->nom_sql)->affiche();
		}
		
		$variables_2 = array(
			
			'#utilisateur_connecte#' => moi()->id,
		);
		
		foreach($champs_libres_element_source as $champ_libre) {
			
			$variables_2['#'.$champ_libre->nom_sql.'#'] = $management->modele->{$champ_libre->nom_sql};
		}
		
		
		foreach($champs_libres as $champ_libre) {
			
			if(!empty($parametrage->{'champ_'.$champ_libre->nom_sql})) {
				
				$valeur = $parametrage->{'champ_'.$champ_libre->nom_sql};
				
				// gestion des variables
				
				// champ date
				if(in_array($champ_libre->type, array(4,5))) {
					
					$recherche = array(
						
						'j_plus_0' => date('Y-m-d H:i:s'),
						'j_plus_1' => date('Y-m-d H:i:s', strtotime('now +1 day')),
						'j_plus_2' => date('Y-m-d H:i:s', strtotime('now +2 day')),
						'j_plus_3' => date('Y-m-d H:i:s', strtotime('now +3 day')),
						'j_plus_4' => date('Y-m-d H:i:s', strtotime('now +4 day')),
						'j_plus_5' => date('Y-m-d H:i:s', strtotime('now +5 day')),
						'j_plus_6' => date('Y-m-d H:i:s', strtotime('now +6 day')),
						'j_plus_7' => date('Y-m-d H:i:s', strtotime('now +7 day')),
						'j_plus_14' => date('Y-m-d H:i:s', strtotime('now +14 day')),
						'j_plus_21' => date('Y-m-d H:i:s', strtotime('now +21 day')),
						'm_plus_1' => date('Y-m-d H:i:s', strtotime('now +1 month')),
						'm_plus_2' => date('Y-m-d H:i:s', strtotime('now +2 month')),
					);
					
					$valeur = str_replace(array_keys($recherche), $recherche, $valeur);
				}
				
				// autres champs
				$valeur = str_replace(array_keys($variables), $variables, $valeur);
				
				$infos[$champ_libre->nom_sql] = $valeur;
			}
			
			if(!empty($parametrage->{'perso_'.$champ_libre->nom_sql})) {
				
				$valeur = str_replace(array_keys($variables_2), $variables_2, $parametrage->{'perso_'.$champ_libre->nom_sql});
				
				$infos[$champ_libre->nom_sql] = $valeur;
			}
		}
		
		$nouvel_element->enregistre($infos);
		
		return true;
	}
	
	/**
	 * 
	 * Action de workflow #4 : notifier
	 * 
	 */
	protected function workflow_notifier($parametrage, $management) {
		
		if(empty($parametrage->notification_html))
			return false;
		
		if($parametrage->type_destinataire == 'utilisateur') {
			
			$notification = management('notification');
			
			$html = $parametrage->notification_html;
			
			// on remplace les variables
			$champs_libres_element_source = Champ_libre::where('type_element', $management->_type_element)->get();
			
			$variables = array();
			
			foreach($champs_libres_element_source as $champ_libre) {
				
				$variables['#'.$champ_libre->nom_sql.'#'] = $management->champ($champ_libre->nom_sql)->affiche();
			}
			
			$variables['#lien_vers_element#'] = $management->affiche_lien();
			$variables['#href_element#'] = $management->lien_vers_element($management->modele->id);
			$variables['#utilisateur_connecte#'] = moi()->prenom.' '.strtoupper(substr(moi()->nom,0,1)).'.';
			$variables['#maintenant#'] = date('d/m à H:i:s');
			
			$infos = array(
				
				'utilisateur_id' => $parametrage->destinataire,
				'contenu_html' => str_replace(array_keys($variables), $variables, $html),
				'zone' => $parametrage->zone,
				'icone' => 'fa-eye',
				'date' => date('Y-m-d H:i:s'),
				'type_element' => $management->_type_element,
				'element_id' => $management->modele->id,
			);
			
			$notification->enregistre($infos);
		}
		
		return true;
	}
}