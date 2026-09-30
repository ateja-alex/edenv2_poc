<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Champ_libre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class S20260331_int_bigint implements Script
{

    /**
     * Exécute le processus de modification des types de colonnes des tables de la base de données en BIGINT pour des champs spécifiques.
     *
     * Cette fonction récupère les champs de `eden_champslibres` qui sont joints à `eden_tableslibres`
     * selon des conditions spécifiques. Pour chaque champ récupéré, elle tente de modifier la structure de la table MySQL
     * pour changer le type de colonne en `BIGINT`. Toutes les erreurs rencontrées lors de cette opération sont journalisées.
     *
     * Conditions :
     * - Jointure entre `eden_tableslibres` et `eden_champslibres` sur la propriété `type_element`.
     * - Filtre les enregistrements de `eden_tableslibres` où `vue_sql` est 0 ou null.
     * - Filtre les enregistrements où `eden_tableslibres.type` est 2.
     *
     * Opérations de base de données :
     * - Exécute une instruction SQL pour modifier le type de colonne pour la table et la colonne spécifiées.
     *
     * Gestion des erreurs :
     * - Journalise les erreurs lorsqu'une exception survient lors de l'opération de base de données.
     */
    public function execute(): bool
    {
        $liste_champs = Champ_libre::join("eden_tableslibres", "eden_tableslibres.type_element", "=", "eden_champslibres.type_element")
            ->where(function($where){
                $where->where("eden_tableslibres.vue_sql", 0)->orWhereNull("eden_tableslibres.vue_sql");
            })
            ->where('eden_champslibres.type', 2)
            ->get();
        foreach($liste_champs as $champ){
            try {
                DB::statement('ALTER TABLE ' . $champ->type_element . ' MODIFY ' . $champ->nom_sql . ' BIGINT');
            }
            catch (\Exception $e) {
                Log::error("Erreur lors de la modification du champs " . $champ->nom_sql . " de la table " . $champ->type_element . " : " . $e->getMessage());
            }
        }

        return true;
    }
}
