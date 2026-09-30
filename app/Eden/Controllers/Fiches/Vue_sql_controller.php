<?php

namespace App\Eden\Controllers\Fiches;

use Illuminate\Http\Request;
use App\Eden\Models\Champ_libre;
use App\Eden\Controllers\Fiche_controller;
use Illuminate\Support\Facades\DB;

class Vue_sql_controller extends Fiche_controller {

	public function enregistrer_gestion_champs_libres (Request $formulaire) {
        $vue_sql = modele('vue_sql',$this->id_element);

        $formulaire = $formulaire->all();

        if(!isset($formulaire['gestion_champs_libres']))
            return response()->json(['retour'=>true]);

        if($vue_sql->type_de_vue != 1)
            $retour = $this->gestion_champs_libres_select($vue_sql,$formulaire['gestion_champs_libres']);

        else
            $retour = $this->gestion_champs_libres_union($vue_sql,$formulaire['gestion_champs_libres']);

        if($retour['retour'] !== true)
            return response()->json($retour);

        //On regénére la vue
        service('vue_sql')->generer_vues_sql(array($vue_sql));
        
        return response()->json($retour);
	}

	public function gestion_champs_libres_select($vue_sql,$champs_libres){
        foreach($champs_libres['type_element'] as $type_element => $nom_element){

            foreach($champs_libres[$type_element] as $champ_libre){

                $modele_champ_libre= $champ_libre['modele'];

                // Champ_libre déjà existant
                $champ_libre_existant = Champ_libre::where('type_element',$vue_sql->nom_sql)
                    ->where('type_element_origine',$modele_champ_libre['type_element'])
                    ->where('nom_sql_origine',$modele_champ_libre['nom_sql'])
                    ->first();

                $champ_libre_initial = Champ_libre::where('type_element',$modele_champ_libre['type_element'])
                    ->where('nom_sql',$modele_champ_libre['nom_sql'])
                    ->first();

                if($champ_libre['actif'] == "true" && !$champ_libre_existant){

                    service('vue_sql')->gestion_champ_libre($vue_sql->nom_sql,$champ_libre_initial);

                }

                elseif($champ_libre['actif'] == "false" && $champ_libre_existant){

                    $champ_libre_existant->delete();
                }
            }
        }

        return array('retour' => true);
    }

    public function gestion_champs_libres_union($vue_sql,$champs_libres){
	    $champs_libres_existants = Champ_libre::where('type_element',$vue_sql->nom_sql)->get()->keyBy('id_cl');

        $champs_libres_a_supprimer = clone $champs_libres_existants;

        $type_element_joints = array();

        foreach($champs_libres as $champ_libre) {

            $parent = trim($champ_libre['parent']);

            if(empty($parent))
                return array('retour' => traduction('messages.php.champ_obligatoire'). ' '. traduction('module_sur_fiche.vue_sql.gestion_champs_libres.champ_reference'));

            $modele_parent_champ_libre = explode('.',$parent);

            if(!in_array($modele_parent_champ_libre[0],$type_element_joints))
                $type_element_joints[] = $modele_parent_champ_libre[0];

            if ($champ_libre['id_cl'] != null && isset($champs_libres_a_supprimer[$champ_libre['id_cl']])) {

                unset($champs_libres_a_supprimer[$champ_libre['id_cl']]);
            }

        }

        foreach($champs_libres_a_supprimer as $champ_libre_a_supprimer){

            $champ_libre_a_supprimer->delete();
        }

        $champs_libres_initial_par_type_element = Champ_libre::select(DB::raw("CONCAT(type_element,'.',nom_sql) as type_element_nom_sql"),'eden_champslibres.*')->whereIn('type_element',$type_element_joints)
                    ->get()->keyBy('type_element_nom_sql');

        foreach($champs_libres as $champ_libre) {

            $parent = trim($champ_libre['parent']);

            $modele_parent_champ_libre = explode('.',$parent);

            unset($champ_libre['parent']);

            $champ_libre_existant = null;

            if(isset($champs_libres_existants[$champ_libre['id_cl']])) {
                $champ_libre_existant = $champs_libres_existants[$champ_libre['id_cl']];
            }

            unset($champ_libre['id_cl']);

            if(!isset($champs_libres_initial_par_type_element[$modele_parent_champ_libre[0].'.'.$modele_parent_champ_libre[1]]))
                return array('retour' => traduction('messages.php.fiche_vue_sql.champ_reference_inexistant',null,array($parent)));

            unset($champs_libres_initial_par_type_element[$modele_parent_champ_libre[0].'.'.$modele_parent_champ_libre[1]]->type_element_nom_sql);

            $champ_libre_initial = $champs_libres_initial_par_type_element[$modele_parent_champ_libre[0].'.'.$modele_parent_champ_libre[1]];

            $retour = service('vue_sql')->gestion_champ_libre($vue_sql->nom_sql,$champ_libre_initial,$champ_libre,$champ_libre_existant);

            if($retour!== true && $retour != 'Mise à jour non nécessaire')
                return array('retour' => $retour);
        }

        $champs_libres = Champ_libre::where('type_element',$vue_sql->nom_sql)->select('id_cl','nom_sql','nom',DB::raw("CONCAT(type_element_origine,'.',nom_sql_origine) AS parent"))->get()->toArray();

        return array('retour' => true,'gestion_champs_libres' => $champs_libres);

    }

}