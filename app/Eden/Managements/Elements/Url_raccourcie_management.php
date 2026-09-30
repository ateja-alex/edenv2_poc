<?php

namespace App\Eden\Managements\Elements;

use Illuminate\Support\Str;

class Url_raccourcie_management extends Element_management {

    /**
     *
     * Permet de créer un lien raccourci pour le lien passé en paramètre
     *
     */
    public function enregistre($modifications = array(), $modele = false) {

        if(!isset($modifications['lien_origine']))
            return traduction('messages.php.champ_obligatoire') . traduction($this->champ('lien_origine')->modele->index_traduction. '.nom');

        $verification_lien_origine = modele('url_raccourcie')->where('lien_origine', $modifications['lien_origine'])->count();

        if($verification_lien_origine >= 1)
            return traduction('messages.php.url_raccourcies.lien_existant');

        $chaine_raccourcie = Str::random(5);

        $chaines_raccourcies_existantes = modele('url_raccourcie')->get()->pluck('lien_origine', 'chaine_raccourcie')->toArray();

        while(array_key_exists($chaine_raccourcie, $chaines_raccourcies_existantes)){

            $chaine_raccourcie = Str::random(5);
        }

        $modifications['chaine_raccourcie'] = $chaine_raccourcie;

        $retour = parent::enregistre($modifications, $modele);

        return $retour;
    }

    public function retourne_lien_raccourci(){

        return url('/lr/' . $this->modele->chaine_raccourcie);
    }
}