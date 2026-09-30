<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Listes_management;
use App\Eden\Models\Liste_libre;

class Stocks_management extends Element_management {

    /**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element, $type_element,$id_liste_parent) {

        $liste_libre_conditionnement = Liste_libre::where('type_element','stocks_par_conditionnement')->where('id_rapport','detail_ligne_stocks')->first();

        if(empty($liste_libre_conditionnement))
            exception("Aucun rapport detail_ligne_stocks n'a été trouvé");

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_stocks";

        $nombre_de_conditionnement = modele('conditionnement')->count() + 1;
        $liste_management = liste('stocks_par_conditionnement','detail_ligne_stocks');

        $filtres_pour_fiche = array(
            'article_id' => $management->modele->article_id,
            'entrepot_id' => $management->modele->entrepot_id
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre_conditionnement->id,[
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombre_de_conditionnement,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

		$vue_render = view($vue, array(
			'management' => $management,
			'id_liste' => $liste_libre_conditionnement->id,
			'type_element' => 'stocks_par_conditionnement',
			'id_element' => $id_element,
			'liste_libre' => $liste_libre_conditionnement,
			'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => $id_liste_parent,
		))->render();

		return array('composant' => $vue_render);
	}

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

         if(presence_conditionnement())
             $liste_options[] = 'zoom';

		return $liste_options;
    }

    /**
     * @return void
     *
     * Affichage une colonne avec l'alerte adéquat en fonction des seuils
     *
     */
    public function affichage_colonne_avec_alerte($element,$colonne){

        $valeur = $element->$colonne;
        $valeur_affichage = champ_libre('stocks',$colonne)->champ->affiche($valeur);

        if(empty($element->seuil_mini) && empty($element->seuil_alerte))
            return $valeur_affichage;

        if(empty($this->management_liste->stocks_par_conditionnement)) {

            $articles_ids = $this->management_liste->elements->pluck('article_id')->toArray();
            $entrepot_ids = $this->management_liste->elements->pluck('entrepot_id')->toArray();

            $this->management_liste->stocks_par_conditionnement = modele('stocks_par_conditionnement')
                ->whereIn('article_id',$articles_ids)
                ->whereIn('entrepot_id',$entrepot_ids)
                ->get();
        }

        $stocks_par_conditionnement = $this->management_liste->stocks_par_conditionnement;

        $stocks_conditionnement = $stocks_par_conditionnement
            ->where('article_id',$element->article_id)
            ->where('entrepot_id',$element->entrepot_id)->toArray();

        $couleur_par_score = array(
            2 => array(
                'icone' => "fa-check",
                'couleur' => "green"
            ),
            1 => array(
                'icone' => "fa-exclamation-triangle",
                'couleur' => "orange"
            ),
            0 => array(
                'icone' => "fa-exclamation-triangle",
                'couleur' => "red"
            ),
        );

        $score_globable = 2;

        if($valeur < $element->seuil_mini)
            $score_globable = 0;
        elseif($valeur < $element->seuil_alerte)
            $score_globable = 1;

        $score_conditionnement = 2;

        foreach($stocks_conditionnement as $stock){

            $valeur_stock = $stock[$colonne.'_conditionnement'];

            $score = 2;

            if($valeur_stock < $stock['seuil_mini'])
                $score = 0;
            elseif($valeur_stock < $stock['seuil_alerte'])
                $score = 1;

            if($score < $score_conditionnement)
                $score_conditionnement = $score;
        }

        $alerte='<span class="ml-auto badge" style="height: fit-content;color:white;position: relative;background-color: '.$couleur_par_score[$score_conditionnement]['couleur'].'">
            <i class="fas '.$couleur_par_score[$score_conditionnement]['icone'].'"></i>
            <div class="bulle_option" style="position:absolute;width: 12px;height: 12px;right: -5px;bottom: -5px;background-color: '.$couleur_par_score[$score_globable]['couleur'].'"></div>
        </span>';

        return '<div style="display: flex">
                    <span>'.$valeur_affichage.'</span>
                    '.$alerte.'
                </div>';
    }

    /**
     *
     * Gére l'affichage d'un champ de stock inventaire permettant de modifier le stock réel
     *
     */
    public function stock_inventaire_reel($modele) {

        return '<input onclick="event.stopPropagation()" class="input_stock_inventaire_reel" id_ligne="'.$modele->id.'">';
    }
}