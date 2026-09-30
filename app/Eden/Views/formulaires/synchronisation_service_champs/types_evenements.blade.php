<div class="row" v-if="disponibilites.length > 0">
    <div class="col-sm-2">
        {!! management("synchronisation_service_champs")->champ('types_evenements')->nom_vue() !!}
    </div>
    <div class="col-sm-10 types_evenements">
        <div v-for="disponibilite in disponibilites" class="disponibilite">
            <span v-html="$root.traduction('valeurs_listes_formatees.728.valeur_'+disponibilite.type_evenement)"></span>
            <label class="switch">
                <input type="checkbox" :key="disponibilite.type_evenement" v-if="disponibilite.obligatoire || disponibilites.length == 1" disabled checked>
                <input type="checkbox" :key="disponibilite.type_evenement" name="types_evenements[]" v-else :value="disponibilite.type_evenement" v-model="synchronisation_service_champs.types_evenements">
                <span class="slider round"></span>
                <div v-if="disponibilite.obligatoire" class="champ_obligatoire">*</div>
            </label>
        </div>
        <input type="hidden" name="types_evenements" v-if="synchronisation_service_champs.types_evenements.length == 0 || disponibilites.length <= 1" />
    </div>
</div>

@push('donnees_pour_vuejs_computed')

    disponibilites : function(){

        if(this.champ_externe == null){

            if(this.synchronisation_service_champs.sens == 0)
                return [];

            if(this.table_externe != null){

                var disponibilite = this.table_externe.disponibilites.find(d => d.type_synchronisation == this.synchronisation_service_element.type_synchronisation);

                if(disponibilite.types_evenements == null)
                    return [];

                return disponibilite.types_evenements.map(type_evenement => {
                    return {
                        type_evenement : type_evenement,
                        type_synchronisation : disponibilite.type_synchronisation,
                        obligatoire : false
                    }
                });

            }
            
            return [];
        } 

        return this.champ_externe.disponibilites.filter(d => d.sens == this.synchronisation_service_champs.sens && d.type_synchronisation == this.synchronisation_service_element.type_synchronisation);
    }, 
@endpush

@push('donnees_pour_vuejs_watch')

    disponibilites : function(){

        if(this.champ_externe == null)
            return;
        
        if(this.disponibilites.length > 1){
            if(this.synchronisation_service_champs.types_evenements.length == 0)
                this.synchronisation_service_champs.types_evenements = this.disponibilites.map(d => d.type_evenement);
        }
        else
            this.synchronisation_service_champs.types_evenements = [];
    }
@endpush