<?php

namespace App\Eden\Managements\Elements;

class Composition_article_management extends Element_management {

    /**
     *
     * Gestion avant l'enregistrement pour vérifier certaines valeurs
     *
     */
    public function enregistre($modifications = array(), $modele = false){

        $article_id = !empty($modifications['article_id']) ? $modifications['article_id'] : (!empty($this->modele->article_id) ? $this->modele->article_id : null);
        $article_enfant_id = !empty($modifications['article_enfant_id']) ? $modifications['article_enfant_id'] : (!empty($this->modele->article_enfant_id) ? $this->modele->article_enfant_id : null);

        if(!empty($article_id) && !empty($article_enfant_id)) {

            if($article_enfant_id == $article_id)
			    return traduction('messages.php.fiche_article.composition_ajout_impossible.ajout_article_parent');

            $modele_article = modele('article', $article_id);
            $modele_article_enfant = modele('article', $article_enfant_id);

            if ($modele_article_enfant->type_article == 1 && $modele_article->type_article == 3)
                return traduction('messages.php.fiche_article.composition_ajout_impossible.nomenclature_produit_assemble');

            $verification = modele('composition_article')
                ->where('article_id', $article_enfant_id)
                ->where('article_enfant_id', $article_id)
                ->get()
                ->toArray();

            if (!empty($verification))
                return traduction('messages.php.fiche_article.composition_ajout_impossible.ajout_article_parent_article_enfant');

        }

        $tarif = !empty($modifications['tarif']) ? $modifications['tarif'] : (!empty($this->modele->tarif) ? $this->modele->tarif : null);;

        if(empty($tarif) && $tarif != 0 && !empty($modifications['conditionnement'])) {

            $conditionnement = modele('conditionnement')->where('id', $modifications['conditionnement'])->first();

            if(!empty($conditionnement))
                $modifications['tarif'] = $conditionnement->tarif;
        }

        return parent::enregistre($modifications, $modele);
    }

    /**
     *
     * On doit faire un traitement sur l'article parent pour gérer les prix
     *
     */
    public function methodes_post_modification($modele, $modele_avant, $modifications){

        $management_article = management('article', $this->modele->article_id);

        $management_article->calcule_prix_assemblage_ou_nomenclature();
        
        $management_article->calcul_poids_assemblage_ou_nomenclature();
        
        $management_article->mise_a_jour_prix_parents();

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    /**
     *
     * On doit faire un traitement sur l'article parent pour gérer les prix
     *
     */
    public function methodes_post_suppression($modele){

        $management_article = management('article', $modele->article_id);

        $management_article->calcule_prix_assemblage_ou_nomenclature();

        $management_article->calcul_poids_assemblage_ou_nomenclature();
        
        parent::methodes_post_suppression($modele);
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

        $actions['copier_composition_article'] = '<span class="dropdown-item" v-if="$root.type_element == \'article\'" @click="modale_copie_composition = true"><i class="fa fa-fw fa-copy"></i><span v-html="$root.traduction(\'interface.listes.copie_composition\')"></span></span>';

        return $actions;
    }
}