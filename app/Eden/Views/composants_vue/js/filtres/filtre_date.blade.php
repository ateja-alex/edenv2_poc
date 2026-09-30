<script>
    const filtre_date = Vue.component('filtre-date', {
        template: `
            <div>
                <div v-if="affichage">
                    <span v-if="debut != null && fin != null"
                        v-html="$root.traduction(
                            'filtres.champ_date.affichage_deux_dates',
                            null,
                            ['<span class=valeur>'+debut +'</span>',
                            '<span class=valeur>'+fin +'</span>'
                            ])">
                    </span>
                    <span v-else-if="debut != null"
                          v-html="$root.traduction(
                            'filtres.champ_date.affichage_apres',
                            null,
                            ['<span class=valeur>'+debut +'</span>'])">
                    </span>
                    <span v-else-if="fin != null" v-html="$root.traduction(
                            'filtres.champ_date.affichage_avant',
                            null,
                            ['<span class=valeur>'+fin +'</span>'])">
                    </span>
                    <span v-else-if="variable !== null">
                        @traduction('filtres.champ_date.affichage_contenu')
                        <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_date.'+variable)"></span>
                    </span>
                </div>
                <div class="css_block_datepicker_popover css_form" v-else>
                    <div>
                        <div class="css_champ_date_popover">
                            <span>@traduction('filtres.cree_filtre_pour_liste.champ_date.debut')</span>
                            <input type="date" v-model="debut"/>
                        </div>
                        <div class="css_champ_date_popover">
                            <span>@traduction('filtres.cree_filtre_pour_liste.champ_date.fin')</span>
                            <input type="date" v-model="fin"/>
                        </div>
                    </div>
                    <div>
                        <div class="css_champ_date_popover">
                            <span>@traduction('filtres.cree_filtre_pour_liste.divers.tri_rapide')</span>
                        </div>
                        <div v-if="variables_charges === false" class="text-center">
                            <img style="width: 5%;" class="loader" src="eden/images/ajax_loader.gif">
                        </div>
                        <div class="css_liste_checkbox_popover" v-else>
                            <div class="form-check form-check-inline css_checkbox_popover" v-for="liste_variable in liste_variables">
                                <label>
                                    <input class="form-check-input" v-model="variable" type="radio" :value="liste_variable.valeur">
                                    <span v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_date.'+liste_variable.valeur)"></span>
                                    <br>
                                    <span class="css_description_checkbox_popover">
                                        @{{ liste_variable.description }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,
        props: {
            filtre : {
                type : Object,
                default: function(){
                    return {};
                }
            },
            valeurs : {
                type : Object,
                default: function(){
                    return {
                        variable : null,
                        debut : null,
                        fin : null,
                    };
                }
            },
            affichage: {
                type : Boolean,
                default: false
            }
        },
        data : function(){
            return {
                liste_variables : [],
                variables_charges : false,
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed: {
            debut : {
                get(){
                    return this.valeurs.debut;
                },
                set(valeur){

                    if(valeur == '')
                        valeur = null;

                    this.changement_filtre({
                        variable : null,
                        debut : valeur,
                        fin : this.valeurs.fin,
                    });
                },
            },
            fin : {
                get(){
                    return this.valeurs.fin;
                },
                set(valeur){

                    if(valeur == '')
                        valeur = null;

                    this.changement_filtre({
                        variable : null,
                        debut : this.valeurs.debut,
                        fin : valeur,
                    });
                },
            },
            variable : {
                get(){
                    return this.valeurs.variable;
                },
                set(valeur){
                    this.changement_filtre({
                        variable : valeur,
                        debut : null,
                        fin : null,
                    });
                },
            },

        },
        mounted : async function() {

            var variables_dates = localStorage.variables_dates ? JSON.parse(localStorage.variables_dates) : null;

            if (variables_dates == null || variables_dates.date != this.$root.aujourdhui)
                localStorage.variables_dates = JSON.stringify({
                    date: this.$root.aujourdhui,
                    variables : await $.post({
                        url: '{{route('base_eden.champ.variables_champ_date', [], false)}}',
                        dataType: 'json'
                    })
                });

            this.liste_variables = JSON.parse(localStorage.variables_dates).variables;
            this.variables_charges = true;
        },
    });
</script>
