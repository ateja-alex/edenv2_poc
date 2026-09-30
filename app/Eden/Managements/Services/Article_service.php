<?php

namespace App\Eden\Managements\Services;


class Article_service {

    /**
     * 
     * Retourne une chaine de caractères pour créer du vuejs sur le tableau de saisie des articles
     * Le but de cette méthode est de pouvoir être surchargée pour faire du spé pour afficher autre chose que le code article
     * 
     */
    public function retourne_chaine_affichage_info_article_sur_saisie_document() {

        return '{{ article_sur_document.code_article }}';
    }
}