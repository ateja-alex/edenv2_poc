<?php

namespace App\Eden\Managements;

use App\Eden\Models\Traduction;
use App\Eden\Models\Champ_libre;

class Traduction_management {

    /**
     * 
     * On récupère la liste des utilisateurs et on retraite certaines informations
     * Comme les entités par défaut, etc.
     * 
     */
    public static function traduit($modele, $type_element, $element_id, $langue) {
		
		$champs_libres = Champ_libre::where('type_element', $type_element)->where('multilingue', 1)->get();
		
		foreach($champs_libres as $champ_libre) {
			
			$traduction = Traduction::where('type_element', $type_element)->where('nom_sql', $champ_libre->nom_sql)->where('element_id', $element_id)->where('langue_id', $langue)->first();
			
			if($traduction !== null) {
				
				$modele->{$champ_libre->nom_sql} = $traduction->traduction;
			}
		}
		
		
		return $modele;
    }
	
	public static function genere_fichier_traduction_interface($chemin_fichier_traduction, $nom_dans_formulaire, $formulaire) {
		
		// Si on est en train de faire les migrations, on ne regénère pas les fichiers
		if (defined('migration_en_cours'))
			return true;
		
		if(file_exists($chemin_fichier_traduction))
			$traductions = include($chemin_fichier_traduction);
		else
			$traductions = [];
		
		if(!is_array($traductions))
			$traductions = [];
		
		if(!empty($formulaire[$nom_dans_formulaire]))
			$traductions[$formulaire['index']] = $formulaire[$nom_dans_formulaire];
		else {
			
			if(isset($traductions[$formulaire['index']]))
			 unset($traductions[$formulaire['index']]);
		}
		
		// on recrée le fichier
		$contenu_fichier = "<?php\n\nreturn [\n";

		foreach($traductions as $index => $traduction) {
			
			
			$contenu_fichier .= "\t'$index' => \"$traduction\",\n";
		}
		
		$contenu_fichier .= "];";
		
		// on stocke dans un fichier
		
		// Si le fichier existe, on le supprime
	    // if(file_exists($chemin_fichier_traduction) == true)
	    	// unlink($chemin_fichier_traduction);
		
		// Enregistrement du fichier
	    $fichier = fopen($chemin_fichier_traduction, "w+");
	    fputs($fichier, $contenu_fichier);
	    fclose($fichier);
	}

    
}
