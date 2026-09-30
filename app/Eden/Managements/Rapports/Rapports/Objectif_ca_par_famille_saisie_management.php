<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

class Objectif_ca_par_famille_saisie_management extends Rapports_management {

    /**
     *
     * On initialise les données du rapport
     *
     */
    function __construct() {
        $this->rapport = new Rapport_liste_management();
    }
    
	/**
	 * 
	 * On définit les objectifs de vente globaux par entité
	 * 
	 */
    public function genere($ajax = false) {
		
		$this->rapport->titre .= '
		    <a class="css__lien" href="'.route('base_eden.rapport.index', ['objectif_ca_par_famille']).'" style="margin-left: 50px;">
		        ' . traduction('rapport.objectif_ca_par_famille_saisie.titre_affichage') . '
            </a>';
		
		// on récupère les différents filtres
		$dates = $this->dates_mensuelles($this->rapport);
		$this->enregistrer($this->rapport);
		$entite_id = $this->entite($this->rapport);
		
		$titres = array(ucfirst(table_libre('famille')->element));
		
		foreach($dates['dates'] as $periode) {
			
			$titres[] = array($periode['nom'], 'css_montant');
		}
		
		$this->rapport->titres($titres);
		
		$familles = modele('famille')->orderBy('nom')->get();
		
		foreach($familles as $famille) {
			
			$ligne = array(management('famille', $famille->id)->affiche());
			
			foreach($dates['dates'] as $periode) {
				
				$objectif = modele('objectif_vente_par_famille')
								->where('entite_id', $entite_id)
								->where('famille_id', $famille->id)
								->where('date', 'like', $periode['periode'].'%')
								->first();
				
				// on regarde la mise à jour des données
				if(request()->get('objectif_'.$entite_id.'_'.$famille->id.'_'.$periode['periode']) !== null) {
					
					if($objectif === null) {
						
						$objectif = modele('objectif_vente_par_famille');
						$objectif->entite_id = $entite_id;
						$objectif->famille_id = $famille->id;
						$objectif->date = $periode['periode'].'-01';
					}
					
					$objectif->objectif = request()->get('objectif_'.$entite_id.'_'.$famille->id.'_'.$periode['periode']);
					$objectif->save();
				}
				
				$montant_objectif = 0;
				
				if($objectif !== null)
					$montant_objectif = $objectif->objectif;
				
				$ligne[] = '<input type="text" name="objectif_'.$entite_id.'_'.$famille->id.'_'.$periode['periode'].'" class="js_champ_objectif_ca_par_famille_saisie" value="'.$montant_objectif.'" style="width: 50px;" />';
			}
			
			$this->rapport->ligne($ligne);
		}
		
		return $this->rapport->genere($ajax);
    }
	
	
	
}