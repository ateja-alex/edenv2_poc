<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Script_management;

use App\Eden\Models\Champ_libre;

class S20221229_mise_en_place_cle_utilisateur implements Script
{

    public function execute()
    {

        if(env('BASE_TRADUCTION') === true)
            return true;

        $utilisateur_par_cle = array(
            'frederic.bry@easy-developpement.fr' => '1',
            'emmanuel.bouillon@easy-developpement.fr' => '2',
            'thibaut.leroy14@gmail.com' => '3',
            'lucas.claudel@easy-developpement.fr' => '4',
            'tom.dimster@easy-developpement.fr' => '5',
            'mathis.deplanque@easy-developpement.fr' => '6',
            'guillaume.leti@easy-developpement.fr' => '7',
            'francois.nicollet@easy-developpement.fr' => '8',
            'gpruvost@ateja.fr' => '9',
            'cron@easy-developpement.fr' => '10',
            'yammarkhodja@ateja.fr' => '11',
            'plima@ateja.fr' => '12',
            'swepierre@ateja.fr' => '13',
            'alamart@ateja.fr' => '14'
        );

        $utilisateur_par_mail = modele('utilisateur')->get()->keyBy('email');

        foreach($utilisateur_par_cle as $email => $cle){

            if(isset($utilisateur_par_mail[$email])){

                $utilisateur_par_mail[$email]->cle_externe = $cle;

                $utilisateur_par_mail[$email]->save();
            }
        }

        return true;

    }
}
