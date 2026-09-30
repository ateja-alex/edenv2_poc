@extends('eden::intranet.modules.base_module')

@section('footer_boutons_'.$id)
	<span class="footer_boutons">
        <div class="back" @click="enregistrer('{{$id}}','utilisateur')">
            <span class="glyphicon glyphicon-floppy-disk"></span>
            @traduction('interface.intranet.bouton_modifier_mdp')
        </div>
    </div>
@endsection

@section('contenu_'.$id)

	<dl class="dl-horizontal">
		<dt><b>@traduction('interface.intranet.nom_prenoms')</b></dt>
		<dd class="coupee"><p style="color:white;font-size: 16px;line-height: 35px;">{{ moi()->nom.' '.moi()->prenom }}</p></dd>
	</dl>
	<dl class="dl-horizontal">
		<dt><b>@traduction('interface.intranet.adresse_email')</b></dt>
		<dd class="coupee"><p style="color:white;font-size: 16px;line-height: 35px;">{{ moi()->email }}</p></dd>
	</dl>
	<div id="redict"></div>
	<form id="formulaire_{{$id}}" onsubmit="return false;" >
		<dl class="dl-horizontal" >
			<dt><b>@traduction('interface.intranet.nouveau_mdp')</b></dt>
			<dd class="coupee"><input type="password" name="mot_de_passe" required ></dd>
		 </dl>
		 <dl class="dl-horizontal">
			<dt><b>@traduction('interface.intranet.confirmer_mdp')</b></dt>
			<dd class="coupee"><input type="password" name="mot_de_passe_verification" required></dd>
		</dl>
	</form>

@endsection

@push('donnees_pour_vuejs_data')

	utilisateur : {!! moi() !!},

@endpush