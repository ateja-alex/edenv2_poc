@php

    $vmodel = in_array($type_element,\App\Eden\Variables::$documents_gescom) ? 'document' : $type_element;

@endphp

var formulaire = {
    template: `<div>
        @if(!empty($sous_formulaire))
            {!! sous_formulaire($type_element_enfant, $type_element, $type_element_enfant, $name_remplacement_js, $type_element_remplacement, $remplacements_supplementaires_formate) !!}
        @else
            {!! formulaire($nom_formulaire,$contexte, '',  [], $uniquement_champs_editables, null, $options) !!}
        @endif
    </div>`,

    props : {
        @if(!empty($sous_formulaire))
            nom_sous_formulaire : '',
        @endif
    },

    data: function(){
        var data = {
            @yield('donnees_pour_vuejs_data')
            @stack('donnees_pour_vuejs_data')
        };

        data.{{$vmodel}} = {};

        @if(!empty($sous_formulaire))
            data['type_element_formulaire_parent'] = '{{ $type_element }}';
            data.sous_formulaire = true;
            data.{{$type_element_enfant}} = {};
        @else
            data['type_element_formulaire_parent'] = undefined;
            data.sous_formulaire = false;
        @endif

        return data;
    },

    methods:{

        @yield('donnees_pour_vuejs_methods')
        @stack('donnees_pour_vuejs_methods')
    },

    computed: {

        @yield('donnees_pour_vuejs_computed')
        @stack('donnees_pour_vuejs_computed')
    },

    created: function() {

        @if(!empty($sous_formulaire))
            this.{{$vmodel}} = this.$parent.{{$vmodel}};
            this.{{$type_element_enfant}} = this.$parent.{{$vmodel}}[this.nom_sous_formulaire];
        @else
            this.{{$vmodel}} = this.$parent.element;
        @endif

        @yield('donnees_pour_vuejs_created')
        @stack('donnees_pour_vuejs_created')
    },

    updated: function(){

        @yield('donnees_pour_vuejs_updated')
        @stack('donnees_pour_vuejs_updated')
    },

    mounted: async function() {

        @yield('donnees_pour_vuejs_mounted')
        @stack('donnees_pour_vuejs_mounted')
    },

    watch: {

        @stack('donnees_pour_vuejs_watch')
    },
    filters: {

        @stack('donnees_pour_vuejs_filter')
    },
    directives: {
        @yield('donnees_pour_vuejs_directives')
        @stack('donnees_pour_vuejs_directives')
    }
};