<?php

namespace App\Eden\Managements\Elements;

use DB;

class Feuille_de_temps_management extends Element_management {
	
	/**
	 * 
	 * @cf description sur Element_management
	 * 
	 * On ajoute le champ entite_id
	 *
	 */
	protected function retraite_modifications($modifications) {
		
		if(isset($modifications['type_element']) && isset($modifications['element_id'])) {
			
			$element = modele($modifications['type_element'], $modifications['element_id']);

            if(!empty($element->entite_id))
			    $modifications['entite_id'] = $element->entite_id;
		}
		
		return parent::retraite_modifications($modifications);
	}


	/**
	 * @cf description sur Element_management
	 * 
	 * On empêche la modification d'une feuille de temps validée
	 */
	public function enregistre($modifications = array(), $modele = false) {
		
		// on vérifie si la semaine est validée ou terminée
		$retour = $this->verifie_si_modification_feuille_de_temps_possible($modifications);
		
		if($retour !== true)
			return traduction('messages.php.feuille_de_temps.modification_impossible');

		if(!empty($this->modele) && $this->modele->valide == 1) {

			return traduction('messages.php.feuille_de_temps.modification_validee');
		}

        //On regarde si une feuille de temps n'a pas été déjà créé pour cette combinaison
        if(empty($this->modele) &&
            !empty($modifications['utilisateur_id']) &&
            !empty($modifications['date']) &&
            !empty($modifications['type_element']) &&
            !empty($modifications['element_id']) &&
            !empty($modifications['duree'])){

            $feuille_de_temps = modele('feuille_de_temps')
                ->where('utilisateur_id',$modifications['utilisateur_id'])
                ->where('date',$modifications['date'])
                ->where('type_element',$modifications['type_element'])
                ->where('element_id',$modifications['element_id']);

            if(!empty($modifications['activite_id']))
                $feuille_de_temps->where('activite_id',$modifications['activite_id']);
            else{
                $feuille_de_temps->where(function($requete){
                    $requete->whereNull('activite_id')
                        ->orWhere('activite_id',0);
                });
            }

            if(!empty($modifications['categorie_id']))
                $feuille_de_temps->where('categorie_id',$modifications['categorie_id']);
            else{
                $feuille_de_temps->where(function($requete){
                    $requete->whereNull('categorie_id')
                        ->orWhere('categorie_id',0);
                });
            }

            $feuille_de_temps_existante = $feuille_de_temps->first();

            if(!empty($feuille_de_temps_existante)){

                $nouvelle_duree = $feuille_de_temps_existante->duree + $modifications['duree'];

                $this->modele = $feuille_de_temps_existante;

                return management('feuille_de_temps',$feuille_de_temps_existante->id,$feuille_de_temps_existante)->enregistre(
                    array(
                        'duree' => $nouvelle_duree
                    )
                );
            }
        }

        $utilisateur_id = !empty($modifications['utilisateur_id']) ? $modifications['utilisateur_id'] :
            (!empty($this->modele->utilisateur_id) ? $this->modele->utilisateur_id : null);

        $utilisateur = $utilisateur_id != null ? modele('utilisateur',$utilisateur_id) : null;

        $conversion_heure_jour = 7;

        if(!empty($utilisateur)) {
            if ($utilisateur->type_contrat == 1)
                $conversion_heure_jour = $utilisateur->quantite_contrat / 5;
        }

		if(empty($conversion_heure_jour)){
			return traduction('messages.php.feuille_de_temps.quantite_contrat_invalide');
		}

        if(!empty($modifications['duree']) && empty($modifications['duree_jours']))
            $modifications['duree_jours'] = round($modifications['duree'] / $conversion_heure_jour, 2);

        else if(empty($modifications['duree']) && !empty($modifications['duree_jours']))
            $modifications['duree'] = round($modifications['duree_jours'] * $conversion_heure_jour, 2);
		
		return parent::enregistre($modifications, $modele);
	}

	/**
	 * @cf description sur Element_management
	 * 
	 * On empêche la suppression d'une feuille de temps validée
	 */
	public function supprime($modele = false) {
		
		// on vérifie si la semaine est validée ou terminée
		$retour = $this->verifie_si_modification_feuille_de_temps_possible();
		
		if($retour !== true)
			return traduction('messages.php.feuille_de_temps.modification_impossible');
		
		if($this->modele->valide == 1) {

			return traduction('messages.php.feuille_de_temps.suppression_validee');
		}
		
		return parent::supprime($modele);
	}
	
	/**
	 * 
	 * On vérifie si la feuille de temps peut être modifiée en fonction de la semaine de saisie
	 * 
	 */
	public function verifie_si_modification_feuille_de_temps_possible($modifications = array()) {
		
		$date = null;
		$id_utilisateur = null;
		
		if(isset($modifications['date']))
			$date = $modifications['date'];
		elseif(isset($this->modele->date))
			$date = $this->modele->date;
			
		if(isset($modifications['utilisateur_id']))
			$id_utilisateur = $modifications['utilisateur_id'];
		elseif(isset($this->modele->utilisateur_id))
			$id_utilisateur = $this->modele->utilisateur_id;
			
		if(empty($id_utilisateur) || empty($date))
			return true;
		
		$feuille_de_temps_periode_terminee = modele('feuille_de_temps_periode_terminee')
					->where('date_debut','<=', $date)
					->where('date_fin','>=', $date)
					->where('utilisateur_id', $id_utilisateur)
					->first();
					
		return empty($feuille_de_temps_periode_terminee);
	}
}