@if(isset($include_depuis_fiche) && $include_depuis_fiche === true)

    <i class="css_action_icon primaire fa fa-fw fa-trash" @click="element_modale_suppression = {{ $type_element_options }};modal_options_suppression = true" title="{{ traduction('module_sur_fiche.fiche.element.supprimer') }}" data-placement="left" data-toggle="tooltip"></i>

@endif

@push('modales')
    <template v-if="modal_options_suppression">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog " role="document">
                    <div class="modal-content" style="min-height: 600px">
                        <div class="modal-header ">
                            <div class="modal-title">
                                <h5>
                                    @traduction('module_sur_fiche.include.modale_suppression.titre')
                                </h5>
                            </div>
                        </div>
                        <div class="modal-body css_form">
                            <div class="row" >
                                <div class="col-md-12" v-if="etape_suppression === ''">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>@traduction('module_sur_fiche.include.modale_suppression.texte')</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success" @click="etape_suppression = 'contact_gestion_ok'">@traduction('module_sur_fiche.include.modale_suppression.oui')</button>
                                            <button type="button" class="btn btn-success" @click="etape_suppression = 'adresses_gestion'">@traduction('module_sur_fiche.include.modale_suppression.non')</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" v-if="etape_suppression === 'contact_gestion_ok'">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>@traduction('module_sur_fiche.include.modale_suppression.gestion_contact')</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success" @click="gestion_contacts = 'supprimer'; etape_suppression = 'adresses_gestion'">@traduction('module_sur_fiche.include.modale_suppression.supprimer_contacts')</button>
                                            <button type="button" class="btn btn-success" @click="gestion_contacts = 'contact_gestion_transfert'">@traduction('module_sur_fiche.include.modale_suppression.transferer_contacts')</button>
                                        </div>
                                    </div>
                                    <div class="row" v-if="gestion_contacts === 'contact_gestion_transfert'" style="padding-top: 20px">
                                        <div class="col-md-2">
                                            @traduction('module_sur_fiche.include.modale_suppression.transferer_vers') :
                                        </div>
                                        <div class="col-md-10">
                                            <champ-selection-element type_element_origine="contact"
                                                                     :type_element="type_element"  desactiver_creation_a_la_volee="true"
                                                                     format_champ="" :nom_sql="type_element + '_id'"
                                                                     :modele="contact_transfert" :filtrage="[{'champ' : 'id_element_pour_transfert','valeur' :element_modale_suppression.id},{'champ':'id','condition' : 'where','valeur':element_modale_suppression.id, 'symbole' : '!='}]" >
                                            </champ-selection-element>
                                        </div>
                                    </div>
                                    <div class="row" v-if="gestion_contacts === 'contact_gestion_transfert' && contact_transfert[type_element + '_id'] != 0" style="padding-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success" @click="gestion_contacts = 'transfert'; etape_suppression = 'adresses_gestion'">@traduction('module_sur_fiche.include.modale_suppression.suivant')</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" v-if="etape_suppression === 'adresses_gestion'">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>@traduction('module_sur_fiche.include.modale_suppression.gestion_adresses')</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success" @click="etape_suppression = 'adresses_gestion_ok'">@traduction('module_sur_fiche.include.modale_suppression.oui')</button>
                                            <button type="button" class="btn btn-success" @click="etape_suppression = 'adresses_gestion'; suppression_element_avec_options()">@traduction('module_sur_fiche.include.modale_suppression.non')</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12" v-if="etape_suppression === 'adresses_gestion_ok'">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>@traduction('module_sur_fiche.include.modale_suppression.choix_adresses_element')</h6>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-success" @click="gestion_adresses = 'supprimer'; suppression_element_avec_options()">@traduction('module_sur_fiche.include.modale_suppression.supprimer_adresses')</button>
                                            <button type="button" class="btn btn-success" @click="gestion_adresses = 'adresses_gestion_transfert'">@traduction('module_sur_fiche.include.modale_suppression.transfert_adresses')</button>
                                        </div>
                                    </div>
                                    <div class="row" v-if="gestion_adresses === 'adresses_gestion_transfert'" style="padding-top: 20px">
                                        <div class="col-md-2">
                                            @traduction('module_sur_fiche.include.modale_suppression.transferer_vers') :
                                        </div>
                                        <div class="col-md-10">
                                            <champ-selection-element type_element_origine="adresse"
                                                                     :type_element="type_element"  desactiver_creation_a_la_volee="true"
                                                                     format_champ="" :nom_sql="type_element + '_id'"
                                                                     :modele="adresse_transfert" :filtrage="[{'champ' : 'id_element_pour_transfert','valeur' :element_modale_suppression.id},{'champ':'id','condition' : 'where','valeur':element_modale_suppression.id, 'symbole' : '!='}]" >
                                            </champ-selection-element>
                                        </div>
                                    </div>
                                    <div class="row" v-if="gestion_adresses === 'adresses_gestion_transfert' && adresse_transfert[type_element + '_id'] != 0" style="padding-top: 10px">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success" @click="gestion_adresses = 'transfert'; suppression_element_avec_options()">@traduction('module_sur_fiche.include.modale_suppression.terminer')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="modal_options_suppression = false">{{traduction('interface.modales.fermer')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush
