<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Models\Bl_achat_ligne;
use Illuminate\Support\Facades\DB;

class Bl_achat_management extends Document_management {
	
	/**
	* 
	* Retourne une instance du modèle pour gérer les lignes des documents
	* Nous sommes obligés de passer par ce type de méthode car la gestion s'effectue par une classe commune
	* (Document_management) qui est utilisée pour les factures, les devis... ces éléments ayant le même type de comportement
	* 
	*/
	public function modele_lignes() {
		
		return new Bl_achat_ligne;
	}

    /**
     *
     * Retourne la liste des transformations possibles pour un document (un devis en commande, etc)
     *
     */
    public function transformations_possibles($transformations_possibles = array()) {

        $transformations_possibles['facture_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'facture_achat']);
        $transformations_possibles['bon_retour_achat'] = route('document.transformer', [$this->_type_element, $this->modele->id, 'bon_retour_achat']);

        return parent::transformations_possibles($transformations_possibles);

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

        $articles_du_bl = modele('bl_achat_lignes')
            ->select('bl_achat_lignes.*',DB::raw('bl_achat_lignes.quantite = SUM(facture_achat_lignes.quantite) as transforme_totalement'))
            ->where('bl_achat_lignes.document_id', $this->modele->id)
            ->whereNull('bl_achat_lignes.nomenclature_ligne_parent')
            ->join('facture_achat_lignes',function($join){
                $join->where('facture_achat_lignes.type_element_source','bl_achat')
                    ->on('facture_achat_lignes.id_ligne_source','bl_achat_lignes.id');
            })
            ->groupBy('bl_achat_lignes.id')
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
}