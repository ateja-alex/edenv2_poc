<?php

namespace App\Eden\Managements\Elements;

class Conditionnement_management extends Element_management {

    /**
     *
     * Affiche l'élément en fonction de la chaine d'affichage
     *
     * Permet d'afficher l'élément courant (le modèle lié au management) en fonction de la chaine d'affichage
     *
     * @return string le texte à afficher par exemple "Frédéric Bry" pour un client
     *
     */
    public function affiche($modele_article = false) {

        if($modele_article !== false && $modele_article->id != $this->modele->article_id)
            $modele_article = false;

        return $this->champ('nom')->affiche() . " (" . $this->champ('quantite')->affiche() . " " . management('article', $this->modele->article_id, $modele_article)->champ('unite')->affiche() . ")";

    }

    public function retourne_quantite_unite($modele){

        $article = modele('article')->where('id', $modele->article_id)->first();
        
        $management_conditionnement = management('conditionnement', $modele->id, $modele);
        $affichage_quantite = $management_conditionnement->champ('quantite')->affiche();

        if(!empty($article->unite))
            $unite = modele('article_unite')->where('id', $article->unite)->first();

        return '<span>' . $affichage_quantite . (!empty($unite) ? ' ' . $unite->nom : '') . '</span>';

    }

}