<?php

namespace App\Eden\Managements\Rapports\Rapports;


use App\Eden\Managements\Calcul_gescom_management;
use App\Eden\Managements\Rapports\Rapports_management;
use App\Eden\Managements\Rapports\Rapport_liste_management;

use Illuminate\Http\Request;
use DB;

/**
* Gestion des projets
*/
class Projets_en_cours_management extends Rapports_management {

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
	 * On affiche la liste des projets en cours
	 * 
	 */	
    public function genere($ajax = false) {
        
        $titres = [ucfirst(table_libre('projet')->element_pluriel)];
		
        $this->rapport->titres($titres);
        $this->rapport->sous_titre(traduction('rapport.projets_en_cours.en_attente'));

        $projets = modele('projet')->orderBy('statut')->get();
        
        $old_statut = null;
        foreach($projets as $projet){
            if($projet->statut != $old_statut)
                $this->rapport->sous_titre(traduction('rapport.projets_en_cours.en_cours'));

            $this->rapport->ligne(array('<b>'.management('client', $projet->client_id)->affiche() . '</b> : '. management('projet', $projet->id)->affiche_lien() ));

            $old_statut = $projet->statut;
        }
        
		return $this->rapport->genere($ajax);
    }
	
	
}