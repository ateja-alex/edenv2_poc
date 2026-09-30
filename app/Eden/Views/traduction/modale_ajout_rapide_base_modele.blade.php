<span data-toggle="tooltip" data-placement="left" @click="ajout_traduction_rapide" data-original-title="Ajout de traduction sur la base modèle" class="css_ajouter_element ml-2">
    <i aria-hidden="true" class="css_action_icon css_font_16 fas fa-database"></i>
</span>

@push('modales')
    <template v-if="modale_ajout_rapide_traduction">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.modal_ajout_rapide_base_modele.titre')</h5>
                        </div>

                        <div class="modal-body css_form js_selection_element" >

                            <div class="row">
                                <div class="col-sm-2">@traduction('interface.modal_ajout_rapide_base_modele.champs.langue_principale')</div>
                                <div class="col-sm-4">
                                    <select @change="nouvel_element.langue = element_ajout_rapide.langue_principale" v-model="element_ajout_rapide.langue_principale">
                                        <option v-for="langue in langues_traduction_erp" :value="langue.code">@{{ langue.nom }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">@traduction('interface.modal_ajout_rapide_base_modele.champs.categorie_principale')</div>
                                <div class="col-sm-4">
                                    <select @change="nouvel_element.categorie = element_ajout_rapide.categorie_principale" v-model="element_ajout_rapide.categorie_principale">
                                        <option v-for="categorie_traduction in valeurs_listes_formatees[590]" :value="categorie_traduction.id_valeur">@{{ categorie_traduction.valeur }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">@traduction('interface.modal_ajout_rapide_base_modele.champs.modele_index')</div>
                                <div class="col-sm-4">
                                    <input type="text" @change="nouvel_element.index.valeur = modele_index" v-model="modele_index">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 css_form_ligne_titre">
                                    @traduction('interface.modal_ajout_rapide_base_modele.titre_categorie.valeurs')
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <table style="width: 100%;" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.colonne.index')</th>
                                                <th>@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.colonne.valeur')</th>
                                                <th style="width: 50px;">@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.colonne.langue')</th>
                                                <th style="width: 50px;">@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.colonne.categorie')</th>
                                                <th style="width: 50px;">@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.colonne.options')</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(element,index_element) in element_ajout_rapide.elements">
                                                <td v-for="(osef,index_element) in element">
                                                    <textarea v-if="element[index_element].textarea === true" @dblclick="element[index_element].textarea = false;" v-model="element[index_element].valeur"></textarea>
                                                    <input v-else type="text" @dblclick="element[index_element].textarea = true;" v-model="element[index_element].valeur" />
                                                </td>
                                                <td>
                                                    <i class="css_pointer fas fa-trash" @click="element_ajout_rapide.elements.splice(index_element,1)"></i>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td v-for="(osef,index_nouvel_element) in nouvel_element">
                                                    <textarea v-if="nouvel_element[index_nouvel_element].textarea === true" @dblclick="nouvel_element[index_nouvel_element].textarea = false;" @change="ajout_element_traduction" v-model="nouvel_element[index_nouvel_element].valeur"></textarea>
                                                    <input @change="ajout_element_traduction"  v-else type="text" @dblclick="nouvel_element[index_nouvel_element].textarea = true;" v-model="nouvel_element[index_nouvel_element].valeur" />
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_ajout_rapide_traduction = false">@traduction('interface.modales.fermer')</button>
                            <button type="button" class="btn btn-primary" @click="envoie_base_modele">@traduction('interface.modal_ajout_rapide_base_modele.tableau_valeurs.bouton.envoyer_sur_la_base_modele')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')

    element_ajout_rapide:{
        langue_principale : '',
        categorie_principale : 0,
        elements:[],
    },
    nouvel_element:{
        index : {
            valeur : '',
            textarea : false,
        },
        valeur : {
            valeur : '',
            textarea : false,
        },
        langue : {
            valeur : '',
            textarea : false,
        },
        categorie : {
            valeur : '',
            textarea : false,
        }
    },
    modele_index:'',
    modale_ajout_rapide_traduction: false,
@endpush

@push('donnees_pour_vuejs_methods')

    ajout_traduction_rapide: function(){

        this.element_ajout_rapide.categorie_principale = 0;

        if(this.categorie != null)
            this.element_ajout_rapide.categorie_principale = this.categorie;

        this.nouvel_element.categorie = {
            valeur : this.element_ajout_rapide.categorie_principale,
            textarea : false,
        };

        this.modale_ajout_rapide_traduction = true;
    },

    ajout_element_traduction: function(){

        var ajout_element = true;

        $.each(this.nouvel_element,function(index,element){

            if(element.valeur == '')
                ajout_element = false;
        });

        if(ajout_element){
            this.element_ajout_rapide.elements.push(JSON.parse(JSON.stringify(this.nouvel_element)));
            this.nouvel_element.index = {
                valeur : this.modele_index,
                textarea : false
            };
            this.nouvel_element.valeur = {
                valeur : '',
                textarea : false
            };
            this.nouvel_element.langue = {
                valeur : this.element_ajout_rapide.langue_principale,
                textarea : false
            };
            this.nouvel_element.categorie = {
                valeur : this.element_ajout_rapide.categorie_principale,
                textarea : false
            };
        }
    },

    envoie_base_modele : function(){

        var vue_instance = this;

        loading(true);

        var elements = [];

        vue_instance.element_ajout_rapide.elements.forEach(function(element){

            var element_formate = {};

            $.each(element,function(index_valeurs,valeurs){
                element_formate[index_valeurs] = valeurs.valeur;
            });

            elements.push(element_formate);
        });

        $.post({
            url : '{{URL::to('eden/maintenance/traduction/envoie_base_modele')}}',
            dataType : 'json',
            data : {
                elements : elements,
            }
        }).done(async function(donnees){

            loading(false);

            if(donnees.retour !== true) {

                await alerte_eden(donnees.message);
                return;
            }

            vue_instance.modale_ajout_rapide_traduction = false;

            vue_instance.element_ajout_rapide.elements = [];

            vue_instance.$refs.traduction_table.charger_traductions();
        });
    },
@endpush

@push('donnees_pour_vuejs_mounted')
    this.element_ajout_rapide.langue_principale = this.langues_traduction_erp[0].code;

    this.nouvel_element.langue = {
        valeur : this.element_ajout_rapide.langue_principale,
        textarea : false
    };
@endpush