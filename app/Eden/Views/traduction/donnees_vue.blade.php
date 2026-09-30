@php

$langues_traductions = array();

if(parametre('S20220712_ajout_langue_traduction') == 1){

    $langues_traductions = langues()->keyBy('id');
}

    $langue_par_defaut = $langues_traductions->first(fn($langue) => $langue->code === 'fr');

    if(isset($langues_traductions[maquette('langue_par_defaut')]))
        $langue_par_defaut = $langues_traductions[maquette('langue_par_defaut')];
    
    if(!empty(moi()->langue))
        $langue = $langues_traductions->first(fn($langue) => $langue->code === moi()->langue) ?? $langue_par_defaut;
    else if(!empty(moi_extranet()->langue))
        $langue = $langues_traductions->first(fn($langue) => $langue->code === moi_extranet()->langue) ?? $langue_par_defaut;
    else
        $langue = $langue_par_defaut;
    
    $langues_traductions = $langues_traductions->values();

@endphp

@push('modales')
<traduction-modale ref="traduction_modale"></traduction-modale>
@endpush

@push('scripts_avant_vue')
    <script type="text/javascript" src="{{ asset('storage/traductions/langue_'.$langue->code.'.js') }}?version={{isset($version_composants) ? $version_composants : 0}}"></script>
@endpush

@push('donnees_pour_vuejs_data')
    traductions_valeurs : (typeof traductions_valeurs === 'undefined' ? {} : traductions_valeurs),
    langues_traduction_erp:{!! $langues_traductions !!},
    langue_traduction_erp_defaut:{!! $langue !!},
    mode_traduction: {!! session()->get('mode_traduction') ? 'true' : 'false' !!},
    loading_initial: false,
@endpush

@push('donnees_pour_vuejs_methods')

    traduction: function(index_traduction,champ = null,parametres = []) {

        var index = index_traduction;

        if(champ !== null)
            index = index+'.'+champ;

        if(this.$root.traductions_valeurs[index] === undefined)
            return index;

        var traduction = this.$root.traductions_valeurs[index];

        if(traduction == null)
            return traduction;

        var nombre_valeur_a_remplacer = (traduction.match(/#parametre_eden/g) || []).length;

        if(parametres.length > 0 && nombre_valeur_a_remplacer > 0){

            for(var i = 1; i <= nombre_valeur_a_remplacer; i++){

                var parametre = parametres[i-1];

                if(parametre !== undefined)
                    traduction = traduction.replace('#parametre_eden_'+i+'#',parametre);
            }
        }

        return traduction;
    },

    mise_a_jour_traductions_valeurs : function(){

        var vue_instance = this;

        loading(true);

        $.ajax({
            url:'{{route('parametrage.traduction.mise_a_jour_valeurs')}}',
            dataType:'json'
        }).done(function(traductions_valeurs){

            vue_instance.traductions_valeurs = traductions_valeurs;

            loading(false);
        });
    },

@endpush

@push('donnees_pour_vuejs_watch')

    mode_traduction : function(){

        $.ajax({
            url:'{{URL::to('eden/parametrage/traduction/mode_traduction')}}/'+(this.mode_traduction === true ? 1 : 0)
        });
    },
@endpush