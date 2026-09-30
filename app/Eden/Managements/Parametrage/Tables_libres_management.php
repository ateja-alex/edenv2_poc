<?php

namespace App\Eden\Managements\Parametrage;

use App\Eden\Models\Table_libre;
use App\Eden\Models\Utilisateur;
use App\Eden\Models\Champ_libre;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use DB;


/**
 * Classe de gestions des droits des utilisateurs pour l'affichage des élements
 * de l'ERP
 */
class Tables_libres_management {

    public static $types_elements_profils = [
        'facture_vente',
        'avoir_vente',
        'paiement',
        'client',
        'echange',
        'coupon_reduction',
        'credit'
    ];

    /**
     * Récupère les informations concernant les types d'éléments données en paramètre
     * 
     * @param array $types_elements Liste des types d'éléments recherchés sous 
     *                              forme de tableau de string
     * 
     * @return Collection 
     */
    public function recuperer_informations_types_elements($types_elements) {

        $liste_types_elements = Table_libre::whereIn('nom_table_sql', $types_elements)->get();

        $liste_finale = array();

        foreach($liste_types_elements as $type_element) {

            $liste_finale[$type_element->nom_table_sql] = $type_element;
        }

        return $liste_finale;
    }

    /**
     * Ajoute une colonne utilisateur à la requête de modification de la table
     * 
     * @param string $table nom de la table à laquelle ajouter une colonne utilisateur
     * 
     * @param int $id_utilisateur id de l'utilisateur pour lequel il faut ajouter une colonne
     * 
     * @return string chaîne de caractères à ajouter à la requête si la colonne n'existe pas sinon une chaîne vide
     */
    static private function ajouter_colonne_utilisateur($table, $id_utilisateur) {

        $colonne_utilisateur = "utilisateur_" . $id_utilisateur;

        if (!Schema::hasColumn($table, $colonne_utilisateur)) {

            return "add `". $colonne_utilisateur . "` int not null";
        }

        return "";
    }
	
	/**
	 * 
	 * Crée la table sur la base de données
	 * 
	 */
	public static function cree_table($type_element) {

		// on crée la table si elle n'existe pas
		if(!\Schema::hasTable($type_element)) {

			DB::select('CREATE TABLE`'.$type_element.'` (`id` int(11) NOT NULL AUTO_INCREMENT,`modifie_par` int(11) NOT NULL,`modifie_le` datetime NOT NULL, `cree_par` int(11) NOT NULL,`cree_le` datetime NOT NULL, `inactif` int(11),`chaine_tags_recherche` longtext, PRIMARY KEY (`id`), FULLTEXT INDEX `chaine_tags_recherche` (`chaine_tags_recherche`)) ENGINE=InnoDB DEFAULT CHARSET=latin1');
		}

		Table_libre::champs_libres_par_defaut($type_element);
	}
	
	/**
	 *
	 * Enregistre une nouvelle table libre & sa table correspondante
	 *
	 */
	public static function enregistre($element, $donnees, $type_element = false) {

		// on vérifie l'intégritée des données
		if(empty($donnees['nom_table'])) {

			return "Le champ nom est obligatoire";
		}
		
		elseif(empty($donnees['element'])) {

			return "Le champ element est obligatoire";
		}
		
		elseif(empty($donnees['element_pluriel'])) {

			return "Le champ element pluriel est obligatoire";
		}	

		



		elseif(empty($donnees['id_table'])){

			$table_libre = new Table_libre;

			// on crée le nom_table_sql
			if($type_element === false) {
				
				$accents = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'à', 'á', 'â', 'ã', 'ä', 'å',
					'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
					'È', 'É', 'Ê', 'Ë', 'è', 'é', 'ê', 'ë', 'é', // (le dernier 'é' n'est pas un doublon, c'est un caractère spécial)
					'Ç', 'ç',
					'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï',
					'Ù', 'Ú', 'Û', 'Ü', 'ù', 'ú', 'û', 'ü',
					'ÿ',
					'Ñ', 'ñ');

				$sans_accents = str_split('AAAAAAaaaaaaOOOOOOooooooEEEEeeeeeCcIIIIiiiiUUUUuuuuyNn');

				$nom_table_sql = str_replace($accents, $sans_accents, $element);

				$nom_table_sql = preg_replace("[^A-Za-z0-9]", '', $nom_table_sql);

				// autres caractères spéciaux
				$a_remplacer = str_split("&-(){}!?,;.:/\#[]'\" ~`^@°+=£€¤*%§<>\$²");

				$a_retirer = str_split('’̀');
				
				$nom_table_sql = str_replace($a_remplacer, '_', $nom_table_sql);
				$nom_table_sql = str_replace($a_retirer, '', $nom_table_sql);
				
				$nom_table_sql = strtolower(str_replace('__', '_', $nom_table_sql));
				
				$nom_table_sql_tmp = $nom_table_sql;
			}
			else {
				
				
				$nom_table_sql = $type_element;
				$nom_table_sql_tmp = $type_element;
			}



			$count = 1;

			//Permet d'éviter les doublons nom_table_sql en ajoutant 1 en fin de nom sql
			while(Table_libre::where('nom_table_sql', $nom_table_sql_tmp)->count() > 0) {

				$nom_table_sql_tmp = $nom_table_sql.'_'.$count;
				$count++;
			}

			//On enregistre chacun des champs qui sont fixes ou variables. Si élément n'est pas rempli, il est identique à nom_table
			
            if(empty($donnees['creation_rapide'])) {
				
                $table_libre->creation_rapide = 0;
		    }	
			else {
				
				$table_libre->creation_rapide = $donnees['creation_rapide'];
			}

			$table_libre->element = $donnees['element'];
		    $table_libre->element_pluriel = $donnees['element_pluriel'];			
			$table_libre->nom_table_sql = $nom_table_sql_tmp;
			$table_libre->type_element = $nom_table_sql_tmp;
			$table_libre->nom_table = $donnees['nom_table'];
	    	$table_libre->description = '';
	        $table_libre->feminin = 'e';
	    	$table_libre->fiche = 0 ;
		    $table_libre->disponible_recherche_rapide = 0 ;
			
		    $table_libre->save();

		    // on crée la table sur la base de données
			self::cree_table($nom_table_sql_tmp);

			Maintenance_management::generer_fichier_migration($table_libre->nom_table_sql);

			return true;	
			
		}
		
		else {
			return true ;
		}

	}
	
	public static function supprime($nom_table) {
		
		// DB::select('DROP TABLE`'. $nom_table.'`');
		
	}
}
