<?php

namespace App\Eden\Managements;

use App\Eden\Librairies\czproject\git\src\GitRepository;

class Git_management {
	
	/**
	 * 
	 * Permet de add un fichier
	 * 
	 */
	public function add($fichier) {
		
		$repository = new GitRepository('./');

		// add
		$repository->addFile($fichier);
	}

	/**
	 * 
	 * Permet de commit des changements
	 * 
	 */
	public function commit($message) {
	
		$repository = new GitRepository('./');

		// commit
		$repository->commit($message); 
	}

	/**
	 * 
	 *  Retourne la liste des répertoires où chercher les branches
	 * 
	 */
	public static function retourne_branches() {
		$branches = [
					'Git Eden' => 'app/Eden',
					'Git Spé' => false,
					'Git Public Eden' => 'public/eden'
				];
		foreach($branches as $index => $rep) {
			$branches[$index] = Git_management::retourne_branche_courante($rep);
		}

		return $branches;
	}

	/**
	 * 
	 *  Retourne le nom de la branche courante d'un répertoire
	 * 
	 */
	public static function retourne_branche_courante($module = false) {

		// Si on est dans un module, on change de répertoire
		if($module) {

			$ancienRepertoire = getcwd();
			chdir(base_path().'/'.$module);
		}

		// Exécuter la commande Git pour obtenir le nom de la branche
		$branche = exec('git rev-parse --abbrev-ref HEAD');

		if($module) {
			// Revenir au répertoire courant
			chdir($ancienRepertoire);
		}

		return $branche;
	}


	/**
	 * 
	 * Non utilisé. Je garde dans le cas d'une évolution
	 * 
	 */
	public static function retourne_commits_en_retard($module = false) {
		
		// Si on est dans un module, on change de répertoire
		if($module) {

			$ancienRepertoire = getcwd();
			chdir(base_path().'/'.$module);
		}

		// Exécuter la commande Git pour obtenir le nom de la branche
		$branche = exec('git rev-parse --abbrev-ref HEAD');
		$diff = exec('git rev-list --count');

		if($module) {
			// Revenir au répertoire courant
			chdir($ancienRepertoire);
		}

		return $diff;
	}
    
}
