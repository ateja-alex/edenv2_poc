<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20250818_suppression_migration_spe_utilisateur_log_page implements Script {

    public function execute() {

        $chemin_fichier = app_path("Migrations/Rapports/utilisateur_log_page.php");
        
        if(file_exists($chemin_fichier))
			unlink($chemin_fichier);

		return true;
    }
}
