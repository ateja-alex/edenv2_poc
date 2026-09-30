<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Managements\Elements\Element_management;
use DB;

class Synchronisation_service_element_management extends Element_management{

    public function liste_colonnes_options(){

        $liste_options = parent::liste_colonnes_options();

        $liste_options[] = 'lancer_synchronisation';

        return $liste_options;
    }

     public function enregistre($modifications = array(), $modele = false){

        $filtrage = false;

        if(array_key_exists('filtrage', $modifications)){
            $filtrage = json_decode($modifications['filtrage'], true);
            unset($modifications['filtrage']);
        }

        $retour = parent::enregistre($modifications, $modele);

        if($retour !== true || $filtrage === false)
            return $retour;

        $recherche_avancee = modele('recherche_avancee')
            ->where('type', 'synchronisation_service_element')
            ->where('id_cible', $this->modele->id)->first();

        if(!empty($recherche_avancee))
            $management_recherche_avancee = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee);
        else
            $management_recherche_avancee = management('recherche_avancee');

        if(empty($filtrage)){

            if(!empty($recherche_avancee))
                $management_recherche_avancee->supprime();

            return true;
        }

        $management_recherche_avancee->enregistre([
            'type' => 'synchronisation_service_element',
            'type_element' => $this->modele->type_element,
            'id_cible' => $this->modele->id,
            'structure' => $filtrage
        ]);

