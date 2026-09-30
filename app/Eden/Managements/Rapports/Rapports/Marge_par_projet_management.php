<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;

/**
* Gestion des rapports
*/
class Marge_par_projet_management extends Rapports_management {

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
	 * On calcule le CA par mois
	 * 
	 */	
    public function genere($ajax = false) {
		
        $utilisateurs = $this->utilisateurs($this->rapport);
		
		$dates = $this->dates_mensuelles($this->rapport);
		
		$projets = modele('projet')->where('date', '>=', $dates['date_debut'])->where('date', '<=', $dates['date_fin']);

        if(!empty($utilisateurs)) {
			
            $projets = $projets->where('responsable_commercial', $utilisateurs);
        }

		$nombre_projets = $projets->get()->count();
		
		$pagination = $this->pagination($this->rapport, $nombre_projets);
		
		// On applique la pagination
		$projets = $projets->take($pagination['take'])->skip($pagination['skip'])->get();
		
        $this->rapport->titres(
            array(
                ucfirst(table_libre('projet')->element),
                traduction('rapport.marge_par_projet.colonnes.achats'),
                traduction('rapport.marge_par_projet.colonnes.marge_brute'),
                traduction('rapport.marge_par_projet.colonnes.marge_nette')
            )
        );
		
		foreach($projets as $projet) {
			
			$projet_management = management('projet', $projet->id, $projet);
			
			$ligne = array(
			
				$projet_management->affiche_lien(), 

				montant_lisible($projet_management->modele->montant_achats), 
				montant_lisible($projet_management->modele->marge_brute), 
				montant_lisible($projet_management->modele->marge_nette),
			);
			
			$this->rapport->ligne($ligne);
		}
		
		return $this->rapport->genere($ajax);
    }
	
	
	
	
}