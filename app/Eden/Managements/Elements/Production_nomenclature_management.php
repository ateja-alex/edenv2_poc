<?php

namespace App\Eden\Managements\Elements;

use PDF;
use File;

class Production_nomenclature_management extends Element_management {

    private $conditionnements;
	
	/**
	 *
	 * Effectue les mouvements de stock correspondants à la production
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {
		
		parent::methodes_post_modification($modele, $modele_avant, $modifications);
		
		$this->enregistre_nomenclature($modele, $modele_avant, $modifications);
	}
	
	/**
	 *
	 * Bloque la modification d'éléments
	 * 
	 */
	public function enregistre($modifications = array(), $modele = false) {

		unset($modifications['article_recherche']);

		// si pas de modèle fourni, on prend le modèle de base
		if($modele === false) {

			if(isset($this->modele) && $this->modele->exists === true)
				$modele = $this->modele;
			else {

				$this->modele = modele($this->_type_element);
				$modele = $this->modele;
			}
		}
		
		$retour = $this->possibilite_de_modifier_l_element($modele, $modifications);

		if($retour !== true)
			return $retour;
		
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
            return traduction('messages.php.production_nomenclature.suppression_impossible');

        if(isset($this->test_suppression) && $this->test_suppression === true)
            return 'test_ok' ;

        foreach ($anciens_mouvements as $ancien_mouvement){

            management('mouvement_de_stock', $ancien_mouvement['id'])->supprime();

        }

        return parent::supprime($modele);

	}
	
	/**
	 *
	 * Sert à surcharger la fonction methodes_post_modification en spécifique
	 *
	 */
	protected function enregistre_nomenclature($modele, $modele_avant, $modifications){
		
		$quantite = intval($modifications['quantite']);

        // On a produit $quantite articles d'id $modifications['article']
        $anciens_mouvements = modele('mouvement_de_stock')
            ->where('type_document', $this->_type_element)
            ->where('document_id', $this->modele->id)
            ->get()->toArray();

        $composants = modele('composition_article')
                        ->join('article','article_enfant_id', 'article.id')
						->where('composition_article.article_id', $modifications['article'])
						->select('article.id', 'quantite','composition_article.conditionnement','type_article')
						->get();

        $this->conditionnements = modele('conditionnement')->get()->pluck('quantite','id')->toArray();

        $quantites_par_article = $this->quantites_par_article($composants,$quantite);

        foreach($quantites_par_article as $article_id => $quantite_article) {

            $management_article_enfant = management('mouvement_de_stock');

            foreach ($anciens_mouvements as $ancien_mouvement) {

                if ($ancien_mouvement['article_id'] == $article_id && $ancien_mouvement['entrepot_id'] == $modifications['entrepot'])
                    $management_article_enfant = management('mouvement_de_stock', $ancien_mouvement['id']);

            }

            $management_article_enfant->enregistre([
                'date' => $modifications['date'],
				'entrepot_id' => $modifications['entrepot'],
				'article_id' => $article_id,
				'quantite' => $quantite_article * -1,
                'reserve' => $modifications['reserve'],
                'type_document' => $this->_type_element,
                'type_de_mouvement' => 6,
                'document_id' => $this->modele->id
            ]);

        }
		
		// on rentre les produits finis en stock
		$mouvement_article_parent = array(
		
			'date' => $modifications['date'],
			'entrepot_id' => $modifications['entrepot'],
			'article_id' => $modifications['article'],
			'quantite' => $quantite,
            'reserve' => $modifications['reserve'],
            'type_document' => $this->_type_element,
            'type_de_mouvement' => 6,
            'document_id' => $this->modele->id,
		);
		
		$management_article_parent = management('mouvement_de_stock');

        foreach ($anciens_mouvements as $ancien_mouvement){

            if($ancien_mouvement['article_id'] == $modifications['article'] && $ancien_mouvement['entrepot_id'] == $modifications['entrepot'])
                $management_article_parent = management('mouvement_de_stock', $ancien_mouvement['id']);

        }

        $management_article_parent->enregistre($mouvement_article_parent);
	}

    /**
     *
     * Création des mouvements de stocks pour les articles enfants de la production
     *
     */
    public function quantites_par_article($composants,$quantite,$quantites_par_article = []){

        // on sort les composants du stock
        foreach($composants as $composant) {

            if($composant->type_article == 1){

                $quantite_composant = $quantite * $composant->quantite;

                 $articles_nomenclature = modele('composition_article')
                        ->join('article','article_enfant_id', 'article.id')
						->where('composition_article.article_id', $composant->id)
						->select('article.id', 'quantite','composition_article.conditionnement','type_article')
						->get();

                 $quantites_par_article = $this->quantites_par_article($articles_nomenclature,$quantite_composant,$quantites_par_article);

                 continue;
            }

            $quantite_composant = $quantite * $composant->quantite;

            if(!empty($composant->conditionnement) && isset($this->conditionnements[$composant->conditionnement]))
                $quantite_composant *= $this->conditionnements[$composant->conditionnement];

            if(empty($quantites_par_article[$composant->id]))
                $quantites_par_article[$composant->id] = 0;

            $quantites_par_article[$composant->id] += $quantite_composant;
        }

        return $quantites_par_article;
    }
	
	/**
	 *
	 * Défini si on peut ou non modifier une production
	 * 
	 */
	protected function possibilite_de_modifier_l_element($modele, $modifications){

        $enregistrement_possible = true;

        if($this->existe()){

            foreach ($modifications as $nom_sql => $valeur){

                if($valeur != $this->modele->{$nom_sql} && $nom_sql != "reserve")
                    $enregistrement_possible = false;

            }

        }

		if(!$enregistrement_possible)
			return traduction('messages.php.production_nomenclature.modification_production');
			
		// on vérifie si c'est bien un assemblage
		if(!isset($modifications['article']))
			return traduction('messages.php.champ_obligatoire').' '.champ_libre('production_nomenclature','article')->modele->nom;

		$article = modele('article', $modifications['article']);

		if($article->type_article != 3)
			return traduction('messages.php.production_nomenclature.production_article_non_type_assemblage');
			
		return true;
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

        $donnees_pour_pdf['titre'] = traduction('tables_libres.production_nomenclature.nom_table');
        $donnees_pour_pdf['date'] = $this->champ('date')->affiche();
        $donnees_pour_pdf['article'] = $this->champ('article')->affiche();
        $donnees_pour_pdf['entrepot'] = $this->champ('entrepot')->affiche();
        $donnees_pour_pdf['quantite'] = $this->champ('quantite')->affiche();

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

        return $liste_options;
    }
}