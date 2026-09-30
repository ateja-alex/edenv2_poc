@if(!empty($conversions))
    <div class="btn-group">
        <i class="css_action_icon primaire fa fa-fw fa-plus-circle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
        <div class="dropdown-menu">
            @foreach($conversions as $id => $conversion)
                <span class="dropdown-item" @click="convertir({{$id}}, '{{$conversion}}')">{{ traduction('module_sur_fiche.fiche.convertir') }} {{ strtolower(traduction('tables_libres.' . $conversion . '.element')) }}</span>
            @endforeach
        </div>
    </div>

    @push('modales')
        <template v-if="modale_conversion_reussie">
            <transition name="modal" >
                <div class="modal-mask">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('module_sur_fiche.modale_conversion.titre')</h5>
                                <button type="button" class="close" @click="modale_conversion_reussie = false"  aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body css_form">
                                <span id="titre_modale_conversion_reussie">@traduction('module_sur_fiche.modale_conversion.element_generes')</span>
                                <div v-for="(elements, type_element) in elements_generes">
                                    <div id="elements_modale_conversion_reussie" v-for="(element, champ_liaison) in elements">
                                        <span>@{{ traduction('tables_libres.' + type_element + '.element') }} <span v-if="typeof champ_liaison == 'string'">(@{{ traduction('champs_libres.' + type_element + '.' + champ_liaison + '.nom') }})</span> : </span>
                                        <span v-html="element.affiche_lien"></span>
                                    </div>
                                </div>
                                <div v-if="erreur_conversion != ''">
                                    @{{ erreur_conversion }}
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="modale_conversion_reussie = false">@traduction('interface.listes.fermer')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>

        <template v-if="$root.affichage_modale_conversion_formulaire">
            <transition name="modal" >
                <div class="modal-mask modale_conversion">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('module_sur_fiche.modale_conversion.titre')</h5>
                                <button type="button" class="close" @click="$root.affichage_modale_conversion_formulaire = false"  aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body css_form">
                                <formulaire ref="formulaire_conversion" :nom_formulaire="nom_formulaire"></formulaire>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary css_btn_responsive" @click="enregistrer_formulaire_conversion()">
                                    @traduction('interface.listes.enregistrer')
                                </button>
                                <button type="button" class="btn btn-secondary" @click="$root.affichage_modale_conversion_formulaire = false">@traduction('interface.listes.fermer')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
    @endpush
    @push('donnees_pour_vuejs_data')

        modale_conversion_reussie : false,
        modale_conversion_formulaire : {
            template: '<div></div>',
        },
        affichage_modale_conversion_formulaire : false,
        elements_generes : {},
        erreur_conversion : '',
        nom_formulaire: '',
    @endpush

    @push('donnees_pour_vuejs_methods')

        convertir : function(id, type_element_arrivee){

            loading(true);
            this.elements_generes = {};
            this.erreur_conversion = '';
            this.modale_conversion_formulaire = '';

            $.ajax({

                url: "{{ route('base_eden.element.convertir') }}",
                dataType: "json",
                method: 'post',
                data: {
                    type_element_depart:this.$root.type_element,
                    element_id:this.$root.element_id,
                    type_element_arrivee:type_element_arrivee,
                    mappage_table_id :id,
                }
            }).done((retour) => {

                loading(false);

                if(retour.succes == false)
                    this.erreur_conversion = retour.message;
                else if(retour.donnees.creation_auto == false){
                    this.afficher_modale_conversion_formulaire(retour.donnees);
                    return;
                }
                else
                    this.elements_generes = retour.donnees.elements_crees;

                this.modale_conversion_reussie = true;
            });
        },

        afficher_modale_conversion_formulaire: function(donnees){

            this.nom_formulaire = donnees.nom_formulaire;
            this.affichage_modale_conversion_formulaire = true;

            this.$once('formulaire_charger',() => {

                for(champ of donnees.champs_par_type[donnees.type_arrivee_principal])
                    this.$refs.formulaire_conversion.element[champ.champ_arrivee] = this.$root[this.$root.type_element][champ.champ_depart];
            });

            this.$on('sous_formulaire_charge',(type_element) => {

                var sous_formulaire = this.$refs.formulaire_conversion.$refs.formulaire.sous_formulaires_retraite[donnees.type_arrivee_principal + '_sous_formulaire_' + type_element];

                for(champ of donnees.champs_par_type[sous_formulaire.type_element_enfant])
                    if(champ.champ_liaison == sous_formulaire.champ_liaison)
                        this.$refs.formulaire_conversion.element[type_element][champ.champ_arrivee] = this.$root[this.$root.type_element][champ.champ_depart];
            });
        },

        enregistrer_formulaire_conversion: async function(){

            loading(true);

            let parametres = {conversion_type_element : this.$root.type_element, conversion_id_element : this.$root.element_id};
            retour = await this.$refs.formulaire_conversion.enregistrer(parametres)

            if(retour.retour == true){

                let element_genere = retour.element;
                element_genere.affiche_lien = '<a href="' + retour.lien_vers_element + '">' + element_genere.chaine_affichage + '</a>';
                this.elements_generes = {};
                this.elements_generes[this.$refs.formulaire_conversion.type_element] = [];
                this.elements_generes[this.$refs.formulaire_conversion.type_element].push(element_genere);

                this.affichage_modale_conversion_formulaire = false;
                this.modale_conversion_reussie = true;
            }


            loading(false);

        },
    @endpush
@endif