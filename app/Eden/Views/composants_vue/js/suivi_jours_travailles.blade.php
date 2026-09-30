@php
    $management_fiche = fiche('suivi_jours_travailles');
    $structure_fiche = $management_fiche->structure_fiche();
@endphp

<script>
    const suivi_jours_travailles = Vue.component('suivi-jours-travailles', {
        template: `
        <div>

            <div class="suivi_jours_travailles">

                <a v-if="$root.moi.type_utilisateur == 2" style="position: absolute;top: -10px;left: -10px;z-index: 500" href="/eden/parametrage/fiche/suivi_jours_travailles">
                    <div class="bulle_option css_pointer">
                        <i class="fas fa-cog"></i>
                    </div>
                </a>

                <div v-if="chargement === 1" style="display: flex;align-items: center;justify-content: center;">
                    <img style="width: 35px;height: 35px;" src="/eden/images/ajax_loader.gif" />
                </div>
                <div v-else>

                    @include('eden::fiches.include.structure_fiche',[
                        'structure' => $structure_fiche,
                        'management_fiche' => $management_fiche,
                        'type_element' => 'suivi_jours_travailles',
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

                @stack('donnees_pour_vuejs_data')
                @yield('donnees_pour_vuejs_data')
            }
        },
        created : async function(){

            @stack('donnees_pour_vuejs_created')
            @yield('donnees_pour_vuejs_created')
        },
        mounted : async function(){

            @stack('donnees_pour_vuejs_mounted')
            @yield('donnees_pour_vuejs_mounted')
        },
        methods: {

            @stack('donnees_pour_vuejs_methods')
            @yield('donnees_pour_vuejs_methods')

        },
        computed: {

            @stack('donnees_pour_vuejs_computed')
            @yield('donnees_pour_vuejs_computed')
        },
        watch: {

            @stack('donnees_pour_vuejs_watch')
            @yield('donnees_pour_vuejs_watch')
        },
        directives: {

            @stack('donnees_pour_vuejs_directives')
            @yield('donnees_pour_vuejs_directives')
        },
    });

    @include('eden::composants_vue.js.include.suivi_jours_travailles.bloc_suivi_jours_travailles')

</script>
