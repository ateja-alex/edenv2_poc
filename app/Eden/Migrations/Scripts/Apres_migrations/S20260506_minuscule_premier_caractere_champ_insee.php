<?php

namespace App\Eden\Migrations\Scripts\Apres_migrations;

use App\Eden\Migrations\Scripts\Script;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class S20260506_minuscule_premier_caractere_champ_insee implements Script {

    public function execute(){

        if(!Schema::hasColumn('mappage_insee', 'champ_insee'))
            return true;

        $lignes = DB::table('mappage_insee')->select('id', 'champ_insee')->whereNotNull('champ_insee')->where('champ_insee', '!=', '')->get();

        foreach($lignes as $ligne){
            $cles = explode('+', $ligne->champ_insee);
            $cles_modifiees = array_map(function($cle){
                if(!preg_match('/^(\s*)(\S)(.*)$/s', $cle, $m)) return $cle;
                return $m[1] . strtolower($m[2]) . $m[3];
            }, $cles);

            $nouvelle_valeur = implode('+', $cles_modifiees);

            if($nouvelle_valeur !== $ligne->champ_insee)
                DB::table('mappage_insee')->where('id', $ligne->id)->update(['champ_insee' => $nouvelle_valeur]);
        }

        return true;
    }
}