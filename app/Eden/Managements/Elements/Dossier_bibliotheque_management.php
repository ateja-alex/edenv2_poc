<?php

namespace App\Eden\Managements\Elements;


use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;

use Schema;
use Session;
use DB;

class Dossier_bibliotheque_management extends Element_management {

    /**
     *
     * On applique sur les enfants la disponibilité dans l'extranet des enfants
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        if(isset($modifications['disponible_extranet'])){

            //On récupère les dossiers enfants
            $dossiers_enfants = collect(
                DB::select('
                    WITH RECURSIVE cte AS (
                        SELECT dossier_bibliotheque.id,dossier_bibliotheque.dossier_parent
                        FROM dossier_bibliotheque
                        WHERE dossier_parent = ' . $this->modele->id . '
                        UNION ALL
                        SELECT db.id,db.dossier_parent
                        FROM dossier_bibliotheque db
                        JOIN cte ON db.dossier_parent = cte.id
                    )
                    SELECT dossier_bibliotheque.*
                    FROM cte
                    JOIN dossier_bibliotheque ON cte.id = dossier_bibliotheque.id;
                ')
            )->pluck('id')->toArray();

            //On applique la valeur disponible_extranet aux dossiers enfants récupérés
            DB::update('
                UPDATE dossier_bibliotheque 
                SET disponible_extranet = ' . $modifications['disponible_extranet'] . '
                WHERE id IN (' . implode(',', $dossiers_enfants) . ')
            ');

            //On récupère les fichiers enfants
            $fichiers_enfants = collect(
                DB::select('
                    WITH RECURSIVE cte AS (
                        SELECT dossier_bibliotheque.id,dossier_bibliotheque.dossier_parent
                        FROM dossier_bibliotheque
                        WHERE dossier_parent = ' . $this->modele->id . '
                        UNION ALL
                        SELECT db.id,db.dossier_parent
                        FROM dossier_bibliotheque db
                        JOIN cte ON db.dossier_parent = cte.id
                    )
                    SELECT fichier_bibliotheque.*
                    FROM cte
                    JOIN fichier_bibliotheque ON cte.id = fichier_bibliotheque.dossier_parent OR fichier_bibliotheque.dossier_parent = ' . $this->modele->id . '
                    GROUP BY fichier_bibliotheque.id;
                ')
            )->pluck('id')->toArray();

            //On applique la valeur disponible_extranet aux fichiers enfants récupérés
            DB::update('
                UPDATE fichier_bibliotheque 
                SET disponible_extranet = ' . $modifications['disponible_extranet'] . '
                WHERE id IN (' . implode(',', $fichiers_enfants) . ')
            ');
        }
        
        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    /**
     * @return array
     * Permet de récupérer les différents droits des entités sur un dossier
     */
	public function droits_entites(){

	    $droits_entites = array();

	    $entites_ids = modele('entite')->get()->pluck('id');

	    foreach($entites_ids as $entite_id){

            $droits_entites[$entite_id] = false;

            if($this->modele->entites()->where('id',$entite_id)->first()){

                $droits_entites[$entite_id] = true;
            }
        }

	    return $droits_entites;
    }
	
}