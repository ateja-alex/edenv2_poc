<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Managements\Script_management;
use DB;
use Illuminate\Support\Facades\Schema;

class S20251209_champ_element_id_entier implements Script {

    public function execute() {

        $type_element_std = ['activite','adresse','approbation','bibliotheque_synchro_elements','categorie_ligne',
        'docusign_enveloppe','echange','ecriture_comptable','feuille_de_temps','feuille_de_temps_commentaire',
        'indisponibilite','notification','ordre_dans_kanban','questionnaire_element_repondant','tableaux_libres'];

        $champs_libres = Champ_libre::where('nom_sql', 'element_id')->where('type', 0)->whereIn('type_element', $type_element_std)->get();

        $nouvelles_informations = array(
            'type' => 2,
        );

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations, $champs_libres, true);
 
        return true;
    }
}