@push('donnees_pour_vuejs_data')
    etape_suppression: '',
    gestion_contacts: '',
    gestion_adresses: '',
    contact_transfert: {!! modele_par_defaut("contact") !!},
    adresse_transfert: {!! modele_par_defaut("adresse") !!},
    modal_options_suppression: false,
    element_modale_suppression: {},
    id_liste_element: 0,
@endpush

@push('donnees_pour_vuejs_methods')

    async suppression_element_avec_options() {

        //this.modal_options_suppression = false;
        //this.etape_suppression = '';
        var retour_post_suppression = true;

        // A ce stade, au moins 1 des éléments à besoin d'un traitement particulier

        if(this.id_liste_element === 0)
            retour_post_suppression = await this.suppression_fiche(this.type_element,this.element_id);
        else{

            retour_post_suppression = await this.supprimer_dans_liste(this.element_modale_suppression.id);
            this.modal_options_suppression = false;
        }

        if(retour_post_suppression != true)
            return false;

        // Cas où on ne gère rien, on évite de faire des vérifications inutiles dans ce cas
        if(this.gestion_contacts === '' && this.gestion_adresses === '' && this.id_liste_element === 0)
            return false;

        else if(this.gestion_contacts === '' && this.gestion_adresses === '' && this.id_liste_element !== 0)
            return false;

        // On s'occupe des contacts
        if(this.gestion_contacts !== '')
            this.gestion_transfert_suppression('contact');

        if(this.gestion_adresses !== '')
            this.gestion_transfert_suppression('adresse');

    },

    gestion_transfert_suppression(type_element){

        var vue_composant = this;

        var action = "";
        if(type_element === 'contact')
            action = this.gestion_contacts;

        else
            action = this.gestion_adresses;

        var transfert_vers = "";
        if(type_element === 'contact')
            transfert_vers = this.contact_transfert[vue_composant.type_element + '_id'];

        else
            transfert_vers = this.adresse_transfert[vue_composant.type_element + '_id'];

        // on enregistre la modification
        $.post({

            url: "/eden/fiche/" + vue_composant.type_element + "/gestion_options_suppression",
            dataType: "json",
            method: 'POST',
            data: {
                element_id: vue_composant.element_modale_suppression.id,
                type_element_a_gerer: type_element,
                action: action,
                transfert: transfert_vers,
            }
        });

        return true
    },

    suppression_element_depuis_liste(id_element, id_liste){

        var vue_instance = this;

        loading(true);

        // on enregistre la modification
        $.ajax({

            url: "/eden/element/" + vue_instance.liste.type_element + "/"+id_element,
            dataType: "json",
            method: 'GET',
            data: {
            }
        }).done(function(retour) {

            vue_instance.element_modale_suppression = retour;
            vue_instance.id_liste_element = id_liste;
            vue_instance.etape_suppression = '';
            vue_instance.gestion_contacts = '';
            vue_instance.gestion_adresses = '';
            vue_instance.contact_transfert = {!! modele_par_defaut("contact") !!};
            vue_instance.adresse_transfert = {!! modele_par_defaut("adresse") !!};

            loading(false);

            vue_instance.modal_options_suppression = true;
        });

    },
@endpush