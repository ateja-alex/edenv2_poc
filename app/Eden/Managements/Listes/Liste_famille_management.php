<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_famille_management extends Listes_management {
	
	public function recupere_liste($id_liste, $parametres = array(), $nombre_par_page = false, $type_export = 'basique', $id_utilisateur = null) {
		

		$parametres['limit'] = 1000;

		$tableau = parent::recupere_liste($id_liste, $parametres, $nombre_par_page, $type_export);

        if(empty($tableau['lignes']))
            return $tableau;
		
		if(!file_exists(storage_path('app/eden_familles.php'))) {
			
			\App\Eden\Managements\Familles_management::genere_cache();
		}
			
		$info_familles = include(storage_path('app/eden_familles.php'));

		$tableau['lignes'] = $tableau['lignes']->keyBy('id')->toArray();
		
		foreach ($tableau['colonnes'] as $colonne) {
			
			if ($colonne['valeur'] == 'nom')
				$colonne_nom_id = $colonne['id'];
		}

		$lignes_temp = array();
        $niveau = 0;
		$this->recupere_sous_famille($lignes_temp, $niveau, $colonne_nom_id, $tableau['lignes'], $tableau['lignes']);

		$tableau['lignes'] = collect($lignes_temp);

		return $tableau;

	}

	private function recupere_sous_famille(&$lignes_temp, $niveau, $colonne_nom_id, $lignes, $contenu_famille) {

		$espaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;

        $famille_affiches = [];

        foreach ($contenu_famille as $key => $contenu){

            if(isset($lignes[$key])){

                if($lignes[$key]['element']->parent_id != null && $niveau == 0)
                    continue;

                $lignes[$key]['element'] = $lignes[$key]['element']->toArray();

                $famille_affiches[] = $lignes[$key]['element']['id'];

				for($i = 0 ; $i < $niveau ; $i++)
					$lignes[$key][$colonne_nom_id]['contenu'] = $espaces.$lignes[$key][$colonne_nom_id]['contenu'];

                $lignes_temp[] = $lignes[$key];

                if((isset($contenu['element']) && !empty($contenu['element']->getRelations()['enfants'])))
				    $this->recupere_sous_famille($lignes_temp, $niveau+1, $colonne_nom_id, $lignes, $contenu['element']->getRelations()['enfants']->keyBy('id'));
                else if(!empty($contenu->getRelations()['enfants']))
                    $this->recupere_sous_famille($lignes_temp, $niveau+1, $colonne_nom_id, $lignes, $contenu->getRelations()['enfants']->keyBy('id'));

            }

        }

        if($niveau == 0) {
            foreach ($lignes as $key => $contenu){
                if(!empty($contenu['element']['parent_id']) &&
                    !in_array($contenu['element']['parent_id'],$famille_affiches)){

                    $lignes[$key]['element'] = $lignes[$key]['element']->toArray();

                    $lignes[$key][$colonne_nom_id]['contenu'] = $lignes[$key][$colonne_nom_id]['contenu'];

                    $lignes_temp[] = $lignes[$key];

                    $famille_affiches[] = $lignes[$key]['element']['id'];

                    if((isset($contenu['element']) && !empty($contenu['element']->getRelations()['enfants'])))
                        $this->recupere_sous_famille($lignes_temp, $niveau+1, $colonne_nom_id, $lignes, $contenu['element']->getRelations()['enfants']->keyBy('id'));
                    else if(!empty($contenu->getRelations()['enfants']))
                        $this->recupere_sous_famille($lignes_temp, $niveau+1, $colonne_nom_id, $lignes, $contenu->getRelations()['enfants']->keyBy('id'));

                }
            }
        }
	}

}
