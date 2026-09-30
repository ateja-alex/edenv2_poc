@php
    $management_fiche = fiche('saisie_des_temps');
    $structure_fiche = $management_fiche->structure_fiche();
@endphp

<script>
const saisie_des_temps = Vue.component('saisie-des-temps', {
    template: `
        <div>

            <div class="saisie_des_temps" style="position:relative">

                <a v-if="$root.moi.type_utilisateur == 2" style="position: absolute;top: -10px;left: -10px;z-index: 500" href="/eden/parametrage/fiche/saisie_des_temps">
                    <div class="bulle_option css_pointer">
                        <i class="fas fa-cog"></i>
                    </div>
                </a>

                <div>

                    @include('eden::fiches.include.structure_fiche',[
                        'structure' => $structure_fiche,
                        'management_fiche' => $management_fiche,
                        'type_element' => 'saisie_des_temps',
                        'independant'=>true
                    ])

                </div>
            </div>
        </div>
    `,
    props:{
    },
    data : function(){

        return {

            feuille_de_temps : {
                element_id : null,
                utilisateur_id : null,
            },
            mode_affichage:'{{!empty($structure_fiche['options']['affichage_par_defaut']) ? $structure_fiche['options']['affichage_par_defaut'] : 'semaine'}}',
            gestion_commentaires:{{ !empty($structure_fiche['options']['gestion_commentaires']) ? 'true' : 'false' }},
            desactiver_enregistrer_elements_selectionnes:{{ !empty($structure_fiche['options']['desactiver_enregistrer_elements_selectionnes']) ? 'true' : 'false' }},
            informations_dates : {},
            commentaires: {},
            structure_fiche : {!! collect($structure_fiche) !!},
            @stack('donnees_pour_vuejs_data')
            @yield('donnees_pour_vuejs_data')
        }
    },
    created : async function(){

        this.feuille_de_temps = await this.$root.modele_par_defaut('feuille_de_temps');

        @stack('donnees_pour_vuejs_created')
        @yield('donnees_pour_vuejs_created')

        await this.charger_dates();
    },
    mounted : async function(){

        @stack('donnees_pour_vuejs_mounted')
        @yield('donnees_pour_vuejs_mounted')
    },
    methods: {

        @stack('donnees_pour_vuejs_methods')
        @yield('donnees_pour_vuejs_methods')

        charger_dates : async function(){

            this.$set(this.feuille_de_temps,'elements_ids',[]);

            var donnees = await $.post({
                url : '{{route('saisie_des_temps.charger_dates', [], false)}}',
                dataType:'json',
                data : {
                    parametres : this.feuille_de_temps,
                    mode_affichage : this.mode_affichage,
                }
            });

            this.informations_dates = donnees;

            this.$emit('changement');
        },

        date_statut: function(date){

            if(!(this.informations_dates.statut_saisie_terminee > 0))
                return 0;

            if(this.informations_dates.statut_saisie_validee != null) {

                if (this.informations_dates.statut_saisie_validee == 2)
                    return 2;

                for (periode_validee of this.informations_dates.feuille_de_temps_periode_validee) {

                    if (date >= periode_validee.date_debut && date <= periode_validee.date_fin)
                        return 2;
                }
            }

            if (this.informations_dates.statut_saisie_terminee == 2)
                return 1;

            for(periode_terminee of this.informations_dates.feuille_de_temps_periode_terminee){

                if(date >= periode_terminee.date_debut && date <= periode_terminee.date_fin)
                    return 1;
            }

            return 0;
        },

        affichage_bloc : function(nom_bloc = null){

            if(this.feuille_de_temps.utilisateur_id == null ||
                this.feuille_de_temps.utilisateur_id == 0 ||
                this.feuille_de_temps.type_element == null ||
                this.feuille_de_temps.type_element == 0)
                return false;

            if(nom_bloc != null && (this.feuille_de_temps.elements_ids == null || this.feuille_de_temps.elements_ids.length == 0))
                return false;

            return true;
        },

    },
    computed: {

        @stack('donnees_pour_vuejs_computed')
        @yield('donnees_pour_vuejs_computed')
    },
    watch: {

        @stack('donnees_pour_vuejs_watch')
        @yield('donnees_pour_vuejs_watch')

        'feuille_de_temps.element_id' : {

            handler : function() {

                if(this.feuille_de_temps.element_id == null || this.feuille_de_temps.element_id == 0)
                    return;

                var feuille_de_temps = structuredClone(this.feuille_de_temps);

                if(feuille_de_temps.elements_ids == undefined)
                    this.$set(feuille_de_temps,'elements_ids',[]);

                if(!feuille_de_temps.elements_ids.includes(feuille_de_temps.element_id))
                    this.$emit('chargement_element',feuille_de_temps.element_id);
                else
                    this.$emit('ajout_element_existant',feuille_de_temps.element_id);

                feuille_de_temps.element_id = null;

                this.$set(this,'feuille_de_temps',feuille_de_temps);

            },
            deep:true
        },

        'feuille_de_temps.utilisateur_id' : {

            handler : function(nouveau_utilisateur,ancien_utilisateur) {
                
                if(this.feuille_de_temps.utilisateur_id > 0 && nouveau_utilisateur != ancien_utilisateur){
                    this.feuille_de_temps.elements_ids=[];
                    this.charger_dates();

                    if(this.$refs.calendrier) {
                        this.$nextTick(() => {
                            this.$refs.calendrier.met_a_jour_les_dates();
                        });
                    }
                }
            },
            deep:true
        },
    },
});
</script>
