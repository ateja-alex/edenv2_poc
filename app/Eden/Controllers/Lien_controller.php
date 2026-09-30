<?php

namespace App\Eden\Controllers;

use App\Http\Controllers\Controller;

class Lien_controller extends Controller {

    /**
     *
     * Retrouve le lien d'origine et redirige vers celui-ci
     *
     */
    public function redirection($chaine_raccourcie){

        $lien_redirection = modele('url_raccourcie')->where('chaine_raccourcie', $chaine_raccourcie)->first();

        if(empty($lien_redirection))
            $lien_redirection = url('/');
        else
            $lien_redirection = $lien_redirection->lien_origine;

        return redirect($lien_redirection);
    }
}
