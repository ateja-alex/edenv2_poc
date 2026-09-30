<?php

namespace App\Eden\Managements\Elements;

class Article_fournisseur_management extends Element_management {

    /**
     *
     * @cf description sur Element_management
     *
     * On traite le cas particulier des coefficients
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(fonctionnalite('bloquer_doublon_reference_article_fournisseur') == true && !empty($modifications['reference'])){

            $verification_existance_reference = modele('article_fournisseur');

                $fournisseur_id = null;

                if(!empty($modifications['fournisseur_id']))
                    $fournisseur_id = $modifications['fournisseur_id'];

                if($this->existe()) {
                    $verification_existance_reference = $verification_existance_reference->where('id', '!=', $this->modele->id);
                    $fournisseur_id = $this->modele->fournisseur_id;
                }

                if(empty($fournisseur_id))
                    return traduction('messages.php.fiche_article.fournisseur.article_reference_existante');

            $verification_existance_reference = $verification_existance_reference->where('reference',$modifications['reference'])->where('fournisseur_id',$fournisseur_id)->first();

            if(!empty($verification_existance_reference))
                return traduction('messages.php.fiche_article.fournisseur.article_reference_existante');
        }

        if(isset($this->champ_liaison_creation_sous_formulaire) && 'article_id' == $this->champ_liaison_creation_sous_formulaire)
            return true;


        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true)
            return $retour;

        return true ;
    }

	/**
	 *
	 * On renvoie le stock de l'article lié
	 *
	 */
	public function stock_actuel_article($modele){

	    $stock = 0;

	    if($modele->article_id){

	        $stock = management('article',$modele->article_id)->stock_actuel();
        }

	    return $stock;
    }

    /**
     *
     * On renvoie les stocks previssionels en cours de l'article
     *
     */
    public function stock_previsionnel_article($modele){

        $stock = 0;

        if($modele->article_id){

            $modele_stock_initial = modele('stock_initial')->where('article_id', $modele->article_id)->first();

            $stock_initial = 0;

            if($modele_stock_initial && $modele_stock_initial->stock_initial > 0){

                $stock_initial =  $modele_stock_initial->stock_initial;
            }

            $management_article = management('article',$modele->article_id);

            $stock = $management_article->stock_previsionnel($management_article->modele,$stock_initial);
        }

        return $stock;
    }

    /**
     *
     * On renvoie les quantites de mouvement de l'article
     *
     */
    public function quantite_de_mouvement_article($modele){

        $quantite = 0;

        if($modele->article_id){

            $vitesse_rotation = management('article',$modele->article_id)->retourne_vitesse_rotation();

            $quantite = array_shift($vitesse_rotation);
        }

        return $quantite;
    }

    /**
     *
     * Permet de charger les données pour l'impression de la fiche fournisseur
     *
     */
    public function charge_donnees_pour_pdf_pour_fiche_fournisseur($id_element_fiche){

        // On récupère les articles du fournisseur
        $articles_fournisseur = modele('article_fournisseur')->where('fournisseur_id',$id_element_fiche)->get();

        $infos_article_fournisseur = array();

        // Le fournisseur possède des articles
        if ($articles_fournisseur->isEmpty())
            return array();

        $tarifs_par_article = array();

        // On stock les id pour le WhereIn
        foreach ($articles_fournisseur as $article_fournisseur) {

            $array_id_article[] = $article_fournisseur->article_id;

            $infos_article_fournisseur[$article_fournisseur->article_id] = $article_fournisseur;
        }

        // On prend les articles du fournisseur
        $elements = modele('article')->whereIn('id', $array_id_article)->orderBy('famille_id')->orderBy('designation')->get();

        foreach($elements as $element) {

            $element->infos_article_fournisseur = $infos_article_fournisseur[$element->id];
        }

        return $elements;
    }

    /**
     *
     *
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
        //On modifie le prix d'achat de l'article
        if($this->modele->fournisseur_prioritaire == 1){

            $verification_autres_fournisseurs = modele('article_fournisseur')
                ->where('article_id', $this->modele->article_id)
                ->where('fournisseur_prioritaire', 1)
                ->where('id', '!=', $this->modele->id);

            if(!empty($this->modele->conditionnement_id))
                $verification_autres_fournisseurs = $verification_autres_fournisseurs->where('conditionnement_id', $this->modele->conditionnement_id);
            else
                $verification_autres_fournisseurs = $verification_autres_fournisseurs->zero_ou_null('conditionnement_id');

            $verification_autres_fournisseurs = $verification_autres_fournisseurs->get();

            foreach($verification_autres_fournisseurs as $fournisseur){

                management('article_fournisseur', $fournisseur->id)->enregistre_modele(['fournisseur_prioritaire' => 0]);
            }

        }

        //On met à jour l'index de recherche de l'article
        if(!empty($this->modele->article_id) && (isset($modele_avant['reference']) || isset($this->modele->reference)) &&

            (
                (isset($modele_avant['reference']) && !isset($this->modele->reference)) ||

                (isset($this->modele->reference) && !isset($modele_avant['reference'])) ||

                $modele_avant['reference'] != $this->modele->reference
            )
        ){
            management('article', $this->modele->article_id)->maj_index_recherche();
        }
    }


    /**
     *
     *
     * Retourne les actions sur les listes
     * Peut être surchargé pour ajouter des actions sur mesure en fonction des éléments ou du spécifique
     *
     */
    public function actions_a_afficher($id_liste) {

        // on recupère les actions principales
        $actions = parent::actions_a_afficher($id_liste);

        $actions['creer_conditionnement'] = '<span class="dropdown-item" @click="modale_creer_conditionnement = true"><i class="fa fa-fw fa-check"></i><span v-html="$root.traduction(\'interface.listes.creer_conditionnement\')"></span></span>';

        return $actions;
    }

    /**
     *
     * Crée les details d'une ligne
     *
     */
    public function recuperer_details_ligne_pour_liste($id_element,$type_element,$id_liste_parent) {

        // On va chercher les lignes qui nous interessent
        $conditions_commerciales = modele('condition_commerciale')->where('article_fournisseur_id', $id_element)->orderBy('modifie_le','desc')->get();

        // On appelle la vue qui affiche les infos que l'on veut via un render();
        $vue = "eden::listes.includes.details_ligne_article_fournisseur";
        $vue_render = view($vue, array(

            'conditions_commerciales' => $conditions_commerciales
        ))->render();

        return $vue_render;
    }

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'zoom';

        return $liste_options;
    }

}
