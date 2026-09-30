<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use App\Eden\Models\Liste_libre;

class Facturation_electronique_achat_management extends Element_management {

    protected $types_code_facturx_avoir = [381, 261, 262, 396];

    /**
     *
     * Type d'élément Eden auquel la facturation électronique peut être rapprochée,
     * déterminé par le type de document (BT-3)
     *
     */
    public function type_element_rapprochement(){

        return in_array((int) $this->modele->type_code_facturx, $this->types_code_facturx_avoir) ? 'avoir_achat' : 'facture_achat';
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'poser_statut';
        $liste_options[] = 'zoom';
        $liste_options[] = 'rapprocher_facture_achat';

        return $liste_options;
    }

    public function trouver_candidats_facture_achat() {

        $type_element = $this->type_element_rapprochement();
        $id_rapport = 'rapprochement_'.$type_element;

        $liste_libre = Liste_libre::where('type_element', $type_element)->where('id_rapport', $id_rapport)->first();

        if(empty($liste_libre))
            exception(traduction('messages.php.rapport_detail_ligne_inexistant'));

        $fournisseur_id = null;

        if(!empty($this->modele->siren_emetteur))
            $fournisseur_id = modele('fournisseur')->where('siren', $this->modele->siren_emetteur)->value('id');

        $filtres_pour_fiche = array(
            'valide' => 1,
            'facturation_electronique_achat_id' => null,
            'entite_id' => $this->modele->entite_id,
        );

        if(!empty($this->modele->montant_ttc))
            $filtres_pour_fiche['montant_document_ttc'] = ['montant' => round($this->modele->montant_ttc, 2)];

        if(!empty($fournisseur_id))
            $filtres_pour_fiche['fournisseur_id'] = $fournisseur_id;

        $liste_management = liste($type_element, $id_rapport);

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id, [
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => 20,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

        $donnees_liste['options'] = view('eden::listes.includes.options', [
            'options' => management($type_element)->colonne_options($liste_libre),
            'type_element_options' => $type_element,
            'type_element' => $type_element,
            'mobile' => false,
        ])->render();

        $vue_render = view('eden::listes.includes.details_ligne_document', array(
            'management' => $this,
            'id_liste' => $liste_libre->id,
            'type_element' => $type_element,
            'id_element' => $this->modele->id,
            'liste_libre' => $liste_libre,
            'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => 0,
        ))->render();

        return response()->json(['retour' => true, 'composant' => $vue_render]);
    }

    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        foreach(['facture_achat', 'avoir_achat'] as $type_element_lie) {

            $nom_sql = $type_element_lie.'_id';

            if(!array_key_exists($nom_sql, $modifications))
                continue;

            if(!empty($modele_avant->{$nom_sql}) && $modele_avant->{$nom_sql} != $modele->{$nom_sql}) {

                $ancien_document = management($type_element_lie, $modele_avant->{$nom_sql});

                if($ancien_document->modele->facturation_electronique_achat_id == $modele->id)
                    $ancien_document->enregistre(['facturation_electronique_achat_id' => null]);
            }

            if(!empty($modele->{$nom_sql})) {

                $document = management($type_element_lie, $modele->{$nom_sql});

                if($document->modele->facturation_electronique_achat_id != $modele->id)
                    $document->enregistre(['facturation_electronique_achat_id' => $modele->id]);
            }
        }

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function recuperer_details_ligne_pour_liste($id_element, $type_element, $id_liste_parent) {

        $liste_libre = Liste_libre::where('type_element', 'facturation_electronique_cycle_de_vie')->where('id_rapport', 'detail_ligne_facturation_electronique_achat')->first();

        if(empty($liste_libre))
            exception(traduction('messages.php.rapport_detail_ligne_inexistant'));

        $management = management($type_element, $id_element);

        $vue = "eden::listes.includes.details_ligne_document";

        $liste_management = liste('facturation_electronique_cycle_de_vie', 'detail_ligne_facturation_electronique_achat');

        $nombres_de_lignes = modele('facturation_electronique_cycle_de_vie')->where('facturation_electronique_achat_id', $id_element)->count();

        $filtres_pour_fiche = array(
            'facturation_electronique_achat_id' => $id_element,
        );

        $donnees_liste = $liste_management->recupere_liste($liste_libre->id, [
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'nombre_par_pages' => $nombres_de_lignes,
            'id_liste_parent' => $id_liste_parent,
        ]);

        $donnees_liste['lignes_selectionnees'] = array();
        $donnees_liste['desactiver_checkbox'] = true;

        $vue_render = view($vue, array(
            'management' => $management,
            'id_liste' => $liste_libre->id,
            'type_element' => 'facturation_electronique_cycle_de_vie',
            'id_element' => $id_element,
            'liste_libre' => $liste_libre,
            'donnees_liste' => $donnees_liste,
            'filtres_pour_fiche' => $filtres_pour_fiche,
            'id_liste_parent' => $id_liste_parent,
        ))->render();

        return array('composant' => $vue_render);
    }
}
