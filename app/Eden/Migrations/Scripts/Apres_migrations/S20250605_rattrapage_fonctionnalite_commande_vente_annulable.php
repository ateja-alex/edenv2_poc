<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Librairies\Budgea\Exception;
use App\Eden\Managements\Parametrage\Liste_libre_management;
use App\Eden\Managements\Script_management;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Liste_libre;
use App\Eden\Models\Liste_libre_couleur;
use App\Eden\Models\Rapport_libre;
use Illuminate\Support\Facades\DB;
use App\Eden\Migrations\Scripts\Script;

class S20250605_rattrapage_fonctionnalite_commande_vente_annulable implements Script
{
    public function execute(){

        if(fonctionnalite('gescom_commande_vente_annulable_non_supprimable') === true)
            Script_management::modifier_fonctionnalites(['gescom_commande_vente_annulable_non_supprimable' => 'annulable_non_supprimable']);
    
        return true;
    }
}