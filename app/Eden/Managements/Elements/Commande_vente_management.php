<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Commande_vente_ligne;
use Illuminate\Support\Facades\DB;

class Commande_vente_management extends Commande_management {

	/**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return new Commande_vente_ligne;
	}

	/**
	 *
	 * Statuts disponibles:
	 *
	 * 0 => 'Pro forma',
     * 3 => 'AR à envoyer',
     * 5 => 'AR envoyé',
	 * 49 => 'Facturée / Expédiée / Préparée' (partiellement),
	 * 50 => 'Facturée / Expédiée / Préparée',
	 *
	 */
	public function gere_statut_automatique() {

		// pro forma
		if(empty($this->modele->valide)) {

			$this->enregistre_modele(array('statut' => 0));
			return;
		}

		// on va voir le statut par défaut, pour une commande validée
		if(empty($this->modele->envoye_par_mail))
			$statut = 3; // AR à envoyer
		else
			$statut = 5; // AR envoyé


		$articles_de_la_commande = modele('commande_vente_lignes')->where('document_id', $this->modele->id)->get();

		// il y a au moins des lignes traitées, on passe en facturée / expédiée / préparée
		if($articles_de_la_commande->whereIn('transforme', array(1, 2))->count() > 0) {

			$statut = 50;

			// est ce qu'il y a des lignes non facturées ? => on passe en partiellement facturée / expédiée / préparée
			if($articles_de_la_commande->whereIn('transforme', array(0, 1))->count() > 0)
				$statut = 49;
		}

		$this->enregistre_modele(array('statut' => $statut));

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

        if($modele->annule == 1) {

            $tags[] = '<span class="badge badge-danger">'.traduction('interface.document.badge_titre_saisie_document.annule').'</span>';
        }

        if($modele->annule == 2) {

            $tags[] = '<span class="badge badge-danger">'.traduction('interface.document.badge_titre_saisie_document.annule_partiellement').'</span>';
        }

		return implode(' ', $tags);
	}

	/**
	 *
	 * Pour mettre à jour les stocks
	 *
	 */
	protected function methodes_post_modification_document($modele, $modele_avant, $modifications) {

        if(
            (
                isset($modifications['annule']) &&
                $modifications['annule'] == 1
            )

            ||

            (
                isset($modele['annule']) &&
                isset($modele_avant['annule']) &&
                $modele['annule'] == 1 &&
                $modele['annule'] != $modele_avant['annule']
            )
        )
            $this->enregistre_annulation_sur_ligne();

		parent::methodes_post_modification_document($modele, $modele_avant, $modifications);

		// on met à jour le devis si nécessaire
		$this->enregistre_devis_comme_transforme_en_commande();

        $this->recalcul_echeances();

	}

	/**
	 *
	 * Enregistre le devis lié à la commande comme transformé en commande
	 *
	 */
	protected function enregistre_devis_comme_transforme_en_commande() {

		$documents_lies = $this->documents_lies('devis_vente');

		foreach($documents_lies as $devis) {

			$devis['management']->enregistre_modele(['transforme_en_commande' => 1]);
		}

		if(!empty($modele->client_id)) {

			management('client', $modele->client_id)->calcule_ca();
		}
	}

