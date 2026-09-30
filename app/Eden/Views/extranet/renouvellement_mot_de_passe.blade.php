@extends('eden::extranet.template-login.template')

@section('title')
{{traduction('interface.renouvellement_mot_de_passe.titre')}}
@endsection

@section("style")
    <style>
        #renouvellement .button-submit {
            background-color: {{ maquette('couleur_liens') }};
            border-color: {{ maquette('couleur_liens') }};
        }

        .button-submit:active{

            filter: brightness(80%);
        }
    </style>
@endsection

@section('contenu')
    <div id="renouvellement">
        <form class="form" method="POST" action="{{route('extranet.renouvellement_mot_de_passe_post') }}">
            {{ csrf_field() }}

            <p class="text-center" v-html="traduction('interface.renouvellement_mot_de_passe.phrase')"></p> <br>
            
            @if($errors->count() > 0)
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            
            <div class="form-field">
                <label for="email" class="col-md-4 control-label" v-html="traduction('interface.renouvellement_mot_de_passe.mot_de_passe')"></label>

                <div class="col-md-6" style="display: grid">
                    <Password v-model="mot_de_passe" name="mot_de_passe" input-style="width:100%;" :placeholder="traduction('interface.renouvellement_mot_de_passe.placeholder_mot_de_passe')">
                        <template #footer>
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <span>
                                    <i :class="'fas fa-' + (mot_de_passe.match(/[a-z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.minuscule') }}
                                </span>
                                <span>
                                    <i :class="'fas fa-' + (mot_de_passe.match(/[A-Z]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.majuscule') }}
                                </span>
                                <span>
                                    <i :class="'fas fa-' + (mot_de_passe.match(/[0-9]/) ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.nombre') }}
                                </span>
                                <span>
                                    <i :class="'fas fa-' + (mot_de_passe.length >= 8 ? 'check' : 'times')"></i> {{ traduction('interface.renouvellement_mot_de_passe.taille_minimale') }}
                                </span>
                            </div>
                        </template>
                    </Password>
                </div>
            </div>

            <div class="form-field">
                <label for="email" class="col-md-4 control-label" v-html="traduction('interface.renouvellement_mot_de_passe.confirmation_mot_de_passe')"></label>

                <div class="col-md-6" style="display: grid">
                    <Password v-model="mot_de_passe_confirmation" input-style="width:100%;" name="mot_de_passe_confirmation" :feedback="false" :placeholder="traduction('interface.renouvellement_mot_de_passe.placeholder_confirmation_mot_de_passe')">
                    </Password>
                    <div class="help-block" style="width: 202px;font-size: 11px;margin-top: 5px;" v-if="mot_de_passe_confirmation != '' && mot_de_passe != '' && mot_de_passe_confirmation !== mot_de_passe">
                        {{ traduction('messages.php.connexion.mdp_differents') }}
                    </div>
                </div>
            </div>

            <div class="button">
                <button type="submit" class="button-submit">
                    <span v-html="traduction('interface.renouvellement_mot_de_passe.renouvellement')"></span>
                </button>
            </div>

            <input type="hidden" name="id_utilisateur" value="{{ $id_utilisateur }}" />
            <input type="hidden" name="token" value="{{ $token }}" />
        </form>
    </div>
@endsection

@push('donnees_pour_vuejs_data')
	mot_de_passe : '',
	mot_de_passe_confirmation : '',
@endpush
						