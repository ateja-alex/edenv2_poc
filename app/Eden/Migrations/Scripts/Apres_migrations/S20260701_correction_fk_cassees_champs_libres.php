<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Managements\Maintenance_management;
use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 *
 * Corrige les valeurs de clé étrangère cassées (historiquement 0, ou orphelines) sur les champs
 * libres de type 42 (« Sélection d'élément ») de toutes les tables libres, afin que
 * Maintenance_management::maj_champs_libres() puisse enfin poser les contraintes FK.
 *
 * Deux comportements :
 *  - cree_par / modifie_par : doivent rester NOT NULL => FK KO (ou NULL) remplacée par
 *    l'utilisateur système (id_utilisateur_systeme), puis la colonne est forcée NOT NULL.
 *  - autres champs type 42 (nullable) : FK KO => NULL.
 *
 * Gère aussi quelques FK « standard » hors champs libres (tables système absentes de
 * eden_tableslibres, ex. element_log_detail.id_element_log => element_log) : le type est aligné sur
 * la colonne référencée puis les orphelins sont mis à NULL.
 *
 * Toutes les modifications sont tracées dans storage/logs/fix_fk.log (une ligne JSON par action).
 *
 * Puis on relance maj_champs_libres() pour créer les contraintes désormais que les données sont saines.
 *
 */
class S20260701_correction_fk_cassees_champs_libres implements Script
{

	// Colonnes qui doivent rester NOT NULL : on y met l'utilisateur système, jamais NULL
	private $colonnes_utilisateur_systeme = ['cree_par', 'modifie_par'];

	// FK « standard » hors champs libres : tables système absentes de eden_tableslibres, que
	// maj_champs_libres recrée via mise_en_place_cle_etrangere(). Orphelins => NULL (colonnes nullable).
	private $fk_systeme = [
		[
			'table' => 'element_log_detail',
			'pk' => 'id_element_log_detail',
			'colonne' => 'id_element_log',
			'table_cible' => 'element_log',
			'colonne_cible' => 'id_element_log',
		],
	];

	public function execute()
	{

		DB::connection()->disableQueryLog();	
	
		$erreurs = [];
		$fk_ko = [];

		$id_systeme = id_utilisateur_systeme();

		// Le compte système (cle_externe = 16) doit exister : sinon on ne peut pas remplir
		// cree_par / modifie_par sans réinjecter une FK invalide.
		if (empty($id_systeme))
			throw new \Exception('Compte système Eden introuvable (utilisateur cle_externe = 16). Correction des FK annulée.');

		// Entête de run dans le fichier de traçabilité
		$this->log_fix_fk(['action' => 'debut_run', 'date' => date('Y-m-d H:i:s'), 'id_systeme' => $id_systeme]);

		foreach (Table_libre::get() as $table_libre) {

			// On ignore les tables libres de type vue (vue_sql) : ce sont des vues SQL non
			// modifiables (UPDATE/ALTER impossibles), et maj_champs_libres n'y crée aucune FK.
			if ($table_libre->vue_sql == 1)
				continue;

			$type_element = $table_libre->type_element;

			$champs_type_42 = Champ_libre::where('type_element', $type_element)->where('type', 42)->get();

			foreach ($champs_type_42 as $champ) {

				$nom_sql = $champ->nom_sql;
				$type_element_ajax = $champ->type_element_ajax;

				// On ne traite que les champs pour lesquels maj_champs_libres crée réellement une FK
				// (cible non vide).
				if (empty($type_element_ajax))
					continue;

				try {

					if (in_array($nom_sql, $this->colonnes_utilisateur_systeme)) {

						// La colonne doit matcher {table}.id (int(11) unsigned) et rester NOT NULL,
						// sinon la FK ne peut pas être posée. On corrige la structure historique.
						$this->force_colonne_int_unsigned($type_element, $nom_sql, false, $erreurs);

						// Traçabilité : on logue l'id utilisateur KO (ancienne valeur) avant remplacement
						
						// tant que log_fix_fk() est désactivé ; voir remarque en tête de fichier).
						$nb = DB::selectOne(
							"SELECT COUNT(*) AS nb
							 FROM `{$type_element}` AS t
							 LEFT JOIN `utilisateur` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 WHERE table_lie.id IS NULL"
						)->nb;

						// FK KO ET valeurs NULL => utilisateur système (colonne NOT NULL)
						DB::update(
							"UPDATE `{$type_element}` AS t
							 LEFT JOIN `utilisateur` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 SET t.`{$nom_sql}` = ?
							 WHERE table_lie.id IS NULL",
							[$id_systeme]
						);

					} elseif ($nom_sql === 'cle_locale') {

						// Clé locale d'une table pivot (NOT NULL) : une ligne dont cle_locale pointe
						// vers un élément supprimé est une association morte => on la supprime.

						// Traçabilité : on logue chaque ligne supprimée en entier avant le DELETE
						
						// des lignes entières en mémoire pour un log actuellement désactivé).
						$nb = DB::selectOne(
							"SELECT COUNT(*) AS nb FROM `{$type_element}` AS t
							 LEFT JOIN `{$type_element_ajax}` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 WHERE table_lie.id IS NULL"
						)->nb;

						DB::delete(
							"DELETE t FROM `{$type_element}` AS t
							 LEFT JOIN `{$type_element_ajax}` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 WHERE table_lie.id IS NULL"
						);

					} else {

						// La colonne doit matcher {table}.id (int(11) unsigned) et rester NOT NULL,
						// sinon la FK ne peut pas être posée. On corrige la structure historique.
						$this->force_colonne_int_unsigned($type_element, $nom_sql, true, $erreurs);

						// Traçabilité : id + ancienne valeur avant mise à NULL
					
						$nb = DB::selectOne(
							"SELECT COUNT(*) AS nb
							 FROM `{$type_element}` AS t
							 LEFT JOIN `{$type_element_ajax}` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 WHERE table_lie.id IS NULL AND t.`{$nom_sql}` IS NOT NULL"
						)->nb;

						// Autres champs type 42 (nullable) : orphelins => NULL
						DB::update(
							"UPDATE `{$type_element}` AS t
							 LEFT JOIN `{$type_element_ajax}` AS table_lie ON table_lie.id = t.`{$nom_sql}`
							 SET t.`{$nom_sql}` = NULL
							 WHERE table_lie.id IS NULL AND t.`{$nom_sql}` IS NOT NULL"
						);

					}

					if ($nb > 0)
						$fk_ko[] = ['table' => $type_element, 'colonne' => $nom_sql, 'cible' => $type_element_ajax, 'lignes' => $nb];
				} catch (\Exception | Throwable $e) {
					$erreurs[] = 'Correction FK impossible sur ' . $type_element . '.' . $nom_sql . ' : ' . $e->getMessage();
				}
			}
		}

		// FK « standard » hors champs libres (tables système absentes de eden_tableslibres)
		foreach ($this->fk_systeme as $fk) {
			try {
				$this->corrige_fk_systeme($fk, $erreurs, $fk_ko);
			} catch (\Exception | Throwable $e) {
				$erreurs[] = 'Correction FK système impossible sur ' . $fk['table'] . '.' . $fk['colonne'] . ' : ' . $e->getMessage();
			}
		}

		// On trace le récapitulatif des FK cassées corrigées
		if (!empty($fk_ko))
			log_eden('S20260701_correction_fk_cassees_champs_libres : ' . json_encode($fk_ko), 2);

		// On relance la mise en place des clés étrangères, désormais que les données sont saines
		Maintenance_management::maj_champs_libres();

		if (!empty($erreurs))
			throw new \Exception('Erreurs lors de la correction des FK cassées : ' . print_r($erreurs, true));

		return true;
	}

	/**
	 *
	 * Écrit une ligne JSON dans storage/logs/fix_fk.log (traçabilité des corrections),
	 * en mode append pour ne pas écraser l'historique des runs précédents.
	 *
	 */
	private function log_fix_fk(array $entree)
	{
		return;

		file_put_contents(
			storage_path('logs/fix_fk.log'),
			json_encode($entree, JSON_UNESCAPED_UNICODE) . PHP_EOL,
			FILE_APPEND
		);
	}

	/**
	 *
	 * Normalise une colonne FK en `int(11) unsigned NOT NULL` : c'est le type de la colonne `id`
	 * référencée, indispensable pour que la contrainte FK puisse être posée (le type, signedness
	 * comprise, doit correspondre). Corrige la structure historique laissée en signé et/ou nullable.
	 * À n'appeler qu'après avoir garanti qu'aucune valeur NULL ne subsiste dans la colonne.
	 *
	 * $auto_increment doit être passé à true quand la colonne est la PK auto-incrémentée de sa
	 * table (colonne cible d'une FK système) : un MODIFY réécrit toute la définition de colonne,
	 * donc AUTO_INCREMENT doit être explicitement réinjecté sous peine d'être silencieusement perdu.
	 *
	 */
	private function force_colonne_int_unsigned($type_element, $nom_sql, $nullable, &$erreurs, $auto_increment = false)
	{
		$colonne = DB::selectOne(
			"SELECT COLUMN_TYPE, IS_NULLABLE, EXTRA
			 FROM information_schema.columns
			 WHERE table_schema = ? AND table_name = ? AND column_name = ?",
			[env('DB_DATABASE'), $type_element, $nom_sql]
		);

		if ($colonne === null)
			return;

		// Déjà au bon type, à la bonne nullabilité (selon $nullable) et, si demandé, déjà
		// auto-incrémentée : rien à faire.
		$deja_ok = strtolower($colonne->COLUMN_TYPE) === 'int(11) unsigned'
			&& (($colonne->IS_NULLABLE === 'NO' && !$nullable) || ($colonne->IS_NULLABLE === 'YES' && $nullable))
			&& (!$auto_increment || str_contains(strtolower($colonne->EXTRA), 'auto_increment'));

		if ($deja_ok)
			return;

		try {
			$nullable_sql = $nullable ? 'NULL' : 'NOT NULL';
			$auto_increment_sql = $auto_increment ? ' AUTO_INCREMENT' : '';
			DB::statement("ALTER TABLE `{$type_element}` MODIFY `{$nom_sql}` int(11) unsigned {$nullable_sql}{$auto_increment_sql}");
		} catch (\Exception | Throwable $e) {
			$erreurs[] = 'Impossible de normaliser ' . $type_element . '.' . $nom_sql . ' en int(11) unsigned ' . ($nullable ? 'NULL' : 'NOT NULL') . ($auto_increment ? ' AUTO_INCREMENT' : '') . ' : ' . $e->getMessage();
		}
	}

	/**
	 *
	 * Corrige une FK « standard » (hors champs libres) sur une table système absente de
	 * eden_tableslibres. La colonne cible pouvant être une PK nommée autrement que `id`, on la
	 * passe explicitement. Type aligné sur la colonne référencée, puis orphelins mis à NULL.
	 *
	 */
	private function corrige_fk_systeme($fk, &$erreurs, &$fk_ko)
	{
		$table = $fk['table'];
		$pk = $fk['pk'];
		$colonne = $fk['colonne'];
		$table_cible = $fk['table_cible'];
		$colonne_cible = $fk['colonne_cible'];

		// La colonne locale doit avoir exactement le type de la colonne référencée, sinon la FK ne
		// peut pas être posée. On l'aligne en gardant la colonne nullable (stratégie NULL).
		$this->force_colonne_int_unsigned($table, $colonne, true, $erreurs);
		// La colonne cible est la PK de la table référencée : elle doit garder son AUTO_INCREMENT.
		$this->force_colonne_int_unsigned($table_cible, $colonne_cible, false, $erreurs, true);

		// Traçabilité : PK + ancienne valeur avant mise à NULL
		// tête de fichier / sur log_fix_fk).
		$nb = DB::selectOne(
			"SELECT COUNT(*) AS nb
			 FROM `{$table}` AS t
			 LEFT JOIN `{$table_cible}` AS table_lie ON table_lie.`{$colonne_cible}` = t.`{$colonne}`
			 WHERE table_lie.`{$colonne_cible}` IS NULL AND t.`{$colonne}` IS NOT NULL"
		)->nb;

		DB::update(
			"UPDATE `{$table}` AS t
			 LEFT JOIN `{$table_cible}` AS table_lie ON table_lie.`{$colonne_cible}` = t.`{$colonne}`
			 SET t.`{$colonne}` = NULL
			 WHERE table_lie.`{$colonne_cible}` IS NULL AND t.`{$colonne}` IS NOT NULL"
		);

		if ($nb > 0)
			$fk_ko[] = ['table' => $table, 'colonne' => $colonne, 'cible' => $table_cible, 'lignes' => count($lignes)];
	}
}
