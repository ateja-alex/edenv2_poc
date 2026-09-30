<?php

namespace App\Eden\Managements\Listes;

use App\Eden\Managements\Listes_management;

class Liste_entite_management extends Listes_management {
	
	public function recupere_liste($id_liste, $parametres = array(), $nombre_par_page = false, $type_export = 'basique', $id_utilisateur = null) {

		$tableau = parent::recupere_liste($id_liste, $parametres, $nombre_par_page, $type_export);

        if($tableau['nombre_elements_nombres_sans_filtres'] > $tableau['options_liste']['nombre_par_page'])
            return $tableau;

		$tableau['lignes'] = $tableau['lignes']->keyBy('id')->toArray();

		foreach ($tableau['colonnes'] as $colonne)
			if(strpos($colonne['valeur'], 'nom') !== false)
				$colonne_nom_id = $colonne['id'];

        if(isset($colonne_nom_id)) {

            $lignes_temp = array();
            $niveau = 0;
            $entites = modele('entite')->get()->keyBy('id');

            $this->recupere_sous_entites($lignes_temp, $niveau, $colonne_nom_id, $tableau['lignes'], $entites);

            $tableau['lignes'] = collect($lignes_temp);
        }

		return $tableau;

	}

	private function recupere_sous_entites(&$lignes_temp, $niveau, $colonne_nom_id, $lignes, $contenu_entite) {

		$espaces = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;

        foreach ($contenu_entite as $key => $contenu){

            if($contenu->entite_parent != null && $niveau == 0)
                continue;

            if(isset($lignes[$key])) {

                for ($i = 0; $i < $niveau; $i++)
                    $lignes[$key][$colonne_nom_id]['contenu'] = $espaces . $lignes[$key][$colonne_nom_id]['contenu'];

                $lignes_temp[] = $lignes[$key];
            }

            $enfants = modele('entite')->where('entite_parent',$contenu->id)->get()->keyBy('id');

            if($enfants->isNotEmpty())
                $this->recupere_sous_entites($lignes_temp, $niveau+1, $colonne_nom_id, $lignes, $enfants);

        }
	}

}
