<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use Illuminate\Support\Facades\DB;

class S20240607_suppression_mouvement_fantome{

    public function execute(){

        DB::select("update mouvement_de_stock set inactif = 1, cle_externe = 'S20240607_suppression_mouvement_fantome' where id in (select mds.id from mouvement_de_stock as mds
            left join commande_vente_lignes as cvl on  mds.ligne_id=cvl.id
            where mds.reserve = 1 AND mds.type_document = 'commande_vente' and coalesce(mds.inactif,0)=0 and cvl.id is null)");

        DB::select("update mouvement_de_stock set inactif = 1, cle_externe = 'S20240607_suppression_mouvement_fantome' where id in (select mds.id from mouvement_de_stock as mds
            left join commande_achat_lignes as cvl on  mds.ligne_id=cvl.id
            where mds.reserve = 1 AND mds.type_document = 'commande_achat' and coalesce(mds.inactif,0)=0 and cvl.id is null)");

        return true;
    }
}