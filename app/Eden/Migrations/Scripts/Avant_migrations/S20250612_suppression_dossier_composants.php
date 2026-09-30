<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;

class S20250612_suppression_dossier_composants implements Script {

    public function execute() {

        if(is_dir(storage_path('/app/public/composants')))
			$this->supprime_fichiers_dossier(storage_path('/app/public/composants'));
		if(is_dir(storage_path('/app/public/menus')))
			$this->supprime_fichiers_dossier(storage_path('/app/public/menus'));
		if(is_dir(storage_path('/app/public/css')))
			$this->supprime_fichiers_dossier(storage_path('/app/public/css'));

		return true;
    }

	private function supprime_fichiers_dossier($dossier) {

		$fichiers = array_diff(scandir($dossier), array('.','..'));

		foreach($fichiers as $fichier) {
			(is_dir("$dossier/$fichier")) ? $this->supprime_fichiers_dossier("$dossier/$fichier") : unlink("$dossier/$fichier");
		}

		return rmdir($dossier);

	}
}
