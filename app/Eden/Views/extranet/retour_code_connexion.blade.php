@extends('eden::extranet.template-login.template')

@section('title')
{{traduction('interface.code_connexion.titre')}}
@endsection

@section("style")
    <style>
        #code_connexion .button-submit {
            background-color: {{ maquette('couleur_liens') }};
            border-color: {{ maquette('couleur_liens') }};
        }

        .button-submit:active{

            filter: brightness(80%);
        }
    </style>
@endsection

@section('contenu')
<form class="form" id="code_connexion" method="POST" action="{{route('extranet.retour_code_connexion') }}">
	{{ csrf_field() }}

	<p style="text-align: center;">
        @if($double_facteur_authentification == 1)
            {{traduction('interface.code_connexion.phrase')}}
        @else
            {{traduction('interface.code_connexion.phrase_application')}}
        @endif
    </p> 
    
    <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	
    <div class="form-field" style="justify-content: center;gap:5px;">
        <input type="number" v-for="i in 6" :key="i" v-model="code_connexion[i-1]" ref="code_connexion"
            min="0" max="9" @input="changement_valeur(i)" @keydown.backspace="i > 1 && code_connexion[i-1] == '' ? $refs.code_connexion[i-2].focus() : null"
            style="width: 40px;height: 50px;text-align: center;font-size: 24px;border-radius: 5px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
    </div>

    <div class="button">
        <button type="submit" class="button-submit">
            <span v-html="traduction('interface.code_connexion.valider_code')"></span>
        </button>
    </div>

    <input type="hidden" name="id_utilisateur" value="{{ $id_utilisateur }}" />
	<input type="hidden" name="token" value="{{ $token }}" />
    <input type="hidden" name="code_connexion" :value="code_connexion.join('')" />

    @if($double_facteur_authentification == 1)
        <div class="button">
            <button type="button" class="button-submit" @click="renvoi_mail_code_connexion">
                <span v-html="traduction('interface.code_connexion.renvoi_mail')"></span>
                <span v-if="loader_renvoi_mail" role="status" aria-hidden="true" style="margin-left: 10px;color:white;">
                    <img src="{{ url('eden/images/ajax_loader.gif') }}" style="height: 20px; width: 20px;" />
                </span>
            </button>
        </div>
    @endif
</form>
@endsection

@push('donnees_pour_vuejs_data')
    id_utilisateur : {{$id_utilisateur}},
    token : '{{$token}}',
    code_connexion: [
        '',
        '',
        '',
        '',
        '',
        ''
    ],
    loader_renvoi_mail : false,
@endpush

@push('donnees_pour_vuejs_methods')

    renvoi_mail_code_connexion : function(){

        this.loader_renvoi_mail = true;

        $.post({
            url : '/extranet/renvoi_mail_code_connexion',
            'dataType' : 'json',
            data : {
                id_utilisateur : this.id_utilisateur,
                token : this.token
            }
        }).done((retour) => {

            this.loader_renvoi_mail = false;

            if(retour !== true)
                toastr.error('{{traduction('interface.code_connexion.renvoi_mail.erreur')}}');
            else
                toastr.success('{{traduction('interface.code_connexion.renvoi_mail.succes')}}');

            this.code_connexion = [
                '',
                '',
                '',
                '',
                '',
                ''
            ];
        });
    },

    changement_valeur : function(i){

        if(this.code_connexion[i-1].length == 6){
            this.code_connexion = this.code_connexion[i-1].split('');
            this.$refs.code_connexion[5].focus();
            return;
        }

        if(this.code_connexion[i-1].length > 1)
            this.code_connexion[i-1] = this.code_connexion[i-1][1];

        if(this.code_connexion[i-1] != '' && i < 6)
            this.$refs.code_connexion[i].focus();
    },

@endpush
						