	/**
	 *
	 * Aide contextuelle spécifique aux devis vente
	 *
	 */
	protected function aide_contextuelle_commande_vente() {

		if($this->modele->statut == 50)
			return '';

		$html = array();

		if($this->modele->statut == 43) {

			$html[] = traduction('messages.php.aide_contextuelle.document.statut',null,[strtolower(traduction('valeurs_listes_formatees.101.valeur_43'))]);
		}
		elseif($this->modele->statut == 45) {

			$html[] = traduction('messages.php.aide_contextuelle.document.statut',null,[strtolower(traduction('valeurs_listes_formatees.101.valeur_45'))]);
		}
		else {

			$html[] = traduction('messages.php.aide_contextuelle.document.statut',null,[strtolower(champ_libre('commande_vente','valide')->modele->nom)]);
		}

		if($this->modele->statut < 45) {

			$html[] = traduction('messages.php.aide_contextuelle.document.creation_document',null,[table_libre('commande_achat')->element]).' <span class="fa fa-fw fa-plus-circle"></span>';
			$html[] = traduction('messages.php.aide_contextuelle.document.creation_document',null,[table_libre('bl_vente')->element]).' <span class="fa fa-fw fa-plus-circle"></span>';
		}

		$html[] = traduction('messages.php.aide_contextuelle.document.creation_document',null,[table_libre('facture_vente')->element]).' <span class="fa fa-fw fa-plus-circle"></span>';


		if(empty($this->modele->commande_fournisseur_realisee) || empty($this->modele->commande_fournisseur_recue) || empty($this->modele->expedie)) {

			$html[] = '<br/>';
			$html[] = traduction('messages.php.aide_contextuelle.document.informations_a_renseigner');
		}

		if(empty($this->modele->commande_fournisseur_realisee)) {

			$html[] = traduction('messages.php.aide_contextuelle.commande.commande_fournisseur_realisee').' <span class="fa fa-fw fa-check-square"></span>';
		}
		if(empty($this->modele->commande_fournisseur_recue)) {

			$html[] = traduction('messages.php.aide_contextuelle.commande.commande_fournisseur_recue').' <span class="fa fa-fw fa-check-square"></span>';
		}
		if(empty($this->modele->expedie) && $this->modele->statut < 45) {

			$html[] = traduction('messages.php.aide_contextuelle.commande.expedie').' <span class="fa fa-fw fa-truck"></span>';
		}

		return array('texte' => $html, 'id_aide' => 'commande_vente_multiples_aides');

	}

    /**
     *
     * Retouche les informations de la commande générée lors de la transforamtion d'un devis
     *
     * Cette méthode, à surcharger, sert notamment à ajouter les champs obligatoires
     *
     */
    public function retouche_infos_pour_commande_depuis_autre_document($infos) {

        return $infos;
    }

