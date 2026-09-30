<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Cache_management;
use DB;

class Article_evolution_prix_management extends Element_management {

    /**
     *
     * @cf description sur Element_management
     *
     * On traite le cas particulier des coefficients
     *
     */
    public function enregistre($modifications = array(), $modele = false) {
        if(!empty($modifications['choix_prix']))
            $type_evolution = $modifications['choix_prix'];
        else
            $type_evolution = 'euros';

        if(!empty($modifications['valeur_evolution']))
            $valeur_evolution = $modifications['valeur_evolution'];
        else
            $valeur_evolution = 0;

        if(isset($this->champ_liaison_creation_sous_formulaire) && 'article_id' == $this->champ_liaison_creation_sous_formulaire)
            return true;

        if(!empty($modifications['article_id'])){

            if(!empty($modifications['nouveau_prix']))
                $nouveau_prix = $modifications['nouveau_prix'];
            else
                return traduction('messages.php.article_evolution_prix.nouveau_prix_obligatoire', null, [traduction('formulaire.choix_prix_euros_ou_pourcents.nouveau_prix')]);

            //On récupère le modèle
            $article = modele('article', $modifications['article_id']);

            $modifications['prix_vente'] = $this->calcule_prix_vente($nouveau_prix);

            $this->verification_evolution_future($modifications, $article);
        }
        else if(!empty($modifications['fournisseur_id'])){

            //On récupère les articles vendus par le fournisseur
            $articles = $this->recupere_article_fournisseur($modifications['fournisseur_id']);

            if($articles->isEmpty())
                return "Il n'y a pas d'articles pour ce fournisseur";

            foreach ($articles as $article){

                //On réinitialise le modele du management sinon on modifie le même élément
                $this->modele = null;

                //On récupère le modèle de l'article
                $modele_article = modele('article', $article->article_id);

                //On indique l'id de l'article pour l'enregistrement
                $modifications['article_id'] = $modele_article->id;

                if(in_array($modele_article->type_article, array(1,3)) && !empty($modele_article->tarif_force))
                    $tarif = $modele_article->tarif_force;
                else
                    $tarif = $modele_article->tarif;

                //On applique l'évolution en pourcents ou en euros
                if($type_evolution == 'pourcents')
                    $modifications['prix_vente'] = $this->calcule_evolution_pourcentage($modifications, $tarif, $valeur_evolution);

                else

                    $modifications['prix_vente'] = $this->calcule_evolution_prix($modifications, $tarif, $valeur_evolution);

                $this->verification_evolution_future($modifications, $modele_article);
            }
        }
        else if(!empty($modifications['famille_id'])){

            $familles_id = [$modifications['famille_id']];

            //On récupère le modèle des articles
            $familles_filles = $this->recupere_article_fournisseur_famille($familles_id);

            if(!empty($familles_filles))
                $familles_id = array_merge($familles_filles, $familles_id);

            $articles = $this->recupere_article_famille($familles_id);

            if($articles->isEmpty())
                return "Il n'y a pas d'articles dans cette famille";

            foreach ($articles as $article){

                //On réinitialise le modele du management sinon on modifie le même élément
                $this->modele = null;

                //On indique l'id de l'article pour l'enregistrement
                $modifications['article_id'] = $article->id;

                if(in_array($article->type_article, array(1,3)) && !empty($article->tarif_force))
                    $tarif = $article->tarif_force;
                else
                    $tarif = $article->tarif;

                //On applique l'évolution en pourcents ou en euros
                if(!empty($modifications['nouveau_prix']))
                    $modifications['prix_vente'] = $modifications['nouveau_prix'];

                else if($type_evolution == 'pourcents')
                    $modifications['prix_vente'] = $this->calcule_evolution_pourcentage($modifications, $tarif, $valeur_evolution);

                else
                    $modifications['prix_vente'] = $this->calcule_evolution_prix($modifications, $tarif, $valeur_evolution);

                $this->verification_evolution_future($modifications, $article);
            }
        } else  {

            //On récupère les articles vendus par le fournisseur
            $articles = $this->recupere_article();

            foreach ($articles as $article){

                //On réinitialise le modele du management sinon on modifie le même élément
                $this->modele = null;

                //On indique l'id de l'article pour l'enregistrement
                $modifications['article_id'] = $article->id;

                if(in_array($article->type_article, array(1,3)) && !empty($article->tarif_force))
                    $tarif = $article->tarif_force;
                else
                    $tarif = $article->tarif;

                //On applique l'évolution en pourcents ou en euros
                if($type_evolution == 'pourcents')
                    $modifications['prix_vente'] = $this->calcule_evolution_pourcentage($modifications, $tarif, $valeur_evolution);

                else
                    $modifications['prix_vente'] = $this->calcule_evolution_prix($modifications, $tarif, $valeur_evolution);

                $this->verification_evolution_future($modifications, $article);
            }
        }

        return true;
    }

