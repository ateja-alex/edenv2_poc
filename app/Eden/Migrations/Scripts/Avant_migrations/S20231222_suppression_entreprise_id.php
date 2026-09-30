<?php

namespace App\Eden\Migrations\Scripts\Avant_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20231222_suppression_entreprise_id implements Script
{

    public function execute()
    {
        try {

            $liste_entite = ["adresse", "client", "contact", "echange"];

            foreach ($liste_entite as $entite) {
                if (Schema::hasColumn($entite, 'entreprise_id') && modele($entite)->whereNotNull("entreprise_id")->count() == 0) {
                    if (($fk = DB::selectOne("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = '" . env("DB_DATABASE") . "'  AND COLUMN_NAME = 'entreprise_id' AND TABLE_NAME = '" . $entite . "'")) != null)
                        Schema::table($entite, function (Blueprint $blueprint) use ($fk) {
                            $blueprint->dropForeign($fk->CONSTRAINT_NAME);
                        });

                    Schema::table($entite, function (Blueprint $table) {
                        $table->dropColumn('entreprise_id');
                    });
                }
            }
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la migration de suppression d'entreprise_id : {$e}");
        }

        return true;
    }
}