<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Script_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;

class S20241216_changement_formats_champs_notifications_taches implements Script
{
    public function execute()
    {
        $nouvelles_informations = [
            'format_champ' => 'toggle'
        ];

        $champs = Champ_libre::where('type_element', 'tache')
            ->whereIn('nom_sql', ['notification_email_active', 'notification_visuelle_active'])
            ->get();

        Script_management::liste_formatee_a_autre_type_champ($nouvelles_informations,$champs);

        return true;
    }
}