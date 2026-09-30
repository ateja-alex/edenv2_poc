<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Models\Table_libre;
use Illuminate\Support\Facades\Schema;
use DB;

class S20240116_migration_commentaire_fiche_sur_message implements Script
{

    public function execute()
    {

        if (!Schema::hasTable('commentaire_fiche'))
            return true;

        DB::select('INSERT INTO message (modifie_par,modifie_le,cree_par,cree_le,inactif,chaine_tags_recherche,chaine_affichage,auteur,type_element,element_id,commentaire)
            SELECT modifie_par,modifie_le,cree_par,cree_le,inactif,chaine_tags_recherche,chaine_affichage,cree_par,type_element,element_id,commentaire FROM commentaire_fiche');

        if (is_dir(app_path('Managements'))) {
            $fichiers = scandir(app_path('Managements/'));

            foreach ($fichiers as $fichier) {
                if ($fichier != "." && $fichier != "..") {
                    if (strpos(strtolower($fichier), 'commentaire_fiche') !== false) {
                        abort(403, 'Il y a un fichier commentaire_fiche dans les Managements Spé. Il faut transférer les données vers la table message');
                    }
                }
            }
        }

        Schema::dropIfExists('commentaire_fiche');

        //suppression de commentaire_fiche dans eden_tableslibres
        Table_libre::where('nom_table_sql', 'commentaire_fiche')->delete();

        return true;
    }
}
