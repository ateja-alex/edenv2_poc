<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Commande_achat_ligne;
use App\Eden\Variables;

class Commande_achat_management extends Commande_management {

	/**
	*
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	*
	*/
	public function modele_lignes() {

		return new Commande_achat_ligne;
	}

	/**
	 *
	 * Affiche une liste de tags pour les listes
	 *
	 */
	public function tags_pour_liste($modele) {

		$tags = array();

		if($modele->valide != 1) {

			$tags[] = '<span class="badge badge-default">'.traduction('interface.listes.tags_pour_liste.pro_forma').'</span>';
		}
		else {

			if($modele->expedie == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.expediee').'</span>';
			}

			if($modele->livre == 1) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.livree').'</span>';
			}
		}

		return implode(' ', $tags);
	}

	/**
	 *
	 * Enregistre une réception
	 *
	 */
	public function enregistre_reception_ligne($commande_achat_ligne, $quantite = false) {

		// on regarde s'il y a un BL pour le fournisseur pour aujourd'hui
		if(fonctionnalite('gescom_un_seul_br_par_commande_achat_par_jour')) {

			$bl_achat = modele('bl_achat')
				->where('fournisseur_id', $this->modele->fournisseur_id)
				->where('commande_achat_id', $this->modele->id)
				->where('date', date('Y-m-d'))
				->first();
		}
		else {

			$bl_achat = modele('bl_achat')
				->where('fournisseur_id', $this->modele->fournisseur_id)
				->where('date', date('Y-m-d'))
				->first();
		}

		if($bl_achat !== null) {

			$bl_achat_management = management('bl_achat', $bl_achat->id);
			$articles = $bl_achat_management->articles()->keyBy('ligne');
			$infos_modifications = array();
		}
		else {

			$bl_achat_management = management('bl_achat');

			$articles = array();
            
            $infos_modifications = $bl_achat_management->retourne_informations_par_defaut();
            
            foreach ($this->modele->getAttributes() as $champ_libre => $valeur){

                if(!in_array($champ_libre, ['modifie_le','cree_le','cree_par','modifie_par','reference_document','chaine_affichage','chaine_tags_recherche','id']))
                    $infos_modifications[$champ_libre] = $valeur;
            }

            $infos_modifications = $bl_achat_management->retraite_donnees_pour_transformation($infos_modifications, $this,$this->modele);

            $infos_modifications['date'] = date('Y-m-d');

			if(fonctionnalite('gescom_un_seul_br_par_commande_achat_par_jour')) {

				$infos_modifications['commande_achat_id'] = $commande_achat_ligne->document_id;
			}
		}

		// on vient ajouter l'article réceptionné aux articles du document
		if($quantite === false) {

            $management_ligne = management('commande_achat_lignes',$commande_achat_ligne->id, $commande_achat_ligne);

            $conditionnement = $management_ligne->conditionnement_de_la_ligne();

            $quantite_article = $commande_achat_ligne->reliquat_reception / $conditionnement;

        }
		else
			$quantite_article = $quantite;

		$articles[sizeof($articles) + 1] = array(

			'article_id' => $commande_achat_ligne->article_id,
			'designation' => $commande_achat_ligne->designation,
			'description' => $commande_achat_ligne->description,
			'code_article' => $commande_achat_ligne->code_article,
			'tarif' => $commande_achat_ligne->tarif,
			'tva' => $commande_achat_ligne->tva,
			'remise' => $commande_achat_ligne->remise,
			'tarif_net' => $commande_achat_ligne->tarif_net,
			'quantite' => $quantite_article,
			'type_element_source' => 'commande_achat',
			'conditionnement' => $commande_achat_ligne->conditionnement,
			'id_element_source' => $commande_achat_ligne->document_id,
			'id_ligne_source' => $commande_achat_ligne->id,
		);

		$infos_modifications['articles'] = $articles;

		$retour = $bl_achat_management->enregistre($infos_modifications);

		if($retour !== true)
			return response()->json(array('retour' => $retour));

		// on valide le document si nécessaire
		if(empty($bl_achat_management->modele->valide)) {

			$bl_achat_management->valide();
		}

		return response()->json(array('retour' => true));
	}

	/**
	 *
	 * Retourne un modèle avec des données par défaut
	 *
	 */
	public function modele_par_defaut() {

		$modele = parent::modele_par_defaut();

		$adresse_interne_par_defaut = modele('adresse_interne')->where('par_defaut', 1)->first();

		if(!empty($adresse_interne_par_defaut)){

			$modele->adresse_de_livraison = $adresse_interne_par_defaut->id;
			$modele->adresse_de_facturation = $adresse_interne_par_defaut->id;
		}
		return $modele;
	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['bl_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bl_achat']);
        $transformations_possibles['acompte_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'acompte_achat']);

        return parent::transformations_possibles($transformations_possibles);

    }

    /**
     * @param $lignes_a_gerer
     * @return mixed
     *
     * Permet de récupérer les quantités pour les mouvements de stocks
     *
     */
    public function quantite_lignes_mouvement($lignes_a_gerer){

        return $lignes_a_gerer->pluck('reliquat_reception','id')->toArray();
    }

	public function retourne_options_articles(){
		return array_merge(['suppression_manuelle_reliquat'],parent::retourne_options_articles());
	}

	public function calcule_reliquat_pour_ligne($article, $id_ligne_source){
		if($article->suppression_manuelle_reliquat == 1)
			return 0;

		return parent::calcule_reliquat_pour_ligne($article, $id_ligne_source);
	}

}
