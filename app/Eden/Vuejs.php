<?php
namespace App\Eden;

use App\Eden\Models\Table_libre;

/**
 *
 * Gère certaines choses pour vuejs, comme les filtres (cache)
 *
 */
abstract class Vuejs {
	
	/**
	 * 
	 * Génère un fichier de filtres vuejs pour afficher les éléments avec des clés étrangères (par exemple les entités)
	 * 
	 */
    public static function genere_filtres_vuejs() {
		

		

        if(file_exists(storage_path('app/public/filtres_vuejs.js')))
			return;
		
		//$contenu_fichier = "/* Généré le ".date('d/m/Y H:i:s')." */\n\n";

		// noms des tables libres
		$contenu_fichier = self::noms_types_elements();
		
		// entités
		$contenu_fichier .= self::genere_filtre('entite');
		
		// les familles d'articles
		$contenu_fichier .= self::genere_filtre('famille');
		
		// comptes bancaires
		$contenu_fichier .= self::genere_filtre('compte_bancaire');
		
		// modes de paiement
		$contenu_fichier .= self::genere_filtre('mode_paiement');
		
		// pays
		$contenu_fichier .= self::genere_filtre('pays');
		
		// pays
		$contenu_fichier .= self::genere_filtre('article_unite');
		
		
		// comptes comptables
		// $contenu_fichier .= self::genere_filtre('compte_comptable');
		
		// types d'échange
		// @todo
		
		// utilisateurs
		$contenu_fichier .= self::genere_filtre('utilisateur', false, function($element) { return $element->prenom.' '.$element->nom; });
		
		// utilisateurs avatars
		$contenu_fichier .= self::genere_filtre('utilisateur_avatar', modele('utilisateur')->get(), function($element) { 

			if($element->avatar == '') {
					return 'eden/images/no_avatar.jpg'; 
				} else {
					return 'storage/'.$element->avatar; 
				}
			}
		);
        
		// entrepots
		// $contenu_fichier .= self::genere_filtre('entrepot');
		
		// modalités de paiement
		$contenu_fichier .= self::genere_filtre('modalite_paiement');
		
		// familles de déclinaisons
		$contenu_fichier .= self::genere_filtre('famille_declinaison');
		
		// on stocke dans un fichier
		\Storage::put('public/filtres_vuejs.js', $contenu_fichier);
    }
	
	/**
	 * 
	 * Supprime le fichier des filtres (de manière à le regénéré, par exemple à la mise à jour du cache)
	 * 
	 */
	public static function supprime_fichier_filtres() {
		
		if(file_exists(storage_path('app/public/filtres_vuejs.js')))
			unlink(storage_path('app/public/filtres_vuejs.js'));
		
		return true;
	}
	
	/**
	 *
	 * Génère un filtre vue js pour une liste d'éléments (par exemple la liste des entités)
	 * 
	 */
	protected static function genere_filtre($type_element, $elements = false, $affichage = false, $cle_primaire = 'id') {
		
		if($elements === false)
			$elements = modele($type_element)->get();
		
		if($affichage === false)
			$affichage = function($element) { return $element->nom; };
		
		$contenu_fichier = "Vue.filter('affiche_".$type_element."', function (value) {\n\n";

		$contenu_fichier .= "\tif(!value) return '';\n\n";
		$contenu_fichier .= "\tvar liste = [];\n";

		foreach($elements as $element) {
			
			$contenu_fichier .= "\tliste[".$element->{$cle_primaire}."] = \"".str_replace(['"', "\n"], ['\\"', ''], $affichage($element))."\";\n";
		}
			
		$contenu_fichier .= "\n\treturn liste[value]; \n}); \n\n";
		
		return $contenu_fichier;
	}
	
	/**
	 *
	 * Génère un filtre vue js pour le nom d'un type element
	 * 
	 */
	protected static function noms_types_elements() {


		$tables_libres = Table_libre::get();

		$contenu_fichier = "Vue.filter('noms_types_elements', function (value) {\n\n";

		$contenu_fichier .= "\tif(!value) return '';\n\n";

		$contenu_fichier .= "\tvar liste = [];\n";
		
		foreach($tables_libres as $table_libre) {
			
			$contenu_fichier .= "\tliste[`".$table_libre->type_element."`] = `".$table_libre->element_pluriel."`;\n";
		}
			
		$contenu_fichier .= "\n\treturn liste[value]; \n}); \n\n";
		
		return $contenu_fichier;
	}
	/**
	 *
	 * Génère un filtre vue js pour gérer les avatars des utilisateurs
	 * 
	 */
	protected static function genere_filtre_avatar_utilisateur() {
		
		if($elements === false)
			$elements = modele($type_element)->get();
		
		if($affichage === false)
			$affichage = function($element) { return $element->nom; };
		
		$contenu_fichier = "Vue.filter('affiche_".$type_element."', function (value) {\n\n";

		$contenu_fichier .= "\tif(!value) return '';\n\n";
		$contenu_fichier .= "\tvar liste = [];\n";
		
		foreach($elements as $element) {
			
			$contenu_fichier .= "\tliste[".$element->{$cle_primaire}."] = \"".$affichage($element)."\";\n";
		}
			
		$contenu_fichier .= "\n\treturn liste[value]; \n}); \n\n";
		
		return $contenu_fichier;
	}


}






