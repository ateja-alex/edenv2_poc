<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use App\Eden\Managements\Elements\Synchronisation_service_management;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20260810_chiffrement_parametres_synchronisation_service implements Script {

    public function execute(){

        if(!Schema::hasTable('synchronisation_service') || !Schema::hasColumn('synchronisation_service', 'parametres'))
            return true;

        if(empty(env('CLE_CRYPTAGE')))
            return true;

        $services = DB::table('synchronisation_service')->whereNotNull('parametres')->where('parametres', '!=', '')->get(['id', 'parametres']);

        foreach($services as $service){

            if(json_decode($service->parametres, true) === null)
                continue;

            DB::table('synchronisation_service')
                ->where('id', $service->id)
                ->update(['parametres' => Synchronisation_service_management::chiffre($service->parametres)]);
        }

        return true;
    }
}
