<i class="css_action_icon primaire fas fa-handshake" 
    :title="$root.traduction('module_sur_fiche.fiche.client.fusionner_fiche')" 
    @click="affichage_modale_fusion_fiche()"
    data-toggle="tooltip">
</i>

@push('modales')
<template v-if="modale_fusion_fiche">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-dialog " role="document">
                <div class="modal-content" >
                    <div class="modal-header ">
                        <div class="modal-title">
                            <h5>
                                @traduction('module_sur_fiche.client.modale_fusion.titre')
                            </h5>
                        </div>
                    </div>
                    <div class="modal-body css_form">
                        <div class="row">
                            <div class="col-sm-4">@traduction('module_sur_fiche.client.modale_fusion.fiche_source')</div>
                            <div class="col-sm-8">
                                <champ-selection-element
                                    :type_element="this.type_element"  :desactiver_creation_a_la_volee="true"
                                    :modele="element_fusion"
                                    nom_sql="fiche_source"
                                    :lecture_seule="true" >
                                </champ-selection-element>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">@traduction('module_sur_fiche.client.modale_fusion.fiche_destinataire')</div>
                            <div class="col-sm-8">
                                <champ-selection-element
                                    :type_element="this.type_element"  :desactiver_creation_a_la_volee="true"
                                    :modele="element_fusion"
                                    nom_sql="fiche_destinataire"
                                    :filtrage="[{'champ':'id','condition' : 'where','valeur':element_id, 'symbole' : '!='}]" >
                                </champ-selection-element>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <b>@traduction('module_sur_fiche.client.modale_fusion.explications_1') : </b> @traduction('module_sur_fiche.client.modale_fusion.explications_2') <br/>@traduction('module_sur_fiche.client.modale_fusion.explications_3')<br/> @traduction('module_sur_fiche.client.modale_fusion.explications_4')
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">

                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>@traduction('module_sur_fiche.client.modale_fusion.element')</th>
                                        <th>@traduction('module_sur_fiche.client.modale_fusion.nombre_elements')</th>
                                        <th>@traduction('module_sur_fiche.client.modale_fusion.transferer')</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(element, type_element) in elements_a_fusionner">
                                        <td v-text="element.nom"></td>
                                        <td v-text="element.nombre"></td>
                                        <td>
                                            <label class="switch">
                                                <input type="checkbox" :value="type_element" v-model="element_fusion.elements_a_fusionner" />
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="modale_fusion_fiche = false" data-dismiss="modal">{{traduction('interface.modales.fermer')}}</button>
                        <button type="button" class="btn btn-success" @click="fusionner_fiche">@traduction('module_sur_fiche.client.modale_fusion.fusionner')</button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')
    elements_a_fusionner:[],
    modale_fusion_fiche: false,
    element_fusion:{
        fiche_source: {{$management_element->modele->id}},
        fiche_destinataire: null,
        elements_a_fusionner: [],
    },
@endpush

@push('donnees_pour_vuejs_methods')

    affichage_modale_fusion_fiche() {

        loading(true);
       
        $.post({
            url: "{{ route('base_eden.fiche.index_post', ['type_element' => $management_element->_type_element, 'id' => $management_element->modele->id, 'methode' => 'recuperer_nombre_elements_a_fusionner']) }}",
            dataType: "json",
        }).done((donnees) => {

            this.elements_a_fusionner = donnees;
            this.modale_fusion_fiche = true;
            loading(false);
        });
    },

    fusionner_fiche() {

        loading(true);
        // on va chercher le template
        $.post({

            url: "{{ route('base_eden.fiche.index_post', ['type_element' => $management_element->_type_element, 'id' => $management_element->modele->id, 'methode' => 'fusionner_fiche']) }}",
            dataType: "json",
            method: "post",
            data: this.element_fusion
        }).done(async function(donnees) {

            loading(false);

            if(donnees.retour !== true) {

                await erreur(donnees.message);
                return;
            }

            window.location = donnees.route;

        });
    },
@endpush