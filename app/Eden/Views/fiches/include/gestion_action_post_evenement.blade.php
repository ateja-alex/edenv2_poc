@push('donnees_pour_vuejs_mounted')

    @foreach($options['actions_apres_evenement'] as $action)

        this.$on('{{$action['evenement']}}',(parametres) => {

            @if($action['evenement'] == 'enregistrement_formulaire')

                if(parametres.nom_formulaire != '{{$action['formulaire']}}')
                    return;

                parametres = parametres.retour;
            @endif

            @if(!empty($action['condition_evenement']))

                var condition = `{!! $action['condition_evenement'] !!}`;

                if(parametres != undefined && parametres.element != undefined)
                    condition = condition.replace(new RegExp("{{$type_element}}\\.", "g"),'parametres.element.');
                
                with(this) { 
                    condition = eval(condition); 
                }

                if(!condition)
                    return;
            @endif

            @if($action['type'] == 1)

                @if($action['valeur_action'] == 'url')
                    document.location = '{{$action['valeur_action_annexe']}}';
                @elseif($action['valeur_action'] == 'retour_arriere' && !empty($_SERVER['HTTP_REFERER']))
                    document.location = '{{$_SERVER['HTTP_REFERER']}}';
                @endif
            @endif
        });

    @endforeach
@endpush