    /**
     *
     * @cf description sur Element_management
     *
     * on vérifie si on ne doit pas appliquer directement l'évolution de prix
     *
     */
    protected function methodes_post_modification($modele, $modele_avant, $modifications) {

        $this->comparer_prix_ventes_aux_evolutions($modele->article_id);

        parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    // Permet de surcharger la regle d'arrondi
    public function calcule_evolution_pourcentage($modifications, $tarif, $valeur_evolution){

        $prix_vente = $modifications['prix_vente'];

        $prix_vente = round($tarif * (1+$valeur_evolution/100), 2);

        return $prix_vente;
    }

    // Permet de surcharger la regle d'arrondi
    public function calcule_evolution_prix($modifications, $tarif, $valeur_evolution){
        $prix_vente = $tarif + $valeur_evolution;

        return $prix_vente;
    }

    public function verification_evolution_future($modifications, $article){

        $verification_evolution_future = modele('article_evolution_prix')->where('article_id', $article->id)->where('date_application', '>', date('Y-m-d'))->first();

        if(!empty($verification_evolution_future) && $modifications['date_application'] > date('Y-m-d'))
            $modele = $verification_evolution_future;
        else
            $modele = false;

        parent::enregistre($modifications, $modele);
    }

    // Permet de surcharger le calcul de prix de vente ( pour un arrondi notamment )
    public function calcule_prix_vente($nouveau_prix){

        return $nouveau_prix;
    }

    /*
     *
     * Applique les évolutions de prix aux articles
     *
     */
    public function comparer_prix_ventes_aux_evolutions($id = false){

        if($id === false) {

            $articles = modele('article')->get();

            if(defined('methode_appelee') && methode_appelee == 'comparer_prix_ventes_aux_evolutions')
                echo 'Traitement en cours ... <br>';
        }
        else
            $articles = modele('article')->where('id', $id)->get();


        foreach($articles as $article){

            $date_du_jour = date('Y-m-d');
            $evolution_prix = modele('article_evolution_prix')->where('article_id', $article->id)->where('date_application', '<=',$date_du_jour)->orderBy('date_application', 'desc')->first();

            if(empty($evolution_prix))
                continue;

            if($article->tarif != $evolution_prix->prix_vente && (empty($article->type_article) || in_array($article->type_article, array(0,2)))){

                management('article', $article->id, $article)->enregistre(['tarif' => $evolution_prix->prix_vente]);

                if($id === false && defined('methode_appelee') && methode_appelee == 'comparer_prix_ventes_aux_evolutions')
                    echo 'Le nouveau tarif a été appliqué pour l\'article avec l\'id ' . $article->id . '<br>';
            }

            if($article->tarif_force != $evolution_prix->prix_vente && in_array($article->type_article, array(1,3))){

                management('article', $article->id, $article)->enregistre(['tarif_force' => $evolution_prix->prix_vente]);

                if($id === false && defined('methode_appelee') && methode_appelee == 'comparer_prix_ventes_aux_evolutions')
                    echo 'Le nouveau tarif a été appliqué pour l\'article avec l\'id ' . $article->id . '<br>';
            }
        }

        if($id === false && defined('methode_appelee') && methode_appelee == 'comparer_prix_ventes_aux_evolutions')
            return 'Tous les articles ont été traités';
    }

    /**
     *
     * Retourne les articles fournisseurs filtrés
     *
     */
    public function recupere_article_fournisseur($fournisseur_id){
        return modele('article_fournisseur')->where('fournisseur_id', $fournisseur_id)->get();
    }

    /**
     *
     * Retourne les familles filtrés
     *
     */
    public function recupere_article_fournisseur_famille($famille_id){
        return modele('famille')->whereIn('parent_id', $famille_id)->get()->pluck('id')->all();
    }

    /**
     *
     * Retourne les articles filtrés
     *
     */
    public function recupere_article(){
        return modele('article')->get();
    }

    /**
     *
     * Retourne les articles appartenant aux familles
     *
     */
    public function recupere_article_famille($familles_id) {
        return modele('article')->whereIn('famille_id', $familles_id)->get();
    }
}
