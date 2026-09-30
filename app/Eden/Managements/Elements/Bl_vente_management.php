<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Bl_vente_ligne;
use Illuminate\Support\Facades\DB;

class Bl_vente_management extends Document_management {

	/**
	 *
	 * Retourne une instance du modèle pour gérer les lignes des documents
	 * Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	 * (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	 *
	 */
	public function modele_lignes() {

		return new Bl_vente_ligne;
	}

	/**
	 *
	 * On va gérer que 3 statuts pour le moment
	 *
	 * 10 : a facturer
	 * 40 : partiellement facturé
	 * 50 : facturé
	 *
	 */
	public function gere_statut_automatique() {

		// pro forma
		if(empty($this->modele->valide)) {

			$this->enregistre_modele(array('statut' => 0));
			return;
		}

        $articles_du_bl = modele('bl_vente_lignes')
            ->select('bl_vente_lignes.*',DB::raw('bl_vente_lignes.quantite = SUM(facture_vente_lignes.quantite - COALESCE(avoir_vente_lignes.quantite,0)) as transforme_totalement'))
            ->where('bl_vente_lignes.document_id', $this->modele->id)
            ->whereNull('bl_vente_lignes.nomenclature_ligne_parent')
            ->join('facture_vente_lignes',function($join){
                $join->where('facture_vente_lignes.type_element_source','bl_vente')
                    ->on('facture_vente_lignes.id_ligne_source','bl_vente_lignes.id');
            })
            ->leftJoin('avoir_vente_lignes',function($join){
                $join->where('avoir_vente_lignes.type_element_source','facture_vente')
                    ->on('avoir_vente_lignes.id_ligne_source','facture_vente_lignes.id');
            })
            ->groupBy('bl_vente_lignes.id')
            ->get();

		// par défaut le statut est non facturé
		$statut = 10;

        if($articles_du_bl->where('transforme_totalement',0)->count() > 0)
            $statut = 40;

        // il y a au moins des lignes facturées : on passe en facturé
        else if($articles_du_bl->where('transforme_totalement',1)->count() > 0)
            $statut = 50;

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

			if($modele->statut == 50) {

				$tags[] = '<span class="badge badge-success">'.traduction('interface.listes.tags_pour_liste.facture').'</span>';
			}
			elseif($modele->statut == 40) {

				$tags[] = '<span class="badge badge-warning">'.traduction('interface.listes.tags_pour_liste.partiellement_facture').'</span>';
			}
			else {

				$tags[] = '<span class="badge badge-danger">'.traduction('interface.listes.tags_pour_liste.non_facture').'</span>';
			}

		}

		return implode('<br/>', $tags);
	}

	/**
	 *
	 * On vérifie si il n'y a pas de stocks négatifs
	 *
	 * @todo prendre en compte les nomenclatures
	 * @todo prendre en compte s'il y a plusieurs fois le même article sur le document
	 * @todo prendre en compte les entrepôts
	 *
	 */
	public function valide() {

		if(!fonctionnalite('bloquer_validation_bl_si_stock_negatif'))
			return parent::valide();

		// on doit vérifier s'il y a des stocks négatifs
		$retour = $this->verifie_si_les_articles_sont_en_stock();

		if($retour !== true)
			return $retour;

		return parent::valide();
	}

    /**
     *
     * Définie si le BL a été transformé en facture en fonction des lignes des factures ventes qui possède comme id celui du BL
     *
     */
    public function mise_a_jour_statut_facture() {

        $facture_vente_ligne_transforme = modele('facture_vente_lignes')->where('type_element_source','bl_vente')->where('id_element_source',$this->modele->id)->first();

        if(($facture_vente_ligne_transforme !== null && empty($this->modele->transforme_en_facture)) || ($facture_vente_ligne_transforme === null && $this->modele->transforme_en_facture === 1)){

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
        
        $transformations_possibles['facture_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'facture_vente']);
        $transformations_possibles['bon_retour_vente'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_retour_vente']);
        
        return parent::transformations_possibles($transformations_possibles);

    }

}
