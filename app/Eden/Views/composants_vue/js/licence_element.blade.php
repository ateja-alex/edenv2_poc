<script>
const licence_element = Vue.component('licence-element', {
    template: `
<div>
    <div class="row licence_element">
        <div class="col-md-12">
            <div class="card mb-3" v-if="!creation_ensemble">
                <div class="card-header">
                    <div class="d-flex align-items-center css_form" style="justify-content:space-between">
                        <h4>@traduction('interface.licence.details')</h4>
                        <input v-model="recherche" :placeholder="$root.traduction('interface.licence.rechercher')"/>
                        <div>
                            <span class="css_ajouter_element css__lien" @click="enregistrer_licence" :title="$root.traduction('interface.licence.enregistrer_licence')">
                                <i class="css_action_icon fa fa-fw fa-save"></i>
                            </span>
                            <a class="css_ajouter_element css__lien" href="{{route('base_eden.liste.index',['licence_ensemble'], false)}}" :title="$root.traduction('interface.licence.parametrage_ensembles')">
                                <i class="css_action_icon fa fa-fw fa-cog"></i>
                            </a>
                            <span class="css_ajouter_element css__lien" @click="creation_ensemble = true" :title="$root.traduction('interface.licence.creer_nouvel_ensemble')">
                                <i class="css_action_icon fa fa-fw fa-plus-square"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card-body css_form">
                    <div class="bloc_licence_element">
                        <div class="bloc_element_licence_element" v-for="ensemble in ensembles_tries" :key="ensemble.id">
                            <div class="titre_licence_element">
                                <div style="display: flex; gap: 10px;">
                                  <input :id="'ensemble_'+ensemble.id" :value="ensemble.id" v-model="$root.licence.ensembles" class="css_pointer" type="checkbox">
                                  <label :for="'ensemble_'+ensemble.id" class="css_pointer">
                                      <h5 v-html="ensemble.nom"></h5>
                                  </label>
                                </div>
                                <i @click="ensemble_deploye.includes(ensemble.id) ? ensemble_deploye.splice(ensemble_deploye.indexOf(ensemble.id),1) : ensemble_deploye.push(ensemble.id)"
                                    :class="'fas fa-chevron-'+(ensemble_deploye.includes(ensemble.id) ? 'up' : 'down')" aria-hidden="true" class="css_pointer"></i>
                            </div>
                            <div v-if="ensemble_deploye.includes(ensemble.id)" class="detail_licence_element">
                                <div v-for="(elements,index) in ensemble.elements_par_type" >
                                    <h6>@{{ $root.$options.filters.nom_valeur_liste_formatee(index,621) }}</h6>
                                    <div style="margin-left: 15px;text-transform: capitalize" v-for="element in elements">
                                        @{{ element.nom | retraite_caracteres_speciaux }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3" v-else>
                <div class="card-header element_licence_ensemble">
                    <h4>@traduction('interface.licence.creer_nouvel_ensemble')</h4>
                </div>
                <div class="card-body css_form">
                    <formulaire ref="formulaire" nom_formulaire="licence_ensemble"></formulaire>
                </div>
                <div class="card-footer" style="text-align: right;">
                    <button type="button" class="btn btn-secondary" @click="creation_ensemble = false">@traduction('interface.modales.fermer')</button>
                    <div class="btn btn-primary" @click="enregistrer_ensemble">@traduction('interface.modales.enregistrer')</div>
                </div>
            </div>
        </div>
    </div>
</div>`,
                props:{
                    licence_id:"",
                },
                data: function(){

                    return {
                        ensembles : [],
                        ensemble_deploye: [],
                        creation_ensemble : false,
                        recherche : '',
                    };
                },

                methods:{

                    charger_ensembles : function(){

                        $.post({
                            url : '{{route('maintenance.licence.ensembles', [], false)}}',
                            dataType:'json',
                            data : {
                                licence_id : this.$root.licence.id
                            }
                        }).done((donnees) => {

                            this.ensembles = donnees.ensembles;

                            this.$root.licence.ensembles = donnees.ensembles_elements;
                        });
                    },

                    enregistrer_ensemble : async function(){

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        var donnees = await component.$refs.formulaire.enregistrer();

                        if(donnees.retour === true) {
                            this.charger_ensembles();
                            this.creation_ensemble = false;
                        }

                        loading(false);
                    },

                    enregistrer_licence : async function(){

                        var component = this;

                        // On afficher le loader
                        loading(true);

                        await component.$root.$refs.formulaire_edition_element.$refs.formulaire.enregistrer({
                            'ensembles' : this.$root.licence.ensembles
                        });

                        loading(false);
                    },
                },

                mounted: function() {

                    this.$set(this.$root.licence,'ensembles',[]);

                    this.charger_ensembles();
                },

                computed:{

                    ensembles_par_id : function(){

                        var ensembles_par_id = {};

                        for(ensemble of this.ensembles){

                            ensembles_par_id[ensemble.id] = ensemble;
                        }

                        return ensembles_par_id;
                    },

                    ensembles_tries : function(){

                        var ensembles = this.ensembles;

                        ensembles.sort((a, b) => {

                            if(this.$root.licence.ensembles.includes(a.id) && !this.$root.licence.ensembles.includes(b.id))
                                return -1;

                            if(!this.$root.licence.ensembles.includes(a.id) && this.$root.licence.ensembles.includes(b.id))
                                return 1;

                            if(this.$root.licence.ensembles.includes(a.id) && this.$root.licence.ensembles.includes(b.id))
                                return (this.$root.licence.ensembles.indexOf(a.id) < this.$root.licence.ensembles.indexOf(b.id)) ? -1 : 1;

                            if (a.nom === b.nom)
                                return 0;

                            return (a.nom < b.nom) ? -1 : 1;
                        });

                        if(this.recherche == null || this.recherche == '')
                            return ensembles;

                        var ensembles_tries = [];

                        for(ensemble of ensembles){

                            if(ensemble.nom.toLowerCase().includes(this.recherche) || this.$root.licence.ensembles.includes(ensemble.id))
                                ensembles_tries.push(ensemble);
                        }

                        return ensembles_tries;

                    },
                },
});
</script>
