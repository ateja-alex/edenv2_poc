<div class="synchronisation_service_champ" v-if="synchronisation_service_element?.type_externe ?? false">
    <div class="bloc_champ">
        {!! management("synchronisation_service_champs")->champ('nom_externe')->nom_vue() !!}
        <div>
            <select v-model="synchronisation_service_champs.nom_externe" name="nom_externe" @change="cle_champ_externe++;synchronisation_service_champs.types_evenements = [];">
                <option value="" v-html="$root.traduction('interface.formulaires.synchronisation_service_champs.choisir')"></option>
                <template v-for="champ in champs_externes.filter(c => c.disponibilites.filter(d => d.type_synchronisation == synchronisation_service_element.type_synchronisation && d.sens == synchronisation_service_champs.sens).length > 0)">
                    <optgroup v-if="champ.categorie" :label="champ.nom">
                        <option v-for="valeur in champ.valeurs" :value="champ.nom+'.'+valeur.nom" v-html="valeur.nom"></option>
                    </optgroup>
                    <option v-else :value="champ.nom" v-html="champ.nom"></option>
                </template>
            </select>
        </div>
    </div>
    <template v-if="champ_externe != null">
        <div class="bloc_signe_egal">
            <i class="fas fa-equals"></i>
        </div>
        <div class="bloc_champ">
            {!! management("synchronisation_service_champs")->champ('nom_sql')->nom_vue() !!}
            <div class="champ_valeur">
                <span>
                    <parametrage-lien-champ
                        ref="parametrage_lien_champ"
                        v-if="synchronisation_service_champs.valeur_dur == null || synchronisation_service_champs.valeur_dur == ''"
                        :lien_champ="synchronisation_service_champs.nom_sql"
                        @changement_lien_champ="synchronisation_service_champs.nom_sql = $event;champ_libre_selectionne = $refs.parametrage_lien_champ.champ_selectionne;synchronisation_service_champs.correspondances = null;"
                        @changement_filtrages="filtrages = $event"
                        @initialisation_terminee="champ_libre_selectionne = $refs.parametrage_lien_champ.champ_selectionne;"
                        :type_element="synchronisation_service_element.type_synchronisation == 2 ? null : synchronisation_service_element.type_element"
                        :filtres_valeur_final="{
                            champs : champ_externe?.types ?? [],
                        }"
                        :recherches_avancees="recherches_avancees"
                        :key="cle_champ_externe"
                        :valeur_unique="synchronisation_service_element.type_synchronisation != 2"></parametrage-lien-champ>
                    <input type="hidden" name="nom_sql" v-model="synchronisation_service_champs.nom_sql">
                    <input type="hidden" name="correspondances" v-model="synchronisation_service_champs.correspondances">
                    <input type="hidden" v-for="filtrage in filtrages" name="filtrages[]" :value="JSON.stringify(filtrage)">
                    <a href="javascript:;" @click="gestion_correspondances()" v-if="champ_externe != null && champ_externe.valeurs && synchronisation_service_champs.nom_sql != null && synchronisation_service_champs.nom_sql != ''">
                        @traduction('interface.formulaires.synchronisation_service_champs.correspondances')
                    </a>
                </span>
                <span v-if="(synchronisation_service_champs.nom_sql == null || synchronisation_service_champs.nom_sql == '') && (synchronisation_service_champs.valeur_dur == '' || synchronisation_service_champs.valeur_dur == null)"> OU </span>
                <input v-if="synchronisation_service_champs.nom_sql == null || synchronisation_service_champs.nom_sql == ''" type="text" v-model="synchronisation_service_champs.valeur_dur" name="valeur_dur">
            </div>
        </div>
    </template>
</div>

@include('eden::formulaires.synchronisation_service_champs.parametrage')

@push('donnees_pour_vuejs_data')
    cle_champ_externe : 0,
    champ_libre_selectionne: null,
    filtrages : [],
    recherches_avancees : [],
@endpush

@push('donnees_pour_vuejs_mounted')
    if(this.synchronisation_service_champs.id != null)
        this.recherches_avancees = await $.ajax({
            url : 'eden/recherche_avancee/synchronisation_service_champs_'+this.synchronisation_service_champs.id+'/recherche_type',
            dataType : 'json',
        });
@endpush
