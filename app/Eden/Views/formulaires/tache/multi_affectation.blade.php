<div class="row" style="margin-top: 5px;">
    <div class="col-sm-2">
        @traduction('formulaire.tache.multi_affectation.nom')
    </div>
    <div class="col-sm-6" v-if="tache.id >0">
        {!! management('tache')->champ('affectation')->cree() !!}
    </div>
    <div class="col-sm-10" v-else>
        <champ-multiple :modele="tache" composant_enfant="champ-selection-element" 
            :composant_enfant_props="{
                type_element_origine: 'tache',
                type_element: 'utilisateur',
                nom_sql: 'affectations',
                modele : tache,
                ref: 'affectations',
                name: 'utilisateurs_id',
                desactiver_creation_a_la_volee: true,
            }"></champ-multiple>
    </div>
</div>
<div class="row" style="margin-top: 5px;" v-if="!_.isEmpty(autres_affectations)">
    <div class="col-sm-2">@traduction('formulaire.tache.multi_affectation.autres_affectations')</div>
    <div class="col-sm-10">
        <span v-text="Object.values(autres_affectations).join(', ')"></span>
    </div>
</div>

@push('donnees_pour_vuejs_data')

    utilisateurs_par_equipe_pour_affectation: {},
    autres_affectations: {},
@endpush

@push('donnees_pour_vuejs_methods')

    recupere_autres_affectations: async function(tache_id = null) {

        if(tache_id === null)
            tache_id = this.tache.id;

        await $.post({

            url: "{{ url('/eden/tache/') }}/" + tache_id + "/autres_affectations",
        }).done((retour) => {

            this.$set(this, 'autres_affectations', retour);
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')

    var instance = this;

    if((this.tache.id == false || this.tache.id == undefined) && this.tache.affectations != undefined && this.tache.affectations.length == 0){
        
        this.tache.affectations.push(this.tache.affectation ? this.tache.affectation : this.$root.moi.id);
    }
    else if(this.tache.id != false && this.tache.groupe_affectations != false)
        this.recupere_autres_affectations();

    this.$parent.$on('enregistrement_et_duplication', async (element) => {

        await this.recupere_autres_affectations(element.id);

        this.$set(this.tache, 'affectations', Object.keys(this.autres_affectations).concat(this.tache.affectation));
    })
@endpush