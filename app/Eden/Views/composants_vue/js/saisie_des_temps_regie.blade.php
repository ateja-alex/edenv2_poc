<script>
const saisie_des_temps_regie = Vue.component('saisie-des-temps-regie', {
    template: `
        <div>
            <div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4 style="width: 100%" class="d-flex justify-content-between">
								@traduction('interface.saisie_des_temps_regie.titre')
							</h4>
						</div>
						<div class="card-body css_form">
							<div class="row">
                                <div class="col-md-2">
                                  Choix élément :
                                </div>
                                <div class="col-md-4">
                                  {!! management('feuille_de_temps')->champ('type_element')->cree() !!}
                                </div>
                                <div class="col-md-4">
                                  {!! management('feuille_de_temps')->champ('element_id')->cree() !!}
                                </div>
                                <div class="col-md-2">
                                    <span class="badge"
                                            v-bind:class="[uniquement_mes_temps % 2 == 1 ? 'badge-success' : 'badge-secondary']"
                                            @click="uniquement_mes_temps = (uniquement_mes_temps + 1) % 2">
                                        @traduction('interface.saisie_des_temps_regie.filtre.uniquement_mes_temps')
                                    </span>
                                </div>
							</div>
                            <br>
                            <div class="row">
                                <template v-for="(feuilles_de_temps_par_element,type_element) in feuilles_de_temps_par_type_element">
                                    <div class="col-md-2">
                                        @{{ type_element }} :
                                    </div>
                                    <div class="col-sm-9">
                                        <template v-for="element in feuilles_de_temps_par_element">
                                            <span v-html="element.chaine_affichage" style="margin-right: 5px;" class="badge badge-default" @click="scroll_to_element(type_element,element.element.id)">
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            </div>
						</div>
					</div>
				</div>
			</div>

			<template v-for="(feuilles_de_temps_par_element,type_element) in feuilles_de_temps_par_type_element">
                <template v-for="element in feuilles_de_temps_par_element">
                    <div class="row" :id="type_element+'_'+element.element.id">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="css_flex_header_liste">

                                        <h4>

                                            <span v-html="element.chaine_affichage"></span> | @{{ element.ce_mois_ci }} @traduction('interface.saisie_des_temps_regie.ce_mois_ci') | @{{ element.mois_dernier }} @traduction('interface.saisie_des_temps_regie.le_mois_dernier')

                                        </h4>
                                        <div class="ml-auto">
                                            <a data-toggle="tooltip" class="css_ajouter_element css__lien" :title="$root.traduction('interface.saisie_des_temps_regie.generation.facture_mois_courant')" :href="'/eden/saisie_des_temps/regie/generer_facture/'+type_element+'/'+element.element.id+'?date_debut={{date('Y-m-01')}}'"><i class="css_action_icon fa fa-fw fa-file"></i></a>
                                            <a data-toggle="tooltip" class="css_ajouter_element css__lien" :title="$root.traduction('interface.saisie_des_temps_regie.generation.facture_mois_mois_un')" :href="'/eden/saisie_des_temps/regie/generer_facture/'+type_element+'/'+element.element.id"><i class="css_action_icon fa fa-fw fa-file"></i></a>
                                            <a data-toggle="tooltip" class="css_ajouter_element css__lien" :title="$root.traduction('interface.saisie_des_temps_regie.generation.facture_mois_mois_deux')" :href="'/eden/saisie_des_temps/regie/generer_facture/'+type_element+'/'+element.element.id+'?date_debut={{date('Y-m-01', strtotime('now -2 months'))}}'"><i class="css_action_icon fa fa-fw fa-file"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <form>
                                            <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">@traduction('interface.saisie_des_temps_regie.colonne_recap.date')</th>
                                                        <th scope="col">@traduction('interface.saisie_des_temps_regie.colonne_recap.activite')</th>
                                                        <th scope="col">@traduction('interface.saisie_des_temps_regie.colonne_recap.utilisateur')</th>
                                                        <th scope="col">@traduction('interface.saisie_des_temps_regie.colonne_recap.temps')</th>
                                                        <th scope="col">@traduction('interface.saisie_des_temps_regie.colonne_recap.options')</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template v-for="(feuille_de_temps_element, index) in element.feuilles_de_temps">

                                                        <tr v-if="uniquement_mes_temps == 0 || feuille_de_temps_element.utilisateur_id == {{ moi()->id }}">
                                                            <th scope="col"><input type="date" v-model="feuille_de_temps_element.date" @change="nouvelle_feuille_de_temps(element, feuille_de_temps_element)" /></th>
                                                            <th scope="col"><input type="text" v-model="feuille_de_temps_element.tache" @change="nouvelle_feuille_de_temps(element, feuille_de_temps_element)" /></th>
                                                            <th scope="col">
                                                                {!! management('feuille_de_temps')
                                                                    ->champ('utilisateur_id')
                                                                    ->vmodel(true,'feuille_de_temps_element')
                                                                    ->attr('@change','nouvelle_feuille_de_temps(element, feuille_de_temps_element)')
                                                                    ->cree() !!}
                                                            </th>
                                                            <th scope="col">
                                                              <champ-montant
                                                                  :modele="feuille_de_temps_element"
                                                                  nom_sql="duree"
                                                                  :parametres_emit="{
                                                                    element:element,
                                                                }"
                                                              ></champ-montant>
                                                            </th>
                                                            <th scope="col"><i class="fa fa-trash" title="Supprimer" data-toggle="tooltip" @click="supprimer_feuille_de_temps(feuille_de_temps_element, element, index);"></i></th>
                                                        </tr>

                                                        <tr v-if="temps_par_jour(element.feuilles_de_temps, feuille_de_temps_element.date) > 0 && (!element.feuilles_de_temps[index + 1] && feuille_de_temps_element.date != '{{date('Y-m-d')}}' || element.feuilles_de_temps[index + 1] && feuille_de_temps_element.date != element.feuilles_de_temps[index + 1].date)">
                                                            <th colspan="4" style="text-align: right;font-weight:bold;" v-text="feuille_de_temps_element.date.substring(8,10)+'/'+feuille_de_temps_element.date.substring(5,7)+'/'+feuille_de_temps_element.date.substring(0,4)"></th>
                                                            <th v-text="temps_par_jour(element.feuilles_de_temps, feuille_de_temps_element.date)+'h'"></th>
                                                        </tr>

                                                    </template>

                                                    <tr v-if="temps_par_jour(element.feuilles_de_temps, '{{date('Y-m-d')}}') > 0">
                                                        <th colspan="4" style="text-align: right;font-weight:bold;">@traduction('interface.saisie_des_temps_regie.aujourdhui')</th>
                                                        <th v-text="temps_par_jour(element.feuilles_de_temps, '{{date('Y-m-d')}}')+'h'"></th>
                                                    </tr>

                                                    <tr>
                                                        <th scope="col"><input type="date" v-model="element.feuille_de_temps.date" @change="nouvelle_feuille_de_temps(element)" /></th>
                                                        <th scope="col"><input type="text" v-model="element.feuille_de_temps.tache" @change="nouvelle_feuille_de_temps(element)" /></th>
                                                        <th scope="col">
                                                            {!! management('feuille_de_temps')
                                                                    ->champ('utilisateur_id')
                                                                    ->vmodel(true,'element.feuille_de_temps')
                                                                    ->attr('@change','nouvelle_feuille_de_temps(element)')
                                                                    ->cree() !!}
                                                        </th>
                                                        <th scope="col">
                                                            <champ-montant
                                                                :modele="element.feuille_de_temps"
                                                                nom_sql="duree"
                                                                :parametres_emit="{
                                                                    element:element,
                                                                }"
                                                                ></champ-montant>
                                                        </th>
                                                        <th scope="col"></th>
                                                    </tr>

                                                </tbody>
                                            </table>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
			</template>
        </div>
    `,
    data : function(){

        return {

            choix_element: '',
            feuilles_de_temps_par_type_element: {},
            feuille_de_temps:{},
            uniquement_mes_temps:0,
        }
    },
    mounted : function(){
        this.initialisation();

        this.$on('maj_champ_montant',(donnees) => {

            this.nouvelle_feuille_de_temps(donnees.element,donnees.modele);
        });
    },
    methods: {

        initialisation : function(){

            loading(true);

             $.ajax({
                url: '{{route('saisie_des_temps.regie.initialisation', [], false)}}',
                dataType: "json",
            }).done(async (retour) => {

                if(Object.values(retour.feuilles_de_temps_par_type_element).length > 0)
                    this.feuilles_de_temps_par_type_element = retour.feuilles_de_temps_par_type_element;

                this.feuille_de_temps = retour.feuille_de_temps;

                 this.ordonnes_feuilles();

                loading(false);
            });

        },

        temps_par_jour: function(feuilles_de_temps, date) {

            somme = 0;
            uniquement_mes_temps = this.uniquement_mes_temps;

            $.each(feuilles_de_temps, function(key, feuille_de_temps) {

                if(date == feuille_de_temps.date) {

                    if( uniquement_mes_temps == 0 || feuille_de_temps.utilisateur_id == {{ moi()->id }}) {
                        somme += parseFloat(feuille_de_temps.duree);

                    }
                }
            });

            return somme;
        },

        nouvelle_feuille_de_temps: function(element, nouvelle_feuille_de_temps_saisie = false) {

            var url = '{{ route('base_eden.element.creer','feuille_de_temps', false) }}';

            if(nouvelle_feuille_de_temps_saisie == false)
                nouvelle_feuille_de_temps_saisie = element.feuille_de_temps;
            else
                url = "eden/element/feuille_de_temps/"+nouvelle_feuille_de_temps_saisie.id+"/enregistrer/";

            if(nouvelle_feuille_de_temps_saisie.duree == '' || nouvelle_feuille_de_temps_saisie.tache == '')
                return;

            loading(true);

            // on récupère le projet
            $.post({

                url: url,
                dataType: "json",
                data: nouvelle_feuille_de_temps_saisie,
            }).done(async (retour) => {

                if(retour.retour === true) {

                    if(nouvelle_feuille_de_temps_saisie.id == undefined) {
                        element.feuilles_de_temps.push(retour.element);

                        this.ordonnes_feuilles();
                    }

                    element.feuille_de_temps = {

                        date: this.$root.aujourdhui,
                        utilisateur_id: this.$root.moi.id,
                        element_id: nouvelle_feuille_de_temps_saisie.element_id,
                        type_element: nouvelle_feuille_de_temps_saisie.type_element,
                        duree: '',
                        tache: '',
                    };
                }
                else
                    await erreur(retour.retour);

                loading(false);
            });

        },

        supprimer_feuille_de_temps: async function(feuille_de_temps, element, index) {

            if(!await confirm_eden('{!! traduction('interface.alerte.attention') !!}',this.$root.traduction('interface.listes.etes_vous_certain'),'{!! traduction('interface.modales.oui') !!}','{!! traduction('interface.modales.non') !!}'))
                return false;

            loading(true);

            // on récupère le projet
            $.get({

                url: "eden/element/feuille_de_temps/"+feuille_de_temps.id+"/supprimer",
                dataType: "json"
            }).done(function() {

                element.feuilles_de_temps.splice(index, 1);

                loading(false);
            });
        },

        ajout_element: function(element_id) {

            var type_element = this.feuille_de_temps.type_element;

            if(this.feuilles_de_temps_par_type_element[type_element] == null)
                this.$set(this.feuilles_de_temps_par_type_element,type_element,[]);

            loading(true);

            // on récupère le projet
            $.get({

                url: "eden/saisie_des_temps/regie/recupere_element/"+type_element+'/'+element_id,
                dataType: "json"
            }).done((element) => {

                this.feuilles_de_temps_par_type_element[type_element].push(element);

                loading(false);
            });

        },

        scroll_to_element: function(type_element,element_id){
            position = $('#'+type_element+'_'+element_id).offset().top;
            if($('#mainNav').height()){
                position = position - $('#mainNav').height();
            }
            $('html,body').animate({
            scrollTop: position
            }, 'slow');
        },

        ordonnes_feuilles : function(){

            for(type_element of Object.keys(this.feuilles_de_temps_par_type_element)){

                feuilles_par_element = this.feuilles_de_temps_par_type_element[type_element];

                for(index in feuilles_par_element){

                    feuilles_par_element[index].feuilles_de_temps = _.orderBy(feuilles_par_element[index].feuilles_de_temps, 'date');
                }

                this.feuilles_de_temps_par_type_element[type_element] = feuilles_par_element;
            }
        },
    },
    watch : {

        'feuille_de_temps.element_id' : {

            handler : function() {

                if(this.feuille_de_temps.element_id == null || this.feuille_de_temps.element_id == 0)
                    return;

                var feuille_de_temps = structuredClone(this.feuille_de_temps);

                this.ajout_element(feuille_de_temps.element_id);

                feuille_de_temps.element_id = null;

                this.$set(this,'feuille_de_temps',feuille_de_temps);

            },
            deep:true
        },
    }
});
</script>
