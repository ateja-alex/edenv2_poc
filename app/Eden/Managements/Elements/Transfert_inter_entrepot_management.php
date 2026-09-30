<?php

namespace App\Eden\Managements\Elements;

use PDF;
use File;
use App\Eden\Models\Liste_libre;

class Transfert_inter_entrepot_management extends Element_management {



	/**
	 *
	 * Effectue les mouvements de stock inter entrepots
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        $articles = modele('transfert_inter_entrepot_articles')
            ->where('transfert_inter_entrepot_id', $this->modele->id)
            ->get();

        foreach ($articles as $article){

            management('transfert_inter_entrepot_articles', $article->id,$article)->generation_mouvement_de_stock();

        }

        if(empty($this->modele->numero)) {

            $numero = $this->modele->id;

            while (strlen($numero) < 5) {

                $numero = '0' . $numero;

            }

            $numero = 'BT' . $numero;
            
            $this->enregistre_modele(['numero' => $numero]);
            
        }
        
        parent::methodes_post_modification($modele, $modele_avant, $modifications);
        
	}


	/**
	 *
	 * Bloque la modification d'éléments
	 * 
	 */
	public function enregistre($modifications = array(), $modele = false) {

		// si pas de modèle fourni, on prend le modèle de base
		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
			else {

				$this->modele = modele($this->_type_element);
				$modele = $this->modele;
			}
		}

        $enregistrement_possible = true;

        if($this->existe()){

            foreach ($modifications as $nom_sql => $valeur){

                if($valeur != $this->modele->{$nom_sql} && $nom_sql != "reserve")
                    $enregistrement_possible = false;

            }

        }

		if(!$enregistrement_possible)
			return traduction('messages.php.transfert_inter_entrepot.modification_impossible');

