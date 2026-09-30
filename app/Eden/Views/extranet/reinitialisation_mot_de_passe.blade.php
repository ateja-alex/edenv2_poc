@extends('eden::extranet.template-login.template')

@section('title')
    Mot de passe oublié - Extranet
@endsection

@section("style")
    <style>
        #login .button-submit {
            background-color: {{ maquette('couleur_liens') }};
            border-color: {{ maquette('couleur_liens') }};
        }

        .button-submit:active{

            filter: brightness(80%);
        }
    </style>
@endsection

@section('contenu')
    <div id="login">

        <form class="" method="POST" action="{{route('extranet.changer_mot_de_passe_post') }}" class="form">
            {{ csrf_field() }}

            <h3 v-html="traduction('interface.reinitialisation_mot_de_passe_oublie.phrase')"></h3> <br>

            @if($errors->count() > 0)
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="form-field">
                <label for="password" v-html="traduction('interface.extranet.inscription.champs.mot_de_passe')"></label>

                <div>
                    <div class="col-md-6" style="display: grid">
                        <Password v-model="mot_de_passe" name="mot_de_passe" :placeholder="traduction('interface.extranet.inscription.placeholder.mot_de_passe')">
                            <template #footer>
                                <div style="display: flex; flex-direction: column; gap: 5px;font-size: 12px;">
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe.match(/[a-z]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.minuscule')"></span>
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe.match(/[A-Z]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.majuscule')"></span>
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe.match(/[0-9]/) ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.nombre')"></span>
                                    </span>
                                    <span>
                                        <i :class="'fas fa-' + (mot_de_passe.length >= 8 ? 'check' : 'times')"></i> <span v-html="traduction('interface.renouvellement_mot_de_passe.taille_minimale')"></span>
                                    </span>
                                </div>
                            </template>
                        </Password>
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label for="password" v-html="traduction('interface.extranet.inscription.champs.confirmer_mot_de_passe')"></label>

                <div class="col-md-6" style="display: grid">
                    <Password v-model="mot_de_passe_confirmation" name="mot_de_passe_confirmation" :feedback="false" :placeholder="traduction('interface.extranet.inscription.placeholder.confirmer_mot_de_passe')">
                    </Password>
                    <div class="help-block" style="width: 220px;font-size: 11px;margin-top: 5px;" v-if="mot_de_passe_confirmation != '' && mot_de_passe != '' && mot_de_passe_confirmation !== mot_de_passe">
                        <span v-html="traduction('messages.php.connexion.mdp_differents')"></span>
                    </div>
		        </div>
            </div>

            <div class="button">
                <button type="submit" class="button-submit">
                    <span v-html="traduction('interface.authentification.connexion')"></span>
                </button>
            </div>

            <input type="hidden" name="id_utilisateur" value="{{ $id }}">
            <input type="hidden" name="token" value="{{ $token }}" />
        </form>
    </div>
@endsection

@push('donnees_pour_vuejs_data')
	mot_de_passe : '',
	mot_de_passe_confirmation : '',
@endpush
