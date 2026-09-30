<div class="synchronisation_service_champ" v-if="synchronisation_service_element?.type_externe ?? false">
    <div class="bloc_champ">
        {!! management("synchronisation_service_champs")->champ('nom_sql')->nom_vue() !!}
        <div>
            <select-champs-libres                           
                :champs_libres="[{ 
                    type_element : type_element_champs_destination,
                    index_traduction : 'tables_libres.'+(type_element_champs_destination ?? '')+'.nom_table',
                    champs_libres : champs_libres
                }]"
                :nom_sql="synchronisation_service_champs.nom_sql"
                :type_element_origine="type_element_champs_destination"
                :type_element="type_element_champs_destination"
                @changement_select_champs_libres="synchronisation_service_champs.nom_sql = $event.nom_sql">
            ></select-champs-libres>
            <input type="hidden" name="nom_sql" v-model="synchronisation_service_champs.nom_sql">
        </div>
    </div>
    <template v-if="champ_libre_selectionne != null">
        <div class="bloc_signe_egal">
            <i class="fas fa-equals"></i>
        </div>
        <div class="bloc_champ">
            {!! management("synchronisation_service_champs")->champ('nom_externe')->nom_vue() !!}
            <div class="champ_valeur">
                <span>
                    <select v-if="champs_externes_a_afficher.length > 0 && (synchronisation_service_champs.valeur_dur == '' || synchronisation_service_champs.valeur_dur == null)" v-model="synchronisation_service_champs.nom_externe" name="nom_externe">
                        <option value="" v-html="$root.traduction('interface.formulaires.synchronisation_service_champs.choisir')"></option>
                        <template v-for="champ in champs_externes_a_afficher">
                            <optgroup v-if="champ.categorie" :label="champ.nom">
                                <option v-for="valeur in champ.valeurs" :value="champ.nom+'.'+valeur.nom" v-html="valeur.nom"></option>
                            </optgroup>
                            <option v-else :value="champ.nom" v-html="champ.nom"></option>
                        </template>
                    </select>
                    <input type="hidden" name="correspondances" v-model="synchronisation_service_champs.correspondances">
                    <a href="javascript:;" @click="gestion_correspondances()" v-if="champ_externe != null && champ_externe.valeurs && synchronisation_service_champs.nom_sql != null && synchronisation_service_champs.nom_sql != ''">
                        @traduction('interface.formulaires.synchronisation_service_champs.correspondances')
                    </a>
                </span>
                <span v-if="champs_externes_a_afficher.length > 0 && (synchronisation_service_champs.nom_externe == null || synchronisation_service_champs.nom_externe == '') && (synchronisation_service_champs.valeur_dur == '' || synchronisation_service_champs.valeur_dur == null)"> OU </span>
                <input v-if="synchronisation_service_champs.nom_externe == null || synchronisation_service_champs.nom_externe == ''" type="text" v-model="synchronisation_service_champs.valeur_dur" name="valeur_dur">
            </div>
        </div>
    </template>
</div>

@include('eden::formulaires.synchronisation_service_champs.parametrage')

@push('donnees_pour_vuejs_computed')
    champs_externes_a_afficher(){
        return this.champs_externes.filter(champ_externe => {

            if(champ_externe.disponibilites.filter(d => d.type_synchronisation == this.synchronisation_service_element.type_synchronisation && d.sens == this.synchronisation_service_champs.sens).length == 0)
                return false;

            for(condition_type of champ_externe.types){

                var condition_rempli = true;

                for(champ in condition_type){

                    var valeur_condition = condition_type[champ];

                    if(Array.isArray(valeur_condition)){
                        if(!valeur_condition.includes(this.champ_libre_selectionne[champ])){
                            condition_rempli = false;
                            break;
                        }
                    }
                    else if(this.champ_libre_selectionne[champ] != valeur_condition){
                        condition_rempli = false;
                        break;
                    }
                }

                if(condition_rempli == true)
                    return true;
            }

            return false;
        });
    },

    champ_libre_selectionne(){
        return this.champs_libres.find(champ_libre => champ_libre.nom_sql == this.synchronisation_service_champs.nom_sql);
    },

    type_element_champs_destination(){
        return (this.synchronisation_service_element != null && this.synchronisation_service_element.type_synchronisation == 3)
            ? this.synchronisation_service_element.type_element_destination
            : (this.synchronisation_service_element?.type_element ?? null);
    },

@endpush