@extends('eden::authentification.template')

@section('title')
    Initialisation mot de passe - ERP
@endsection

@section('formulaire')
    <form class="form-horizontal" method="POST" action="{{route('initialisation_mot_de_passe_post')}}">
        {{ csrf_field() }}

        <p class="text-center" v-html="traduction('interface.initialisation_mot_de_passe.phrase')"></p> <br>

        @if($errors->count() > 0)
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="form-group">
            <label for="email" class="col-md-4 control-label" v-html="traduction('interface.initialisation_mot_de_passe.adresse_email')"></label>

            <div class="col-md-6">
                <input id="email" type="text" class="form-control" name="email" value="" :placeholder="traduction('interface.initialisation_mot_de_passe.placeholder_adresse_email')" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="mot_de_passe" class="col-md-4 control-label" v-html="traduction('interface.initialisation_mot_de_passe.nouveau_mot_de_passe')"></label>

            <div class="col-md-6" style="display: grid">
                <Password v-model="mot_de_passe" name="mot_de_passe" input-style="width:100%;" :placeholder="traduction('interface.renouvellement_mot_de_passe.placeholder_mot_de_passe')">
                    <template #footer>
                        <div style="display: flex; flex-direction: column; gap: 5px;">
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

        <div class="form-group">
            <label for="confirmation_mot_de_passe" class="col-md-4 control-label" v-html="traduction('interface.initialisation_mot_de_passe.confirmation_mot_de_passe')"></label>

            <div class="col-md-6" style="display: grid">
                <Password v-model="mot_de_passe_confirmation" input-style="width:100%;" name="confirmation_mot_de_passe" :feedback="false" :placeholder="traduction('interface.initialisation_mot_de_passe.confirmation_mot_de_passe')">
                </Password>
                <div class="help-block" v-if="mot_de_passe_confirmation != '' && mot_de_passe != '' && mot_de_passe_confirmation !== mot_de_passe">
                    <span v-html="traduction('messages.php.connexion.mdp_differents')"></span>
                </div>
            </div>
        </div>

        <input type="hidden" value="{!! $token !!}" name="token">

        <div class="form-group">
            <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary btn-lrdl" style="background-color: #038d8d; border-color: #527d1e; width: 100%">
                    <span v-html="traduction('interface.initialisation_mot_de_passe.bouton_initialisation')"></span>
                </button>
            </div>
        </div>

    </form>
@endsection

@push('donnees_pour_vuejs_data')
	mot_de_passe : '',
	mot_de_passe_confirmation : '',
@endpush