        return true;
    }

    public function methodes_post_modification($modele, $modele_avant, $modifications){

        oublie_cache_eden('synchronisations.'.$modele->type_element);

        if(!empty($modele_avant->type_element) && $modele_avant->type_element != $modele->type_element)
            oublie_cache_eden('synchronisations.'.$modele_avant->type_element);

        return parent::methodes_post_modification($modele, $modele_avant, $modifications);
    }

    public function methodes_post_suppression($modele){

        oublie_cache_eden('synchronisations.'.$modele->type_element);

        return parent::methodes_post_suppression($modele);
    }

    private $origine = null;
    private $champs = false;

    public function origine(){

        if(!isset($this->origine))
            $this->origine = management('synchronisation_service',$this->modele->synchronisation_service_id);

        return $this->origine;
    }

    public function champs(){

        if($this->champs === false){

            $champs = modele('synchronisation_service_champs')
                ->where('synchronisation_service_element_id',$this->modele->id)
                ->get();

            $champs_externes = $this->champs_externes();

            if($this->modele->type_synchronisation == 0){

                $modele_table_pivot = table_libre_existe('synchronisation_service_champs_types_evenements') ? modele('synchronisation_service_champs_types_evenements') : \DB::table('synchronisation_service_champs_types_evenements');

                $champs_types_evenements = $modele_table_pivot
                    ->whereIn('cle_locale', $champs->pluck('id'))
                    ->get()->groupBy('cle_locale')->map(function($valeurs){
                            return $valeurs->pluck('valeur');
                        })->toArray();

                foreach($champs as $champ){

                    $types_evenements = [];
                    
                    if(!empty($champs_types_evenements[$champ->id]))
                        $types_evenements = $champs_types_evenements[$champ->id];

                    if(empty($champ->nom_externe)){

                        $table_externe = $this->table_externe();

                        $disponibilites = collect($table_externe['disponibilites'] ?? [])
                            ->where('type_synchronisation', $this->modele->type_synchronisation)->first();

                        if(sizeof($disponibilites['types_evenements'] ?? []) == 1)
                            $types_evenements = $disponibilites['types_evenements'];
                    }
                    else{

                        $liste_champs_externes = $champs_externes;

                        $parties_nom_externe = explode('.',$champ->nom_externe);

                        $nom_externe = array_pop($parties_nom_externe);

                        foreach($parties_nom_externe as $partie){
                            $liste_champs_externes = collect($liste_champs_externes)->where('nom',$partie)->first()['valeurs'] ?? [];
                        }

                        $champ_externe = collect($liste_champs_externes)->where('nom',$nom_externe)->first();

                        $disponibilites = collect($champ_externe['disponibilites'] ?? [])
                            ->where('sens', $champ->sens)->toArray();

                        if(sizeof($disponibilites) > 1)
                            array_filter($disponibilites, function($disponibilite){
                                return !empty($disponibilite['obligatoire']);
                            });

                        $types_evenements = array_unique(array_merge($types_evenements ?? [], collect($disponibilites)->pluck('type_evenement')->toArray()));
                    }

                    $champ->types_evenements = $types_evenements;
                }

            }

            $this->champs = $champs;
        }

        return $this->champs;
    }

    public function table_externe(){

        if(empty($this->modele->type_externe))
            return null;

        return $this->origine()->service()->table_externe($this->modele->type_externe);
    }

    public function champs_externes(){

        if(empty($this->modele->type_externe))
            return [];

        $champs_externes = $this->origine()->service()->champs_externes($this->modele->type_externe);

        return array_values(array_filter($champs_externes, function($champ_externe){
            return collect($champ_externe['disponibilites'] ?? [])->where('type_synchronisation', $this->modele->type_synchronisation)->count() > 0;
        }));
    }

    public function synchronisation($management_origine){

        $table_externe = $this->table_externe();

        if(empty($table_externe))
            return;

        $requete_etat_synchronisation = modele($management_origine->_type_element)
            ->where($management_origine->_type_element.'.id', $management_origine->modele->id);

        $recherche_avancee = modele('recherche_avancee')
            ->where('type', 'synchronisation_service_element')
            ->where('id_cible', $this->modele->id)->first();

        if(!empty($recherche_avancee)){
            $structure = management('recherche_avancee',$recherche_avancee->id,$recherche_avancee)->structure();
            management('recherche_avancee')->applique_filtrage($structure,$requete_etat_synchronisation,$management_origine->_type_element);
        }

        $etat_synchronisation = $requete_etat_synchronisation->count() == 1;

        $historique_element = modele('synchronisation_service_element_historique')
            ->where('synchronisation_service_element_id', $this->modele->id)
            ->where('element_id', $management_origine->modele->id)
            ->where('type_element', $management_origine->_type_element)
            ->orderBy('cree_le', 'desc')
            ->first();

        // Si on a rien à synchroniser et que l'élément n'était pas synchronisé avant, on ne fait rien
        if(!$etat_synchronisation && (empty($historique_element) || $historique_element->type_evenement == 3))
            return true;

        if(!$etat_synchronisation)
            $type_evenement = 3; // Suppression
        else if(empty($historique_element) || $historique_element->type_evenement == 3)
            $type_evenement = 1; // Création
        else
            $type_evenement = 2; // Modification

        $types_evenements = collect($table_externe['disponibilites'])->where('type_synchronisation', $this->modele->type_synchronisation)->first()['types_evenements'] ?? [];

        if(!in_array($type_evenement,$types_evenements) || !method_exists($this->origine()->service(),$this->fonction_a_appeler($type_evenement)))
            return true;

        $champs_evenements = $this->champs()->filter(function($champ) use ($type_evenement){
            return in_array($type_evenement, $champ->types_evenements);
        })->values();

        $valeurs_champs_evenements = [];

        foreach($champs_evenements->where('sens',0) as $champ_evenement){

            $partie_nom_externe = explode('.',$champ_evenement->nom_externe);

            $nom_externe = array_pop($partie_nom_externe);

            $valeur_a_modifier = &$valeurs_champs_evenements;

            foreach($partie_nom_externe as $partie){

                if(!isset($valeur_a_modifier[$partie]))
                    $valeur_a_modifier[$partie] = [];

                $valeur_a_modifier = &$valeur_a_modifier[$partie];
            }

            $management_champ = management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement);

            $valeur = $management_champ->valeur_champ_interne($management_origine);

            $valeur_a_modifier[$nom_externe] = $valeur ?? '';
        }

        if($type_evenement == 2 && $valeurs_champs_evenements == json_decode($historique_element->valeurs_transmises,true))
            return true;
        
        if(empty($valeurs_champs_evenements))
            return true;

        queue('synchronisation_externe')::dispatch($this,$management_origine, $type_evenement, $valeurs_champs_evenements, $champs_evenements->where('sens',1)->values());
    }

    public function fonction_a_appeler($type_evenement){

        $types_evenements = [
            1 => 'creation',
            2 => 'modification',
            3 => 'suppression'
        ];

        return $types_evenements[$type_evenement].'_'.$this->modele->type_externe;
    }

    public function webhook($donnees){

        if($this->modele->type_synchronisation != 1 || !empty($this->modele->desactive))
             return traduction('messages.php.synchronisation_service_element.synchronisation_desactivee');

        synchronisation_service_en_cours(true);

        $table_externe = $this->table_externe();

        if(empty($table_externe))
            return;

        $methode = 'donnees_webhook_'.$this->modele->type_externe;

        if(method_exists($this->origine()->service(),$methode))
            $donnees = $this->origine()->service()->{$methode}($donnees);

        $modifications = [];

        $champs_evenements = $this->champs()->values();

        foreach($champs_evenements as $champ_evenement){

            $management_champ = management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement);
            $valeur_externe =  $management_champ->valeur_champ_externe($donnees);

            $modifications[$champ_evenement->nom_sql] = $valeur_externe;
        }

        $management_element = management($this->modele->type_element);

        $retour_modification = $management_element->enregistre($modifications);

        if($retour_modification !== true){
            management('synchronisation_service_element_erreur')->enregistre([
                'synchronisation_service_element_id' => $this->modele->id,
                'type_evenement' => 1,
                'valeurs_transmises' => json_encode($modifications),
                'message_erreur' => $retour_modification
            ]);

            return $retour_modification;
        }

        management('synchronisation_service_element_historique')->enregistre([
            'synchronisation_service_element_id' => $this->modele->id,
            'type_element' => $management_element->_type_element,
            'element_id' => $management_element->modele->id,
            'type_evenement' => 1,
            'valeur_recus' => json_encode($modifications)
        ]);

        return true;
    }

    public function synchronisation_element($management_origine, $eviter_delai_lecture = false){

        $verrou = \Cache::lock('synchronisation_element_'.$this->modele->id.'_'.$management_origine->_type_element.'_'.$management_origine->modele->id, 300);

        try {
            return $verrou->block(60, function() use ($management_origine, $eviter_delai_lecture){

                if($this->modele->type_synchronisation == 3)
                    return $this->synchronisation_lecture($management_origine, $eviter_delai_lecture);

                return $this->synchronisation($management_origine);
            });
        }
        catch(\Illuminate\Contracts\Cache\LockTimeoutException $exception){
            return traduction('messages.php.synchronisation_service_element.synchronisation_deja_en_cours');
        }
    }

    public function synchronisation_lecture($management_origine, $eviter_delai_lecture = false){

        if($this->modele->type_synchronisation != 3 || !empty($this->modele->desactive))
            return traduction('messages.php.synchronisation_service_element.synchronisation_desactivee');

        $table_externe = $this->table_externe();

        if(empty($table_externe))
            return;

        $requete_etat_synchronisation = modele($management_origine->_type_element)
            ->where('id', $management_origine->modele->id);

        $recherche_avancee = modele('recherche_avancee')
            ->where('type', 'synchronisation_service_element')
            ->where('id_cible', $this->modele->id)->first();

        if(!empty($recherche_avancee)){
            $structure = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->structure();
            management('recherche_avancee')->applique_filtrage($structure, $requete_etat_synchronisation, $management_origine->_type_element);
        }

        if($requete_etat_synchronisation->count() == 0)
            return true;

        $delai_heures = $this->modele->delai_lecture ?? null;

        if(!empty($delai_heures) && !$eviter_delai_lecture){

            $derniere_lecture = modele('synchronisation_service_element_historique')
                ->where('synchronisation_service_element_id', $this->modele->id)
                ->where('element_id', $management_origine->modele->id)
                ->where('type_element', $management_origine->_type_element)
                ->orderBy('cree_le', 'desc')
                ->value('cree_le');

            if(!empty($derniere_lecture) && $derniere_lecture >= date('Y-m-d H:i:s', time() - ($delai_heures * 3600)))
                return true;
        }

        $methode = 'lecture_'.$this->modele->type_externe;

        if(!method_exists($this->origine()->service(), $methode))
            return traduction('messages.php.synchronisation_service_element.methode_introuvable');

        synchronisation_service_en_cours(true);

        $champs_evenements = $this->champs()->values();

        $valeurs_champs = [];

        foreach($champs_evenements->where('sens', 0) as $champ_evenement){

            $partie_nom_externe = explode('.', $champ_evenement->nom_externe);
            $nom_externe = array_pop($partie_nom_externe);
            $valeur_a_modifier = &$valeurs_champs;

            foreach($partie_nom_externe as $partie){
                if(!isset($valeur_a_modifier[$partie]))
                    $valeur_a_modifier[$partie] = [];
                $valeur_a_modifier = &$valeur_a_modifier[$partie];
            }

            $management_champ = management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement);
            $valeur_a_modifier[$nom_externe] = $management_champ->valeur_champ_interne($management_origine) ?? '';
        }

        $retour = $this->origine()->service()->{$methode}($valeurs_champs);

        if($retour['succes'] === false){
            management('synchronisation_service_element_erreur')->enregistre([
                'synchronisation_service_element_id' => $this->modele->id,
                'type_element' => $management_origine->_type_element,
                'element_id' => $management_origine->modele->id,
                'type_evenement' => 4,
                'valeurs_transmises' => json_encode($valeurs_champs),
                'code_erreur' => $retour['code'] ?? '#NA#',
                'message_erreur' => $retour['message'] ?? '#ERREUR INCONNUE#'
            ]);
            return $retour['message'];
        }

        $elements_externes = $retour['donnees'];
        $type_element_destination = $this->modele->type_element_destination;

        if(!empty($type_element_destination) && !empty($elements_externes)){

            $cle_maj_eden = $champs_evenements->where('cle_mise_a_jour', 1)->map(function($element) use ($type_element_destination){
                return $type_element_destination.'.'.$element->nom_sql;
            })->toArray();

            $champs_libres_destination = champs_libres($type_element_destination)->keyBy('nom_sql');
            $types_champs_bdd = \App\Eden\Variables::types_champs_libres_bdd();

            $cles_entieres = [];

            foreach($champs_evenements->where('cle_mise_a_jour', 1) as $champ){

                $type_champ_destination = $champs_libres_destination[$champ->nom_sql]->type ?? null;

                $type_bdd = $types_champs_bdd[$type_champ_destination] ?? null;

                if(is_array($type_bdd))
                    $type_bdd = $type_bdd['type'] ?? null;

                if(in_array($type_bdd, ['INT', 'BIGINT']))
                    $cles_entieres[$champ->nom_sql] = true;
            }

            $cles_mise_a_jour_externes = array_map(function($element) use ($champs_evenements, $cles_entieres){
                $valeurs = [];
                foreach($champs_evenements->where('cle_mise_a_jour', 1) as $champ){
                    $management_champ = management('synchronisation_service_champs', $champ->id, $champ);
                    $valeur = $management_champ->valeur_champ_externe($element);
                    $valeurs[] = isset($cles_entieres[$champ->nom_sql]) ? (int) $valeur : $valeur;
                }
                return implode('|', $valeurs);
            }, $elements_externes);

            $elements_eden = collect();

            if(!empty($cle_maj_eden)){

                $concat_cle_mise_a_jour = "CONCAT(".implode(",'|',", $cle_maj_eden).")";

                $modele_destination = modele($type_element_destination);

                if(empty($this->modele->suppression_elements_absents))
                    $modele_destination->avec_inactifs();

                $requete_elements_eden = $modele_destination
                    ->select($type_element_destination.'.*', DB::raw($concat_cle_mise_a_jour." as _cle_mise_a_jour"))
                    ->whereIn(DB::raw($concat_cle_mise_a_jour), $cles_mise_a_jour_externes);

                if(!empty($this->modele->champ_element_destination))
                    $requete_elements_eden->where($type_element_destination.'.'.$this->modele->champ_element_destination, $management_origine->modele->id);

                $elements_eden = $requete_elements_eden->get()->keyBy('_cle_mise_a_jour');
            }

            $elements_a_supprimer = collect();

            if(!empty($this->modele->suppression_elements_absents) && !empty($this->modele->champ_element_destination) && !empty($cle_maj_eden))
                $elements_a_supprimer = modele($type_element_destination)
                    ->where($type_element_destination.'.'.$this->modele->champ_element_destination, $management_origine->modele->id)
                    ->get()->keyBy('id');

            $ids_modifies = [];

            foreach($elements_externes as $index => $element_externe){

                $cle = $cles_mise_a_jour_externes[$index];
                $modifications = [];

                foreach($champs_evenements->where('sens', 1) as $champ){
                    $management_champ = management('synchronisation_service_champs', $champ->id, $champ);
                    $modifications[$champ->nom_sql] = $management_champ->valeur_champ_externe($element_externe);
                }

                if(!empty($this->modele->champ_element_destination))
                    $modifications[$this->modele->champ_element_destination] = $management_origine->modele->id;

                if(!empty($elements_eden[$cle])){

                    $modele_element = $elements_eden[$cle];

                    $elements_a_supprimer->forget($modele_element->id);

                    if($modele_element->inactif == 1) continue;

                    unset($modele_element->_cle_mise_a_jour);

                    $management_element = management($type_element_destination, $modele_element->id, $modele_element);

                    $modification_utile = false;
                    foreach($modifications as $champ => $valeur){
                        if($management_element->modele->{$champ} != $valeur){
                            $modification_utile = true;
                            break;
                        }
                    }

                    if(!$modification_utile) continue;
                }
                else{
                    $management_element = management($type_element_destination);
                }

                $management_element->fonction_a_eviter[] = 'trigger_applicatif';

                $retour_modification = $management_element->enregistre($modifications);

                if($retour_modification !== true){
                    management('synchronisation_service_element_erreur')->enregistre([
                        'synchronisation_service_element_id' => $this->modele->id,
                        'type_element' => $management_origine->_type_element,
                        'element_id' => $management_origine->modele->id,
                        'type_evenement' => 4,
                        'valeurs_transmises' => json_encode($modifications),
                        'message_erreur' => $retour_modification
                    ]);
                    continue;
                }

                $ids_modifies[] = $management_element->modele->id;
            }

            foreach($elements_a_supprimer as $element_absent){

                $management_element_absent = management($type_element_destination, $element_absent->id, $element_absent);

                $management_element_absent->fonction_a_eviter[] = 'trigger_applicatif';

                $retour_suppression = $management_element_absent->supprime();

                if($retour_suppression !== true){
                    management('synchronisation_service_element_erreur')->enregistre([
                        'synchronisation_service_element_id' => $this->modele->id,
                        'type_element' => $management_origine->_type_element,
                        'element_id' => $management_origine->modele->id,
                        'type_evenement' => 4,
                        'valeurs_transmises' => json_encode(['id' => $element_absent->id]),
                        'message_erreur' => $retour_suppression
                    ]);
                    continue;
                }

                $ids_modifies[] = $element_absent->id;
            }

            if(!empty($ids_modifies))
                management($type_element_destination)->trigger_applicatif_elements_multiples(array_unique($ids_modifies));
        }

        management('synchronisation_service_element_historique')->enregistre([
            'synchronisation_service_element_id' => $this->modele->id,
            'type_element' => $management_origine->_type_element,
            'element_id' => $management_origine->modele->id,
            'type_evenement' => 4,
            'valeurs_transmises' => json_encode($valeurs_champs),
            'valeur_recus' => json_encode($elements_externes)
        ]);

        return true;
    }

    public function synchronisation_lecture_cron(){

        $verrou = \Cache::lock('synchronisation_cron_'.$this->modele->id, 1800);

        try {
            return $verrou->block(10, function(){
                return $this->synchronisation_lecture_cron_verrouillee();
            });
        }
        catch(\Illuminate\Contracts\Cache\LockTimeoutException $exception){
            return traduction('messages.php.synchronisation_service_element.synchronisation_deja_en_cours');
        }
    }

    private function synchronisation_lecture_cron_verrouillee(){

        if($this->modele->type_synchronisation != 3 || empty($this->modele->cron_actif) || !empty($this->modele->desactive))
            return traduction('messages.php.synchronisation_service_element.synchronisation_desactivee');

        $requete_elements = modele($this->modele->type_element);

        $recherche_avancee = modele('recherche_avancee')
            ->where('type', 'synchronisation_service_element')
            ->where('id_cible', $this->modele->id)->first();

        if(!empty($recherche_avancee)){
            $structure = management('recherche_avancee', $recherche_avancee->id, $recherche_avancee)->structure();
            management('recherche_avancee')->applique_filtrage($structure, $requete_elements, $this->modele->type_element);
        }

        $requete_elements->whereRaw('COALESCE('.$this->modele->type_element.'.inactif,0) = 0');

        $delai_secondes = !empty($this->modele->delai_lecture) ? $this->modele->delai_lecture * 3600 : 60;

        $date_limite = date('Y-m-d H:i:s', time() - $delai_secondes);

        $requete_elements->whereNotExists(function($sous_requete) use ($date_limite){
            $sous_requete->select(DB::raw(1))
                ->from('synchronisation_service_element_historique')
                ->whereColumn('synchronisation_service_element_historique.element_id', $this->modele->type_element.'.id')
                ->where('synchronisation_service_element_historique.synchronisation_service_element_id', $this->modele->id)
                ->where('synchronisation_service_element_historique.type_element', $this->modele->type_element)
                ->where('synchronisation_service_element_historique.type_evenement', 4)
                ->where('synchronisation_service_element_historique.cree_le', '>=', $date_limite);
        });

        foreach($requete_elements->get() as $element){

            $management_origine = management($this->modele->type_element, $element->id, $element);

            $this->synchronisation_element($management_origine, true);
        }

        return true;
    }

    public function synchronisation_cron(){

        $verrou = \Cache::lock('synchronisation_cron_'.$this->modele->id, 1800);

        try {
            return $verrou->block(10, function(){
                return $this->synchronisation_cron_verrouillee();
            });
        }
        catch(\Illuminate\Contracts\Cache\LockTimeoutException $exception){
            return traduction('messages.php.synchronisation_service_element.synchronisation_deja_en_cours');
        }
    }

    private function synchronisation_cron_verrouillee(){

        if($this->modele->type_synchronisation != 2 || !empty($this->modele->desactive))
             return traduction('messages.php.synchronisation_service_element.synchronisation_desactivee');

        $table_externe = $this->table_externe();

        $disponibilite = collect($table_externe['disponibilites'] ?? [])->where('type_synchronisation', 2)->first();

        $limite_par_page = $disponibilite['limite_par_appel'] ?? 100;

        $types_evenements = $disponibilite['types_evenements'] ?? [1, 2, 3];

        $methode = 'recuperation_'.$this->modele->type_externe;

        if(!method_exists($this->origine()->service(),$methode))
            return traduction('messages.php.synchronisation_service_element.methode_introuvable');

        synchronisation_service_en_cours(true);

        $fin_synchronisation = false;

        $ids_modifies = [];

        $nombre_elements_recuperes = 0;

        $debut_synchronisation = date('Y-m-d H:i:s');

        $derniere_synchronisation = parametre('synchronisation_cron_'.$this->modele->id) ?? '2020-01-01 00:00:00';

        $champs_evenements = $this->champs()->values();

        $parametres_entree = array_merge($this->parametres_entree_cron($champs_evenements), [
            'derniere_synchronisation' => $derniere_synchronisation
        ]);

        $cle_maj_eden = $champs_evenements->where('cle_mise_a_jour',1)->pluck(function($element){
                return $this->modele->type_element.'.'.$element->nom_sql;
            })->toArray();

        $elements_synchronises_a_supprimer = !in_array(3, $types_evenements) ? collect() : modele($this->modele->type_element)
                ->select($this->modele->type_element.'.*')
                ->where('synchronisation_service_element_historique.synchronisation_service_element_id', $this->modele->id)
                ->join('synchronisation_service_element_historique', $this->modele->type_element.'.id', 'synchronisation_service_element_historique.element_id')
                ->joinSub(modele('synchronisation_service_element_historique')
                    ->select('element_id',DB::raw('MAX(cree_le) as derniere_synchronisation'))
                    ->where('synchronisation_service_element_id', $this->modele->id)
                    ->where('type_element', $this->modele->type_element)
                    ->groupBy('element_id'),'tmp',function($join){
                        $join->on('synchronisation_service_element_historique.element_id', 'tmp.element_id')
                            ->on('synchronisation_service_element_historique.cree_le', 'tmp.derniere_synchronisation');
                })
                ->whereRaw('COALESCE('.$this->modele->type_element.'.inactif,0) = 0')
                ->where('synchronisation_service_element_historique.type_element', $this->modele->type_element)
                ->where('synchronisation_service_element_historique.type_evenement', '!=', 3)
                ->get()->keyBy('id');

        while(!$fin_synchronisation){

            $parametres = array_merge($parametres_entree, [
                'nombre_elements_recuperes' => $nombre_elements_recuperes,
                'limite_par_page' => $limite_par_page
            ]);

            $retour = $this->origine()->service()->{$methode}($parametres);

            if($retour['succes'] === false)
                return $retour['message'];

            $elements_externes = $retour['donnees'];

            $nombre_traites = $retour['nombre_traites'] ?? count($elements_externes);

            $nombre_elements_recuperes += $nombre_traites;

            if(!empty($retour['derniere_synchronisation']))
                $parametres_entree['derniere_synchronisation'] = $retour['derniere_synchronisation'];

            if($nombre_traites < $limite_par_page)
                $fin_synchronisation = true;

            $cles_mise_a_jour_externes = array_map(function($element) use ($champs_evenements){

                $valeurs = [];

                foreach($champs_evenements->where('cle_mise_a_jour',1) as $champ_evenement){

                    $management_champ = management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement);
                    $valeur_externe =  $management_champ->valeur_champ_externe($element);

                    $valeurs[] = $valeur_externe;
                }

                return implode('|',$valeurs);
            },$elements_externes);

            $elements_eden = modele($this->modele->type_element)
                ->avec_inactifs()
                ->select($this->modele->type_element.'.*', \DB::raw("CONCAT(".implode(",'|',", $cle_maj_eden).") as _cle_mise_a_jour"))
                ->whereIn(DB::raw("CONCAT(".implode(",'|',", $cle_maj_eden).")"), $cles_mise_a_jour_externes)
                ->get()
                ->keyBy('_cle_mise_a_jour');

            foreach($elements_externes as $index_element => $element_externe){

                $cle_mise_a_jour = $cles_mise_a_jour_externes[$index_element];

                $modifications = [];

                foreach($champs_evenements as $champ_evenement){

                    $management_champ = management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement);
                    $valeur_externe =  $management_champ->valeur_champ_externe($element_externe);

                    $modifications[$champ_evenement->nom_sql] = $valeur_externe;
                }

                if(!empty($elements_eden[$cle_mise_a_jour])){

                    $modele_element = $elements_eden[$cle_mise_a_jour];

                    unset($elements_synchronises_a_supprimer[$modele_element->id]);

                    if($modele_element->inactif == 1)
                        continue;

                    unset($modele_element->_cle_mise_a_jour);

                    $type_evenement = 2;
                    $management_element = management($this->modele->type_element, $modele_element->id, $modele_element);

                    $modification_utile = false;

                    foreach($modifications as $champ => $valeur){
                        if($management_element->modele->{$champ} != $valeur){
                            $modification_utile = true;
                            break;
                        }
                    }

                    if(!$modification_utile)
                        continue;
                }
                else{
                    $type_evenement = 1;
                    $management_element = management($this->modele->type_element);
                }

                if(!in_array($type_evenement, $types_evenements))
                    continue;

                $management_element->fonction_a_eviter[] = 'trigger_applicatif';

                $retour_modification = $management_element->enregistre($modifications);

                if($retour_modification !== true){
                    management('synchronisation_service_element_erreur')->enregistre([
                        'synchronisation_service_element_id' => $this->modele->id,
                        'type_evenement' => $type_evenement,
                        'valeurs_transmises' => json_encode($modifications),
                        'message_erreur' => $retour_modification
                    ]);

                    continue;
                }

                $ids_modifies[] = $management_element->modele->id;

                management('synchronisation_service_element_historique')->enregistre([
                    'synchronisation_service_element_id' => $this->modele->id,
                    'type_element' => $management_element->_type_element,
                    'element_id' => $management_element->modele->id,
                    'type_evenement' => $type_evenement,
                    'valeurs_transmises' => json_encode($modifications),
                    'valeur_recus' => json_encode($element_externe)
                ]);
            }
        }

        foreach($elements_synchronises_a_supprimer as $element_a_supprimer){

            $management_element = management($this->modele->type_element, $element_a_supprimer->id, $element_a_supprimer);

            $management_element->fonction_a_eviter[] = 'trigger_applicatif';

            $retour_suppression = $management_element->supprime();

            if($retour_suppression !== true){
                management('synchronisation_service_element_erreur')->enregistre([
                    'synchronisation_service_element_id' => $this->modele->id,
                    'type_evenement' => 3,
                    'message_erreur' => $retour_suppression
                ]);

                continue;
            }

            $ids_modifies[] = $element_a_supprimer->id;

            management('synchronisation_service_element_historique')->enregistre([
                'synchronisation_service_element_id' => $this->modele->id,
                'type_element' => $management_element->_type_element,
                'element_id' => $element_a_supprimer->id,
                'type_evenement' => 3,
            ]);
        }

        if(!empty($ids_modifies))
            management($this->modele->type_element)->trigger_applicatif_elements_multiples(array_unique($ids_modifies));

        parametre('synchronisation_cron_'.$this->modele->id, $debut_synchronisation);

        return true;
    }

    private function parametres_entree_cron($champs_evenements){

        $parametres = [];

        foreach($champs_evenements->where('sens',0) as $champ_evenement){

            if(!empty($champ_evenement->valeur_dur))
                $valeurs = $champ_evenement->valeur_dur;
            else{

                if(empty($champ_evenement->nom_sql))
                    continue;

                $valeurs = collect(service('lien_champ')->valeurs($champ_evenement->nom_sql, [
                    'filtrages' => management('synchronisation_service_champs', $champ_evenement->id, $champ_evenement)->filtrages(),
                ]))->pluck('valeur')->filter()->values()->toArray();
            }

            $partie_nom_externe = explode('.', $champ_evenement->nom_externe);

            $nom_externe = array_pop($partie_nom_externe);

            $valeur_a_modifier = &$parametres;

            foreach($partie_nom_externe as $partie){

                if(!isset($valeur_a_modifier[$partie]))
                    $valeur_a_modifier[$partie] = [];

                $valeur_a_modifier = &$valeur_a_modifier[$partie];
            }

            $valeur_a_modifier[$nom_externe] = $valeurs;
        }

        return $parametres;
    }
}