    /**
     *
     * Définie si la commande a été transformé en facture en fonction des lignes des factures ventes qui possède comme id celui de la commande
     *
     */
    public function mise_a_jour_statut_facture() {

        $facture_vente_ligne_transforme = modele('facture_vente_lignes')->where('type_element_source','commande_vente')->where('id_element_source',$this->modele->id)->first();

        if(($facture_vente_ligne_transforme !== null && $this->modele->transforme_en_facture === 0) || ($facture_vente_ligne_transforme === null && $this->modele->transforme_en_facture === 1)){

            $this->enregistre(array(
                'transforme_en_facture' => ($facture_vente_ligne_transforme !== null ? 1 : 0),
            ));

        }

    }

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['bon_preparation_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_preparation_vente']);
        $transformations_possibles['bl_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bl_vente']);
        $transformations_possibles['acompte_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'acompte_vente']);
        $transformations_possibles['facture_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'facture_vente']);
        $transformations_possibles['devis_achat'] = route('document.transformer_fournisseur', [$this->_type_element, $this->modele->id, 'devis_achat']);
        $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat';

        if(fonctionnalite('regroupement_articles_documents'))
            $transformations_possibles['commande_achat'] = 'transformer_fournisseurs_par_article_commande_achat_prefiltre';
        

        return parent::transformations_possibles($transformations_possibles);
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        if(!empty(fonctionnalite('gescom_commande_vente_annulable_non_supprimable')))
            $liste_options[] = 'annuler';

        return $liste_options;
    }

    /**
     *
     * Règles de gestion: pas possible de supprimer une facture validée
     *
     */
    public function supprime($modele = false) {

        if($modele === false && !empty($this->modele))
            $modele = $this->modele;

        if($modele->valide == 1 && fonctionnalite('gescom_commande_vente_annulable_non_supprimable') == 'annulable_non_supprimable')
            return traduction('messages.php.document.supprimer_commande_vente_annulable');

        return parent::supprime($modele);

    }

    public function enregistre_annulation_sur_ligne(){

        $lignes = $this->articles([], false, true);

        foreach ($lignes as $ligne){

            $management = management('commande_vente_lignes', $ligne->id);

            $management->management_parent = $this;

            $management->enregistre([
                'document_annule' => 1,
                'transforme_reliquat' => 0,
                'transforme_reliquat_commande_fournisseur' => 0,
                'transforme_reliquat_reception_fournisseur' => 0,
                'total_reliquat' => 0,
                'total_marge_reliquat' => 0
            ]);
        }

        $this->calcule_reliquat_ca();
    }

    /**
    *
    * Retourne les actions sur les listes
    * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
    *
    */
    public function actions_a_afficher($id_liste) {

        $actions = parent::actions_a_afficher($id_liste);

        if(!empty(fonctionnalite('gescom_commande_vente_annulable_non_supprimable')))
            $actions['annuler_documents'] = '<span class="dropdown-item" @click="modale_annuler_documents = true"><i class="fa fa-fw fa-times"></i><span v-html="$root.traduction(\'interface.listes.annuler\')"></span></span>';

        return $actions;

    }

    /**
     *
     * On annule les commandes
     *
     */
    public function annuler_documents($formulaire, $type_element){

        $commandes_annulees = 0;

        foreach ($formulaire->ids as $element_id){

            $management = management($this->_type_element, $element_id);

            if($management->modele->annule == 1)
                continue;

            $commandes_annulees++;

            $management->enregistre(['annule' => 1]);
        }

        return ['retour' => true, 'nombre_documents' => count($formulaire->ids), 'nombre_annules' => $commandes_annulees];

    }

    /**
     * @param $lignes_a_gerer
     * @return mixed
     *
     * Permet de récupérer les quantités pour les mouvements de stocks
     *
     */
    public function quantite_lignes_mouvement($lignes_a_gerer){

        $ids_lignes = $lignes_a_gerer->pluck('id')->toArray();

        $bon_preparation = modele('bon_preparation_vente_lignes')
            ->join('bl_vente_lignes',function($join){
                $join->on('bon_preparation_vente_lignes.id','bl_vente_lignes.id_ligne_source')
                    ->where('bl_vente_lignes.type_element_source','bon_preparation_vente');
            })
            ->select('bl_vente_lignes.quantite','bon_preparation_vente_lignes.id_ligne_source')
            ->where('bon_preparation_vente_lignes.type_element_source', 'commande_vente')
            ->whereIn('bon_preparation_vente_lignes.id_ligne_source', $ids_lignes);

        $facture = modele('facture_vente_lignes')
            ->select('quantite','id_ligne_source')
            ->where('type_element_source', 'commande_vente')
            ->whereIn('id_ligne_source', $ids_lignes);

        $bl_vente_lignes = modele('bl_vente_lignes')
            ->select('quantite','id_ligne_source')
            ->unionAll($bon_preparation)
            ->unionAll($facture)
            ->where('type_element_source', 'commande_vente')
            ->whereIn('id_ligne_source', $ids_lignes);

        // on va chercher la quantite reçue
        $requete = '('.vsprintf(str_replace(['?'], ['\'%s\''],$bl_vente_lignes->toSql()),$bl_vente_lignes->getBindings()).')';

        $reliquat_par_ligne = DB::table(DB::raw("{$requete} as lignes"))
            ->select(DB::raw('SUM(quantite) as quantite'),'id_ligne_source')
            ->groupBy('id_ligne_source')
            ->get()
            ->pluck('quantite','id_ligne_source')
            ->toArray();

        $quantite_par_ligne = [];

        foreach($lignes_a_gerer as $ligne){

            $quantite_recue = $reliquat_par_ligne[$ligne->id] ?? 0;

            $quantite_par_ligne[$ligne->id] = $ligne->quantite - $quantite_recue;
        }

        return $quantite_par_ligne;
    }

}