		return parent::enregistre($modifications, $modele);		
	}


	/**
	 *
	 * Ne supprime pas un élément
	 * 
	 */
	public function supprime($modele = false) {

        $anciens_mouvements = modele('mouvement_de_stock')
            ->where('type_document', $this->_type_element)
            ->where('document_id', $this->modele->id)
            ->get()->toArray();

        if(empty($anciens_mouvements))
		    return traduction('messages.php.transfert_inter_entrepot.suppresion_impossible');

        $articles = modele('transfert_inter_entrepot_articles')->where('transfert_inter_entrepot_id', $this->modele->id)->get();
        
        foreach ($articles as $article){
            
            management('transfert_inter_entrepot_articles', $article->id, $article)->supprime();
            
        }

        return parent::supprime($modele);

	}

    /**
     *
     *
     * Retourne les actions sur les listes
     * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
     *
     */
    public function actions_a_afficher($id_liste) {

        $actions = parent::actions_a_afficher($id_liste);

        $actions['imprimer_pdf'] = '<span class="dropdown-item" @click="modale_imprimer_pdf = true;"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.imprimer\')"></span></span>';

        return $actions;
    }

    /**
     *
     * Retourne le chemin vers le fichier PDF
     *
     */
    public function recupere_chemin_pdf($forcer_regeneration = false, $id_modele_doc = false) {

        $chemin_pdf = $this->modele->pdf;

        if($forcer_regeneration || empty($chemin_pdf) || !is_file(storage_path('app/'.$chemin_pdf))) {

            try{

                $this->creation_pdf();

            }
            catch (\Exception $e){

                $pdf = PDF::loadView("eden::pdf.erreur_generation_pdf", ['source_erreur' => $e->getMessage() . 'Line :' . $e->getLine()]);
                \Storage::put($this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf', $pdf->output());
                return $this->_type_element . '_' . $this->modele->id . '_' . date('d') . '.pdf';

            }

            $this->reload_modele();
        }

        return $this->modele->pdf;

    }

    /**
     *
     * Génère le pdf
     *
     */
    public function creation_pdf($id_modele_doc = false) {

        $donnees_pour_pdf['titre'] = traduction('tables_libres.transfert_inter_entrepot.nom_table');
        $donnees_pour_pdf['date'] = $this->champ('date')->affiche();
        $donnees_pour_pdf['entrepot_depart'] = $this->champ('entrepot_depart_id')->affiche();
        $donnees_pour_pdf['entrepot_arrive'] = $this->champ('entrepot_arrivee_id')->affiche();
        $donnees_pour_pdf['numero'] = $this->champ('numero')->affiche();

        $donnees_pour_pdf['articles'] = modele('transfert_inter_entrepot_articles')->where('transfert_inter_entrepot_id', $this->modele->id)->get();

        $conditionnements = modele('transfert_inter_entrepot_articles')
            ->select('conditionnement.*','transfert_inter_entrepot_articles.id as id')
            ->join('conditionnement','conditionnement.id','transfert_inter_entrepot_articles.conditionnement_id')
            ->where('transfert_inter_entrepot_id', $this->modele->id)->get()->keyBy('id');
        
        foreach ($donnees_pour_pdf['articles'] as &$article){
            
            $article->affichage = management('article', $article->article_id)->affiche();

            if(!empty($conditionnements[$article->id])) {

                $conditionnement = $conditionnements[$article->id];

                $article->conditionnement_affichage = $conditionnement->nom .' - '.$conditionnement->quantite;
            }
        }

        $pdf = PDF::loadView('eden::pdf.' . $this->_type_element, $donnees_pour_pdf);

        $nom_du_pdf = $this->_type_element.'_'.$this->modele->id.'.pdf';

        $chemin_base = $this->_type_element;
        $date = $this->modele->date;
        $date_annee = substr($date,0,4).'/';
        $date_mois = substr($date,5,2).'/';

        // Cas où le dossier "gescom" n'existe pas, on le créer
        if(!File::isDirectory($chemin_base))
            $retour = File::makeDirectory($chemin_base, $mode = 0777, true, true);


        // Cas où le dossier de l'année n'existe pas, le dossier du mois à l'intérieur n'existe donc pas
        if(!File::isDirectory($chemin_base . "/" . $date_annee)){

            $chemin_final = $chemin_base . "/" .$date_annee . "/" .$date_mois;
            $retour = File::makeDirectory($chemin_final, $mode = 0777, true, true);
        }
        else{

            // Le dossier de l'année existe, on vérifie si celui du mois existe également
            $chemin_final = $chemin_base . "/" .$date_annee . "/" .$date_mois;

            if(!File::isDirectory($chemin_final))
                $retour = File::makeDirectory($chemin_final, $mode = 0777, true, true);
            else
                $retour = false;
        }

        //Code pour les images en https

        $pdf->getDomPDF()->set_option("enable_php", true)->set_option("isHtml5ParserEnabled",true)->set_option('isRemoteEnabled',true);

        $contxt = stream_context_create([
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed'=> TRUE
            ]
        ]);

        $pdf->getDomPDF()->setHttpContext($contxt);

        \Storage::put($chemin_final.$nom_du_pdf, $pdf->output());

        $this->enregistre_modele(array(
            'pdf' => $chemin_final.$nom_du_pdf,
        ));

        return true;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'pdf';
        $liste_options[] = 'zoom';

        return $liste_options;
    }

    /**
	 *
	 * Crée les details d'une ligne
	 *
	 */
	public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        $liste_libre = Liste_libre::where('type_element','mouvement_de_stock')->where('id_rapport','detail_ligne_transfert_inter_entrepot')->first();

        if(empty($liste_libre))
            exception(traduction('messages.php.rapport_detail_ligne_inexistant'));

		// On va chercher les infos qui nous interessent
		$management = management($type_element, $id_element);

		// On appelle la vue qui affiche les infos que l'on veux via un render()
		$vue = "eden::listes.includes.details_ligne_document";

        $liste_management = liste('mouvement_de_stock','detail_ligne_transfert_inter_entrepot');

        $transfert_inter_entrepot_articles_id = modele('transfert_inter_entrepot_articles')
            ->where('transfert_inter_entrepot_id',$id_element)
            ->get()->pluck('id')->toArray();

        $nombres_de_lignes = modele('mouvement_de_stock')
            ->where('type_document','transfert_inter_entrepot_articles')
            ->whereIn('document_id',$transfert_inter_entrepot_articles_id)
            ->count();

        $blocs = [];

        foreach($transfert_inter_entrepot_articles_id as $id){
            $blocs[] = [
                'operateur' => 1,
                'exclu' => 0,
                'filtres' => [
                    [
                        'nom_sql' => 'document_id',
                        'type_element' => 'mouvement_de_stock',
                        'operateur' => 0,
                        'valeurs' => [
                            'montant' => $id,
                            'variable' => 'egale'
                        ]
                    ]
                ]
            ];
        }

        $recherche_avancee = [
                [
                    'operateur' => 0,
                    'exclu' => 0,
                    'filtres' => [
                        [
                            'nom_sql' => 'type_document',
                            'type_element' => 'mouvement_de_stock',
                            'valeurs' => [
                                'variable' => 'egal_a',
                                'texte' => "transfert_inter_entrepot_articles",
                            ],
                            'operateur' => 0,
                        ]
                    ]
                ],
                [
                    'operateur' => 0,
                    'exclu' => 0,
                    'blocs' => $blocs,
                    'filtres' => []
                ],
            ];

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id,[
            'recherche_avancee' => $recherche_avancee,
            'nombre_par_pages' => $nombres_de_lignes,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

		$vue_render = view($vue, array(
			'management' => $management,
			'id_liste' => $liste_libre->id,
			'type_element' => 'mouvement_de_stock',
			'id_element' => $id_element,
			'liste_libre' => $liste_libre,
			'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => [],
            'id_liste_parent' => $id_liste_parent,
		))->render();

		return array('composant' => $vue_render);
	}
}