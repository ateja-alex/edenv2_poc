<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Champ_libre;
use App\Eden\Variables;

class Document_lignes_management extends Element_management {

    public $conditionnement_de_la_ligne = false;
    public $management_ligne_parent = false;
    
    public $feuille_de_temps_ids = false;

    public $nomenclature = false;

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(in_array('supprimer',$liste_options))
            unset($liste_options[array_search('supprimer',$liste_options)]);

        if(in_array('dupliquer',$liste_options))
            unset($liste_options[array_search('dupliquer',$liste_options)]);

        if(in_array('apercu',$liste_options))
            unset($liste_options[array_search('apercu',$liste_options)]);

        $liste_options[] = 'lien';

        return $liste_options;
    }

	/**
	 *
	 * Retourne le management du document lié à la ligne
	 *
	 */
	public function management_entete() {

        if(!empty($this->management_parent)) {

			return $this->management_parent;
		}

		$this->management_parent = management(str_replace('_lignes', '', $this->_type_element), $this->modele->document_id);

		return $this->management_parent;
	}

	/**
	 *
	 * Retourne le management de la ligne d'origine
	 *
	 */
	public function management_entete_origine() {

        if(empty($this->modele->type_element_source) || empty($this->modele->id_element_source))
            return null;

        if(!isset($this->management_entete()->managements_entetes_origines[$this->modele->type_element_source][$this->modele->id_element_source]))
			$this->management_entete()->managements_entetes_origines[$this->modele->type_element_source][$this->modele->id_element_source] = management($this->modele->type_element_source, $this->modele->id_element_source);

		 return $this->management_entete()->managements_entetes_origines[$this->modele->type_element_source][$this->modele->id_element_source];
	}

	/**
	 *
	 * Retourne le management de la ligne d'origine
	 *
	 */
	public function management_ligne_origine() {

        if(empty($this->modele->type_element_source) || empty($this->modele->id_element_source) || empty($this->modele->id_ligne_source))
            return null;

        $type_element_source = $this->modele->type_element_source;
        $id_element_source = $this->modele->id_element_source;
        $id_ligne_source = $this->modele->id_ligne_source;

        if(!isset($this->management_entete()->managements_entetes_lignes_origines[$type_element_source][$id_element_source])){

            $lignes_modeles = modele($this->modele->type_element_source.'_lignes')
                ->where('document_id',$id_element_source)
                ->get();

            foreach($lignes_modeles as $ligne_modele){

                $this->management_entete()->managements_entetes_lignes_origines[$type_element_source][$id_element_source][$ligne_modele->id] =
                    management($this->modele->type_element_source.'_lignes', $ligne_modele->id, $ligne_modele);
            }
        }

		return $this->management_entete()->managements_entetes_lignes_origines[$type_element_source][$id_element_source][$id_ligne_source] ?? null;
	}

	/**
	 *
	 * Retourne le management de la ligne parent (cadre d'une nomenclature)
	 *
	 */
	public function management_ligne_parent() {

        if($this->management_ligne_parent !== false)
			return $this->management_ligne_parent;

		if(!empty($this->modele->nomenclature_ligne_parent))
			$this->management_ligne_parent = management($this->_type_element, $this->modele->nomenclature_ligne_parent);

		return $this->management_ligne_parent;
	}

	/**
	 *
	 * Retourne le management du document lié à la ligne
	 *
	 */
	public function type_element() {

		return str_replace('_lignes', '', $this->_type_element);
	}

	/**
	 *
	 * Retourne true si ce management correspond à un document de vente, false sinon
	 *
	 */
	public function est_une_vente() {

		if(strpos($this->_type_element, '_vente') !== false)
			return true;

		return false;
	}

	/**
	 *
	 * Retourne true si ce management correspond à un document d'achat, false sinon
	 *
	 */
	public function est_un_achat() {

		if(strpos($this->_type_element, '_vente') !== false)
			return false;

		return true;
	}

	/**
	 *
	 * Retourne le conditionnement de la ligne
	 *
	 */
	public function conditionnement_de_la_ligne() {

        if($this->conditionnement_de_la_ligne !== false)
            return $this->conditionnement_de_la_ligne;

		$conditionnement_id = intval($this->modele->conditionnement);

		$quantite_par_conditionnement = 1;

		if(!empty($conditionnement_id)) {

            $conditionnement = $this->management_entete()->conditionnements()->where('id',$conditionnement_id)->first();
            
            if(!empty($conditionnement))
			    $quantite_par_conditionnement = $conditionnement->quantite;

		}

		// au cas où "0" ait été saisi sur la fiche article dans la partie conditionnement
		if(empty($quantite_par_conditionnement))
			$quantite_par_conditionnement = 1;

		// on traite le cas ou c'est une ligne de nomenclature, il faut également aller chercher le conditionnement de la nomenclature
		if(!empty($this->modele->nomenclature_ligne_parent)) {

			$quantite_par_conditionnement *= $this->management_ligne_parent()->conditionnement_de_la_ligne();
		}

        $this->conditionnement_de_la_ligne = $quantite_par_conditionnement;

		return $quantite_par_conditionnement;
	}

	/**
	 *
	 * 1) on met à jour les lignes d'origine du document
	 *
	 */
	protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        $this->management_entete()->lignes_articles($this->modele);

        if(!empty($this->modele->type_element_source) && !empty($this->modele->id_element_source) && !empty($this->modele->id_ligne_source)){

            $management_origine = $this->management_entete_origine();

            $type_element_source = $this->modele->type_element_source;
            $id_element_source = $this->modele->id_element_source;
            $id_ligne_source = $this->modele->id_ligne_source;

            $type_element = $this->management_entete()->_type_element;

            if(!isset($management_origine->calcule_transformations_lignes[$type_element])){

                $lignes = modele($type_element . '_lignes')
                    ->select($type_element . '_lignes.*')->where(array(

                    $type_element . '_lignes.type_element_source' => $type_element_source,
                    $type_element . '_lignes.id_element_source' => $id_element_source,
                ));

                if ($type_element == 'facture_vente')
                    $lignes->whereNull('avoir_vente_lignes.id')
                        ->leftJoin('avoir_vente_lignes', function ($join) use($type_element) {
                            $join->where('avoir_vente_lignes.type_element_source','facture_vente')
                                ->on('avoir_vente_lignes.id_ligne_source',$type_element . '_lignes.id');
                        });
				else if ($type_element == 'facture_achat')
                    $lignes->whereNull('avoir_achat_lignes.id')
                        ->leftJoin('avoir_achat_lignes', function ($join) use($type_element) {
                            $join->where('avoir_achat_lignes.type_element_source','facture_achat')
                                ->on('avoir_achat_lignes.id_ligne_source',$type_element . '_lignes.id');
                        });

                $lignes = $lignes->get()->groupBy('id_ligne_source');

                $management_origine->calcule_transformations_lignes[$type_element] = $lignes;
            }

            if(!isset($management_origine->calcule_transformations_lignes[$type_element][$id_ligne_source]))
                $management_origine->calcule_transformations_lignes[$type_element][$id_ligne_source] = collect([]);

            if(!in_array($this->modele->id,$management_origine->calcule_transformations_lignes[$type_element][$id_ligne_source]->pluck('id')->toArray()))
                $management_origine->calcule_transformations_lignes[$type_element][$id_ligne_source]->push($this->modele);
        }

		log_eden("Document_lignes_management::methodes_post_modification::Debut", 2);

		parent::methodes_post_modification($modele, $modele_avant, $modifications);

		log_eden("Document_lignes_management::methodes_post_modification::parent", 2);

		log_eden("Document_lignes_management::methodes_post_modification::parent", 2);

        //On vérifie que l'article est une nomenclature

        $article = $this->management_entete()->articles_du_document()->where('id',$this->modele->article_id)->first();

        if(!empty($article) && $article->type_article == 1 && $this->nomenclature !== false)
            $this->enregistre_nomenclatures_sous_forme_de_lignes();

		// on enregistre les nomenclatures sous forme de ligne
		log_eden("Document_lignes_management::methodes_post_modification::enregistre_nomenclatures_sous_forme_de_lignes", 2);

		// on met à jour les lignes d'origine du document
		$this->mise_a_jour_lignes_document_origine();

		log_eden("Document_lignes_management::methodes_post_modification::mise_a_jour_lignes_document_origine", 2);

		// on met à jour les lignes de ce document
		$this->mise_a_jour_lignes_document();

		log_eden("Document_lignes_management::methodes_post_modification::mise_a_jour_lignes_document", 2);

        if(!empty($this->feuille_de_temps_ids)){

            $feuilles_de_temps = modele('feuille_de_temps')
                ->whereIn('id',$this->feuille_de_temps_ids)
                ->get();

            foreach($feuilles_de_temps as $feuille_de_temps){
                management('feuille_de_temps',$feuille_de_temps->id,$feuille_de_temps)->enregistre_modele([
                    'type_element_transforme' => $this->_type_element,
                    'element_id_transforme' => $this->modele->id,
                ]);
            }
        }
    }

	/**
	 *
	 * 1) on met à jour les lignes d'origine du document
	 *
	 */
	protected function methodes_post_suppression($modele) {
		
		parent::methodes_post_suppression($modele);

        $management_entete_origine = $this->management_entete_origine();

        if(!empty($management_entete_origine) && !empty($management_entete_origine->calcule_transformations_lignes[$this->management_entete()->_type_element][$this->modele->id_ligne_source])) {

            $collection = &$management_entete_origine->calcule_transformations_lignes[$this->management_entete()->_type_element][$this->modele->id_ligne_source];

            $cle_element = null;

            foreach($collection as $cle => $element){

                if($element->id == $this->modele->id)
                    $cle_element = $cle;
            }

            if($cle_element !== null)
                $collection->forget($cle_element);
        }

		// on met à jour les lignes d'origine du document
		$this->mise_a_jour_lignes_document_origine();

        $feuilles_de_temps = modele('feuille_de_temps')
            ->where('type_element_transforme',$this->_type_element)
            ->where('element_id_transforme',$this->modele->id)
            ->get();

        foreach($feuilles_de_temps as $feuille_de_temps){
            management('feuille_de_temps',$feuille_de_temps->id,$feuille_de_temps)->enregistre_modele([
                'type_element_transforme' => null,
                'element_id_transforme' => null,
            ]);
        }
	}

	/**
	 *
	 * Enregistre les nomenclatures du document sous forme de lignes
	 *
	 */
	public function enregistre_nomenclatures_sous_forme_de_lignes() {

		$nomenclature = $this->nomenclature;

		// il n'y a plus de nomenclature, ou elle est vide, alors on supprime toutes les lignes (s'il y en a)
		if(empty($nomenclature) || $nomenclature == '[]') {
			
			$this->supprime_ligne_nomenclature();
            return;
		}

		$lignes_gerees = array();

		foreach($nomenclature as $ligne_nomenclature) {
			
			if(isset($ligne_nomenclature->article_enfant_id))
				$article_id = $ligne_nomenclature->article_enfant_id;
			else
				$article_id = $ligne_nomenclature->article_id;
			
            $management = $this->retourne_management_pour_ligne_nomenclature($ligne_nomenclature, $article_id);

			$informations = $this->prepare_donnees_a_enregistrer_pour_ligne_nomenclatire($ligne_nomenclature, $article_id);

			// maintenant on va comparer les valeurs que l'on a trouvé, avec les valeur que l'on a déjà en bdd, 
			// pour voir si l'enregistrement est nécessaire
			if($management->existe()) {

				log_eden("Le management de la ligne existe", 3);

				foreach($informations as $champ => $valeur) {
					
					$valeur_bdd = $management->modele->$champ;
					
					if(in_array($champ, array('quantite', 'tarif_apres_remise', 'tarif'))) {

						$valeur = round($valeur, 10);
						$valeur_bdd = round($valeur_bdd, 10);
					}
					
					if($valeur_bdd == $valeur)
						unset($informations[$champ]);
				}
			}

            if(isset($informations['conditionnement']) && empty($informations['conditionnement']))
                unset($informations['conditionnement']);
			
			if(!empty($informations)) {
                $management->fonction_a_eviter[] = 'maj_index_recherche';
                $management->fonction_a_eviter[] = 'log_modifications';
                $management->fonction_a_eviter[] = 'notifications';
                $management->fonction_a_eviter[] = 'retraite_modifications';
                $management->fonction_a_eviter[] = 'verifie_formatage_champs';
                $management->fonction_a_eviter[] = 'verifie_profil_enregistrement';

				$management->enregistre($informations);
			}

			$lignes_gerees[] = $management->modele->id;
		}

		// on supprime les lignes qui ont disparu
		$lignes = $this->management_entete()->lignes_articles()->where('nomenclature_ligne_parent', $this->modele->id);

		foreach($lignes as $ligne) {

			if(!in_array($ligne->id, $lignes_gerees)) {

                $management_ligne = management($this->_type_element, $ligne->id,$ligne);
                $management_ligne->management_parent = $this->management_entete();
                $management_ligne->supprime();
            }
		}
    }

	/**
	 *
	 * 
	 * Retourne le management de la ligne que l'on doit modifier pour les articles dans les nomenclatures
	 * 
	 */
	public function retourne_management_pour_ligne_nomenclature($ligne_nomenclature, $article_id) {

        $management = management($this->_type_element);

		if(!empty($ligne_nomenclature->id)) {

            $ligne_nomenclature_modele = $this->management_entete()->lignes_articles()->where('id',$ligne_nomenclature->id)->first();

            $management = management($this->_type_element, $ligne_nomenclature->id,$ligne_nomenclature_modele);
		}

        $management->management_parent = $this->management_entete();
        $management->management_ligne_parent = $this;

		return $management;
	}
	
	/**
	 * 
	 * Prépare les données à enregistrer pour une ligne de nomenclature
	 * 
	 */
	public function prepare_donnees_a_enregistrer_pour_ligne_nomenclatire($ligne_nomenclature, $article_id) {
		
		// infos à renseigner sur la ligne
		$informations = array(
			
			'document_id' => $this->modele->document_id,
			'nomenclature_ligne_parent' => $this->modele->id,
			'article_id' => $article_id,
			'quantite' => $ligne_nomenclature->quantite * $this->modele->quantite,
			'designation' => $ligne_nomenclature->designation,
			'tarif' => $ligne_nomenclature->tarif,
			'remise' => $this->modele->remise,
			'remise_globale_ligne' => $this->modele->remise_globale_ligne,
			'tarif_eco_contribution' => $ligne_nomenclature->tarif_eco_contribution ?? null,
			'application_eco_contribution' => $ligne_nomenclature->application_eco_contribution ?? null,
			'categorie_eco_contribution_id' => $ligne_nomenclature->categorie_eco_contribution_id ?? null,
			'quantite_unite_eco_contribution' => $ligne_nomenclature->quantite_unite_eco_contribution ?? null,
		);
		
		if($this->est_un_achat()) {
			
			$informations['fournisseur_id_ligne'] = $this->modele->fournisseur_id_ligne;
		}
		else {
			
			$informations['client_id_ligne'] = $this->modele->client_id_ligne;
		}
		
		$infos_a_tester = array(
			
			'type_element_source' => '',
			'id_element_source' => '',
			'id_ligne_source' => '',
			'code_article' => '',
			'nomenclature' => '[]',
			'conditionnement' => 0,
			'prix_achat' => 0,
			'marge_pourcentage' => 0,
		);
		
		foreach($infos_a_tester as $nom_champ => $valeur_defaut) {
			
			if(!isset($ligne_nomenclature->{$nom_champ})) {
				
				$informations[$nom_champ] = $valeur_defaut;
				continue;
			}
			
			if(empty($ligne_nomenclature->{$nom_champ})) {
				
				$informations[$nom_champ] = $valeur_defaut;
				continue;
			}
			
			if($ligne_nomenclature->{$nom_champ} == 'null') {
				
				$informations[$nom_champ] = $valeur_defaut;
				continue;
			}
			
			if($ligne_nomenclature->{$nom_champ} == 'undefined') {
				
				$informations[$nom_champ] = $valeur_defaut;
				continue;
			}
			
			$informations[$nom_champ] = $ligne_nomenclature->{$nom_champ};
		}

		return $informations;
	}
	
	
	/**
	 * 
	 * Supprime les lignes qui sont liée à une nomenclature
	 *
	 */
	public function supprime_ligne_nomenclature($modele = false) {

        if($modele == false)
            $modele = $this->modele;

		$lignes = modele($this->_type_element)->where('nomenclature_ligne_parent', $modele->id)->get();

		foreach($lignes as $ligne) {

			// @todo frédéric 28/10/2021 traiter les cas où il y a un message d'erreur en retour de la méthode supprime()

            $management_ligne = management($this->_type_element, $ligne);

            $management_ligne->management_parent = $this->management_entete();

			$management_ligne->supprime();
		}

		return;
	}

	/**
	 *
	 * C'est le cas ou on transforme un document en un autre
	 * On doit ajouter les infos comme la ligne source sur la nomenclature
	 *
	 */
	public function ajoute_info_ligne_source_nomenclature($type_element_source, $id_element_source) {

		$nomenclature = $this->modele->nomenclature;

		if(empty($nomenclature))
			return;

		// la ligne est une nomenclature
		foreach($nomenclature as $ligne_nomenclature) {

			$management_ligne = management($type_element_source.'_lignes', $ligne_nomenclature->id, $ligne_nomenclature);

			$sous_nomenclature = $management_ligne->modele->nomenclature;

            if(!empty($sous_nomenclature) && $management_ligne->modele->type_article == 1)
                $management_ligne->ajoute_info_ligne_source_nomenclature($type_element_source, $id_element_source);

			$ligne_nomenclature->type_element_source = $type_element_source;
			$ligne_nomenclature->id_element_source = $id_element_source;
			$ligne_nomenclature->id_ligne_source = $ligne_nomenclature->id;
			$ligne_nomenclature->id = null;
		}

	}

	/**
	 *
	 * On met à jour les lignes du document d'origine
	 *
	 */
	protected function mise_a_jour_lignes_document_origine() {

		if(empty($this->modele->type_element_source) || empty($this->modele->id_element_source) || empty($this->modele->id_ligne_source))
			return false;
		
		log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::debut', 3);

		$type_element_source = $this->modele->type_element_source;
		$id_element_source = $this->modele->id_element_source;

		// on charge le management du document d'origine
		$management_ligne_origine = $this->management_ligne_origine($id_element_source);
		$management_document_origine = $this->management_entete_origine();

        // c'est le cas ou la ligne de l'élément source a été supprimée par exemple
		if(empty($management_document_origine) || !$management_document_origine->existe() || empty($management_ligne_origine) || !$management_ligne_origine->existe())
			return false;

        $management_document_origine->managements_entete[$this->management_entete()->_type_element][$this->management_entete()->modele->id] = $this->management_entete();

        $management_ligne_origine->management_parent = $management_document_origine;
		
		log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::recuperation des managements', 3);

		// on va charger la ligne d'origine
		$ligne_element_source = $management_ligne_origine->modele;

		$quantite_transformee = $management_document_origine->calcule_nombre_transformations_ligne($this->modele->id_ligne_source);
		
		log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::calcule_nombre_transformations_ligne', 3);

		$conditionnement_origine = $management_ligne_origine->conditionnement_de_la_ligne();
		
		log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::conditionnement_de_la_ligne', 3);

		$quantite_origine = $ligne_element_source['quantite'] * $conditionnement_origine;

		if($quantite_transformee == 0) {

			// non traité
			$transforme = 0;
			$transforme_reliquat = $quantite_origine;
		}
		elseif($quantite_transformee < $quantite_origine) {

			// partiellement traité
			$transforme = 1;
			$transforme_reliquat = $quantite_origine - $quantite_transformee;
		}
		else {

			// traité
			$transforme = 2;
			$transforme_reliquat = 0;
		}

        $mise_a_jour = $transforme != $ligne_element_source['transforme'] || $transforme_reliquat != $ligne_element_source['transforme_reliquat'];
		// on enregistre le changement sur l'élément source
		if($mise_a_jour) {

            $modifications_ligne = array(

                'transforme' => $transforme,
                'transforme_reliquat' => $transforme_reliquat,
            );

            $management_entete = $this->management_principal ?? $this->management_entete();

            $management_ligne_origine->management_principal = $management_entete;

            $management_ligne_origine->fonction_a_eviter[] = 'maj_index_recherche';
            $management_ligne_origine->fonction_a_eviter[] = 'notifications';

            // mise à jour de la ligne
            $management_ligne_origine->enregistre($modifications_ligne);
			
			log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::enregistre', 3);

            if(defined('gestion_lignes')) {

                $identifiant = $management_document_origine->_type_element.'_'.$management_document_origine->modele->id;

                if(!isset($management_entete->documents_changement_reliquat[$identifiant]))
                    $management_entete->documents_changement_reliquat[$identifiant] = $management_document_origine;
            }
            else {
                // et mise à jour du reliquat du total / marge
                $management_document_origine->calcule_reliquat_ca();

                log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::calcule_reliquat_ca', 3);

                // on met à jour le statut du document
                $management_document_origine->gere_statut_automatique();

                log_eden('Document_lignes_management::mise_a_jour_lignes_document_origine::gere_statut_automatique', 3);
            }
        }

        return $mise_a_jour;

	}

	/**
	 *
	 * On met à jour les lignes du document (transforme, reliquat) => doit être fait à chaque enregistrement de la ligne
	 *
	 */
	public function mise_a_jour_lignes_document() {

		log_eden("Document_lignes_management::mise_a_jour_lignes_document::debut", 3);

		// pour des raisons de perf, ça ne sert à rien d'aller chercher les infos
		// pour les types de documents ci dessous, car dans tous les cas, la méthode retourne 0 pour ces types de document
		if(!in_array($this->_type_element, array('acompte_vente_lignes', 'facture_vente_lignes', 'avoir_vente_lignes'))) {

			// on va chercher les transformations réalisées
			$quantite_transformee = $this->management_entete()->calcule_nombre_transformations_ligne($this->modele->id);

			log_eden("Document_lignes_management::mise_a_jour_lignes_document::calcule_nombre_transformations_ligne", 3);

			// conditionnement
			$conditionnement_de_la_ligne = $this->conditionnement_de_la_ligne();

			log_eden("Document_lignes_management::mise_a_jour_lignes_document::conditionnement_de_la_ligne", 3);
		}
		else {

			$quantite_transformee = 0;
			$conditionnement_de_la_ligne = 1;
		}

		if($quantite_transformee == 0 && empty($this->modele->document_annule)) {

			// non traité
			$transforme = 0;

            $transforme_reliquat = $this->modele->quantite * $conditionnement_de_la_ligne;
		}
		elseif($quantite_transformee < $this->modele->quantite * $conditionnement_de_la_ligne && empty($this->modele->document_annule)) {

			// partiellement traité
			$transforme = 1;

            $transforme_reliquat = ($this->modele->quantite * $conditionnement_de_la_ligne) - $quantite_transformee;
		}
		else {

			// traité
			$transforme = 2;
			$transforme_reliquat = 0;
		}

		// pas de modification, on n'enregistre pas pour des raisons de perf
		if($this->modele->transforme == $transforme && $this->modele->transforme_reliquat == $transforme_reliquat)
			return;

        $modifications_ligne = array(
            'transforme' => $transforme,
            'transforme_reliquat' => $transforme_reliquat,
        );

        $this->enregistre_modele($modifications_ligne);

		log_eden("Document_lignes_management::mise_a_jour_lignes_document::enregistre_modele", 3);
	}

    /**
     *
     * Permet de récupérer la valeur de total
     *
     */
    public function total($element,$colonne){

        $this->modele = $element;

        $management_entete = $this->management_entete();

        $mode_calcul = config('eden.mode_calcul_gescom');

        // pour les achats, on est forcément en HT
        if($this->est_un_achat())
            $mode_calcul = 'ht';

        $total = service('calcul_total_sur_document')->calcule($mode_calcul,[$element],$management_entete);

        return $total[$colonne];
    }

    /**
     *
     * On gére différent les triggers pour les lignes pour les performances
     *
     */
    public function trigger_applicatif($remplacements = array()) {

        // Si on est en train d'enregistrer un document on gére le trigger en une fois
        if(empty($remplacements) && defined('gestion_lignes') && gestion_lignes == $this->management_entete()->_type_element)
            return;

        return parent::trigger_applicatif($remplacements);
    }

    /**
     *
     * On met à jour les stocks
     *
     */
    public function mise_a_jour_stocks($recreation = true){

        if (defined('gestion_lignes')) {

            $management_parent = $this->management_principal ?? $this->management_entete();

            $stocks_a_mettre_a_jour = &$management_parent->stocks_a_mettre_a_jour;

            $type_element = $this->management_entete()->_type_element;
            $element_id = $this->management_entete()->modele->id;

            if(!isset($stocks_a_mettre_a_jour[$type_element.'_'.$element_id]))
                $stocks_a_mettre_a_jour[$type_element.'_'.$element_id] = [
                    'management' => $this->management_entete(),
                    'mise_a_jour_stocks' => [],
                    'suppresion_stocks' => [],
                ];

            if ($recreation)
                $stocks_a_mettre_a_jour[$type_element.'_'.$element_id]['mise_a_jour_stocks'][] = $this;
            else
                $stocks_a_mettre_a_jour[$type_element.'_'.$element_id]['suppresion_stocks'][] = $this;

            return;
        }

        service('mouvement_de_stock')->genere_mouvement_de_stock($this->management_entete(), collect([$this]), $recreation);
    }

    /**
     *
     * Règles de gestion: pas possible de supprimer une ligne qui est à l'origine d'une autre
     *
     */
    public function supprime($modele = false) {

        if($modele === false && !empty($this->modele))
            $modele = $this->modele;

        $documents_gescom = Champ_libre::where('nom_sql','type_element_source')
            ->get()->pluck('type_element')->toArray();

        foreach($documents_gescom as $type_element) {

            $ligne = modele($type_element)
                ->where('type_element_source',$this->type_element())
                ->where('id_ligne_source',$modele->id)->first();

            if(!empty($ligne))
                return traduction('messages.php.document.suppression_ligne_impossible_origine_autre_ligne')." : ".$ligne->designation;
        }

        // on supprime les lignes enfants de la nomenclature avant la ligne elle même, sinon la contrainte de clé étrangère nomenclature_ligne_parent empêche la suppression
        if(empty($this->test_suppression))
            $this->supprime_ligne_nomenclature($modele);

        return parent::supprime($modele);
    }
    
    public function enregistre($modifications = array(), $modele = false){
        
        if(isset($modifications['feuille_de_temps_ids']))
            $this->feuille_de_temps_ids = $modifications['feuille_de_temps_ids'];

        if(isset($modifications['nomenclature'])){
            $this->nomenclature = $modifications['nomenclature'];
            unset($modifications['nomenclature']);
        }

        if(!empty($this->modele)) {

            $colonnes_non_gere = ['nomenclature','modele','type_article','stock','conditionnement_possible','afficher_nomenclature'];

            foreach ($colonnes_non_gere as $colonne) {
                if (array_key_exists($colonne, $this->modele->getAttributes()))
                    unset($this->modele->{$colonne});
            }
        }

        return parent::enregistre($modifications, $modele);
    }
}
