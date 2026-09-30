<div class="btn-group">
    <i class="css_action_icon primaire fa fa-fw fa-file-invoice" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
    <div class="dropdown-menu">
        <span v-for="modele in modeles_transformation_document_temps"
              @click="initialisation_transformation_document(modele)" class="dropdown-item">@{{modele.nom}}</span>
    </div>
</div>

@push('modales')
    <template v-if="modale_transformation_document">
        <transition name="modal" >
            <div class="modal-mask modale_transformation_document">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.modale_transformation_document.transformation_document_temps')</h5>
                            <button type="button" class="close" @click="modale_transformation_document = false;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body css_form">
                            <div class="row">
                                <div class="col-sm-12">
                                    <filtre-date :valeurs="valeurs_filtres_temps" :filtre="{id :'filtre_date_temps'}"></filtre-date>
                                </div>
                            </div>
                            <div class="row" v-if="utilisateurs_impactes === false && chargement_utilisateurs !== false">
                                <div class="col-sm-12 text-center">
                                    <img style="width: 50px" src="{{asset('eden/images/ajax_loader.gif')}}" />
                                </div>
                            </div>
                            <div class="row" v-if="utilisateurs_impactes !== false && utilisateurs_impactes.length == 0">
                                <div class="col-sm-12 alert alert-danger">
                                    @traduction('interface.modale_transformation_document.transformation_impossible')
                                </div>
                            </div>
                            <div class="row" v-if="utilisateurs_impactes !== false && utilisateurs_impactes.length > 0">
                                <div class="col-sm-2">
                                    @traduction('interface.modale_transformation_document.utilisateurs_a_transformer')
                                </div>
                                <div class="col-sm-10" style="display: flex;gap: 5px;">
                                    <div v-for="utilisateur in utilisateurs_impactes"
                                         @click="utilisateurs_a_transformer.includes(utilisateur.id) ?
                                         utilisateurs_a_transformer.splice(utilisateurs_a_transformer.indexOf(utilisateur.id),1) :
                                         utilisateurs_a_transformer.push(utilisateur.id)"
                                         :style="(utilisateurs_a_transformer.includes(utilisateur.id) ? 'background: limegreen;color: white;' : 'background: lightgrey;')+'border-radius: 5px;cursor: pointer;padding: 10px;'" v-html="utilisateur.chaine_affichage"></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_transformation_document = false;">@traduction('interface.modales.fermer')</button>
                            <a type="button" class="btn btn-primary" v-if="utilisateurs_a_transformer.length > 0" :href="url_transformation_document">@traduction('interface.modale_transformation_document.transformer')</a>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    modeles_transformation_document_temps : {!! collect($modeles) !!},
    modale_transformation_document : false,
    valeurs_filtres_temps:{
        variable : null,
        debut : null,
        fin : null,
    },
    chargement_utilisateurs : false,
    utilisateurs_a_transformer : [],
    utilisateurs_impactes : false,
    modele_id_transformation_temps : null,
@endpush

@push('donnees_pour_vuejs_mounted')

    this.$on('changement_filtre',(parametres) => {

        this.valeurs_filtres_temps = parametres.valeurs;
        this.chargement_temps_utilisateurs();
    });
@endpush

@push('donnees_pour_vuejs_methods')
    initialisation_transformation_document : function(modele){
        this.modale_transformation_document = true;
        this.modele_id_transformation_temps = modele.id;
        this.valeurs_filtres_temps = {
            variable : null,
            debut : null,
            fin : null,
        };
        this.utilisateurs_a_transformer = [];
        this.utilisateurs_impactes = false;
    },

    chargement_temps_utilisateurs : function(){

        if(this.chargement_utilisateurs !== false)
            this.chargement_utilisateurs.abort();

        this.utilisateurs_impactes = false;

        this.chargement_utilisateurs = $.post({
            url: 'eden/transformation_document_temps/calcul_utilisateurs_concernes',
            data : {
                dates : this.valeurs_filtres_temps,
                type_element : this.type_element,
                element_id: this.element_id
            }
        }).done((donnees) => {

            this.chargement_utilisateurs = false;
            this.utilisateurs_a_transformer = donnees.utilisateurs_a_transformer.map(utilisateur => utilisateur.id);
            this.utilisateurs_impactes = donnees.utilisateurs_a_transformer;
        });

    },

@endpush

@push('donnees_pour_vuejs_computed')

    url_transformation_document : function () {

        var url = new URL("{{route('transformation_document_temps.transformation')}}");

        var parametres = new URLSearchParams({
            parametres : btoa(JSON.stringify({
                dates : this.valeurs_filtres_temps,
                type_element : this.type_element,
                element_id: this.element_id,
                utilisateurs_a_transformer: this.utilisateurs_a_transformer,
                modele_id_transformation_temps: this.modele_id_transformation_temps,
            }))
        });

        url.search = parametres.toString();

        return url.toString();
    },
@endpush