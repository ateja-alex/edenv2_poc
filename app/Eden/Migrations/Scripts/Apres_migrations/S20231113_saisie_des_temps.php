<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;

class S20231113_saisie_des_temps implements Script{

    public function execute(){

        $tables_libres = Table_libre::whereIn('type_element',['feuille_de_temps','activite'])
            ->get()->pluck('id','type_element');

        management('trigger_eden')->enregistre(array(
            'type_element_id' => $tables_libres['feuille_de_temps'],
            'type_element_concerne_id' => $tables_libres['activite'],
            'requete' => "
                UPDATE activite 
                SET realise = (SELECT SUM(feuille_de_temps.duree) 
                               FROM feuille_de_temps 
                               WHERE COALESCE(inactif,0) = 0 
                                 AND feuille_de_temps.activite_id = 
                                     (SELECT activite_id 
                                      FROM feuille_de_temps ft 
                                      WHERE ft.id = #id_cible#) 
                               GROUP BY activite_id)
                WHERE activite.id = 
                      (SELECT activite_id 
                       FROM feuille_de_temps ft 
                       WHERE ft.id = #id_cible#
                       )
            ",
            'requete_elements_concernes' => "
                SELECT activite.* FROM activite JOIN feuille_de_temps 
                ON feuille_de_temps.activite_id = activite.id AND feuille_de_temps.id = #id_cible#
            ",
        ));

        return true;
    }
}