<?php

namespace App\Eden\Managements;

/**
 * 
 * Management des familles, mais pas considéré comme un élément (non lié à une famille en particulier)
 * Permet de gérer le cache des familles par exemple
 * 
 */
class Familles_management {
	
	/**
	 * 
	 * Supprime les fichiers de cache des familles
	 * 
	 */
	public static function supprime_cache() {
		
		if(file_exists(storage_path('app/eden_familles.php')))
			unlink(storage_path('app/eden_familles.php'));
			
		// on le regénère à la volée, comme ça si jamais le client ne va pas sur une autre page Eden, il sera regénéré automatiquement quand même
		self::genere_cache();
		
		return true;
	}
	
	/**
	 * 
	 * Génère le cache des familles
	 * 
	 */
	public static function genere_cache() {
		
		// @note Frédéric 13/10/2021 : cette méthode est appelée dans un midleware
		// et elle prend du temps à s'éxécuter
		// du coup si le fichier existe, on ne la lance pas
		// elle sera lancée que si le fichier n'existe pas
		if(file_exists(storage_path('app/eden_familles.php'))) {
			
			return;
			// @unlink(storage_path('app/eden_familles.php'));
		}

		$contenu_fichier = "<?php\n\n";
		$contenu_fichier .= "return [\n\n";
		
		// la liste des sous familles sans la famille parent
		$contenu_fichier .= "\t'liste_sous_familles' => [\n\n";
		
		// entités
		$familles = self::genere_sous_familles_par_famille();
		
		foreach($familles as $id_famille => $sous_familles) {
			
			$contenu_fichier .= "\t\t$id_famille => [";
			
			foreach($sous_familles as $id_sous_famille) {
				
				$contenu_fichier .= "$id_sous_famille,";
			}
			
			$contenu_fichier .= "],\n";
		}
		
		$contenu_fichier .= "\t],";
		
		// la liste des sous familles avec la famille parent
		$contenu_fichier .= "\n\t'liste_sous_familles_avec_famille_parent' => [\n\n";
		
		$familles = self::genere_sous_familles_par_famille();
		
		foreach($familles as $id_famille => $sous_familles) {
			
			$contenu_fichier .= "\t\t$id_famille => [";
			
			$contenu_fichier .= "$id_famille,";
			
			foreach($sous_familles as $id_sous_famille) {
				
				$contenu_fichier .= "$id_sous_famille,";
			}
			
			$contenu_fichier .= "],\n";
		}
		
		$contenu_fichier .= "\t],";
		
		// les familles parents pour chaque famille
		$contenu_fichier .= self::genere_liste_familles_parents();
		
		
		// arborescence
		$contenu_fichier .= self::genere_arborescence_familles();
		
		// fin du fichier
		$contenu_fichier .= "\n];";
		
		// on stocke dans un fichier
		\Storage::put('eden_familles.php', $contenu_fichier);

	}
	
	/**
	 * 
	 * Génère l'arborescence de la liste des familles
	 * 
	 */
	public static function genere_arborescence_familles() {
		
		$contenu_fichier = "\n\t'arborescence' => [\n\n";
		
		$contenu_fichier .= self::genere_arborescence_familles_recursion();
		
		$contenu_fichier .= "\t],";
		
		return $contenu_fichier;
	}
	
	/**
	 * 
	 * Génère l'arborescence de la liste des familles
	 * 
	 */
	public static function genere_arborescence_familles_recursion($contenu_fichier = '', $parent_id = false) {
		
		if($parent_id !== false)
			$familles = modele('famille')->sans_profils()->where('parent_id', $parent_id)->get();
		else
			$familles = modele('famille')->sans_profils()->where(function($requete) {
				
				$requete->where('parent_id', 0);
				$requete->orWhereNull('parent_id');
			})->get();

		foreach($familles as $famille) {
			
			// on crée une ligne
			if($parent_id === false)
				$contenu_fichier .= "\t\t";
		
			$contenu_fichier .= $famille->id." => [";
			
			$contenu_fichier .= self::genere_arborescence_familles_recursion('', $famille->id);
			
			$contenu_fichier .= "],";
			
			if($parent_id === false)
				$contenu_fichier .= "\n";
				
		}
		
		return $contenu_fichier;
	}
	
	/**
	 * 
	 * Liste toutes les familles parent d'une famille
	 * 
	 */
	public static function genere_liste_familles_parents() {
		
		// on va boucler sur toutes les familles
		$familles = modele('famille')->sans_profils()->get();
			
		$contenu_fichier = "\n\t'familles_parents' => [\n\n";
		
		foreach($familles as $famille) {
			
			// on crée une ligne pour chaque famille
			$contenu_fichier .= "\t\t".$famille->id." => [";
			
			$contenu_fichier .= implode(',', self::recupere_familles_parent($famille));
			
			$contenu_fichier .= "],\n";
		}
		
		$contenu_fichier .= "\t],";
		
		return $contenu_fichier;
	}
	
	public static function recupere_familles_parent($famille, $familles_parent = array()) {
		
		if(empty($famille->parent_id))
			return $familles_parent;
		
		// ok y'a une famille parent
		$famille_parent = modele('famille')->sans_profils()->where('id', $famille->parent_id)->first();
		
		if($famille_parent === null) {
			
			return $familles_parent;
		}
		
		$familles_parent[] = $famille_parent->id;
		
		$familles_parent = self::recupere_familles_parent($famille_parent, $familles_parent);
		
		return $familles_parent;
	}
	
	/**
	 * 
	 * Liste toutes les sous familles d'une famille
	 * 
	 */
	public static function genere_sous_familles_par_famille() {
		
		// on va faire un tableau pour chaque famille
		$familles = modele('famille')->sans_profils()->get();
		
		$sous_familles = array();
		
		// pour chaque famille on va chercher les sous familles récursivement
		foreach($familles as $famille) {
			
			$sous_familles[$famille->id] = self::recupere_sous_familles($famille->id);
		}
		
		return $sous_familles;
	}
	
	public static function recupere_sous_familles($parent_id, $sous_familles = array()) {
		
		$familles = modele('famille')->sans_profils()->where('parent_id', $parent_id)->get();
		
		foreach($familles as $famille) {
			
			$sous_familles[] = $famille->id;
			
			$sous_familles = self::recupere_sous_familles($famille->id, $sous_familles);
		}
		
		return $sous_familles;
	}

    
}
