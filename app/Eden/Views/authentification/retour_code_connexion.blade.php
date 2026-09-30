@extends('eden::authentification.template')

@section('title')
@{{traduction('interface.code_connexion.titre')}}
@endsection

@section('formulaire')
<form class="form-horizontal" method="POST" action="{{route('retour_code_connexion') }}">
	{{ csrf_field() }}

	<p class="text-center">
        @if($double_facteur_authentification == 1)
            <span v-html="traduction('interface.code_connexion.phrase')"></span>
        @else
            <span v-html="traduction('interface.code_connexion.phrase_application')"></span>
        @endif
    </p> 
    
    <br>
	
	@if($errors->count() > 0)
		<div class="alert alert-danger">{{ $errors->first() }}</div>
	@endif
	
	
	<div class="form-group">
        <div style="display: flex; justify-content: center;gap:5px;">
            <input type="number" v-for="i in 6" :key="i" v-model="code_connexion[i-1]" ref="code_connexion"
                min="0" max="9" @input="changement_valeur(i)" @keydown.backspace="i > 1 && code_connexion[i-1] == '' ? $refs.code_connexion[i-2].focus() : null"
                style="width: 40px;height: 50px;text-align: center;font-size: 24px;border-radius: 5px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
        </div>
    </div>

	<div class="form-group" style="display: flex;justify-content: center;">
		<div class="col-md-6">
			<button type="submit" class="btn btn-primary btn-lrdl" style="background-color: #038d8d; border-color: #527d1e; width: 100%">
				<span v-html="traduction('interface.code_connexion.valider_code')"></span>
			</button>
		</div>
	</div>

    <input type="hidden" name="id_utilisateur" value="{{ $id_utilisateur }}" />
	<input type="hidden" name="token" value="{{ $token }}" />
    <input type="hidden" name="code_connexion" :value="code_connexion.join('')" />
    <input type="hidden" name="remember_token" value="{{$remember_token}}" /> 

    @if($double_facteur_authentification == 1)
        <div class="form-group" style="display: flex;justify-content: center;text-align: center;">
            <div class="col-md-6">
                <span class="btn btn-link btn-link-lrdl" @click="renvoi_mail_code_connexion" style="color: #ee7767" v-html="traduction('interface.code_connexion.renvoi_mail')">
                </span>
            </div>
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
@endpush

@push('donnees_pour_vuejs_methods')

    renvoi_mail_code_connexion : function(){

        $.post({
            url : '/eden/renvoi_mail_code_connexion',
            'dataType' : 'json',
            data : {
                id_utilisateur : this.id_utilisateur,
                token : this.token
            }
        }).done((retour) => {
            if(retour !== true)
                toastr.error(this.traduction('interface.code_connexion.renvoi_mail.erreur'));
            else
                toastr.success(this.traduction('interface.code_connexion.renvoi_mail.succes'));

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
						