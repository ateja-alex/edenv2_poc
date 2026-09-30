@extends($extends)

@section('footer_sticky')

<div style="z-index: 1000;position: fixed;bottom: 0px;margin-left: 0px;right: 0;background: #fff;border: 1px solid black;padding: 10px;">

	<div class="row">
		<div class="col-sm-12 text-center mb-10">
			<b>@traduction('tables_libres.campagne_de_prospection.nom_table') :</b>
			{!! management('campagne_de_prospection',$campagne_de_prospection_en_cours->id,$campagne_de_prospection_en_cours)->affiche() !!}
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12">

			<a :title="traduction('module_sur_fiche.element_campagne_de_prospection.retour_campagne')" href="{{route('base_eden.fiche.index',['campagne_de_prospection',$campagne_de_prospection_en_cours->id])}}" class="css_pointer btn btn-primary">
				<i class="fas fa-stop"></i>
			</a>

			@if(isset($campagne_lien_element_precedent))
				<a class="btn btn-primary" href="{{$campagne_lien_element_precedent}}">&laquo;</a>
			@endif

			<span @click="enregistrer_reponses" class="css_pointer btn btn-primary">
				@traduction('module_sur_fiche.fiche.campagne_de_prospection.enregistrer')
			</span>

			@if(isset($campagne_lien_element_suivant))

				<span @click="enregistrer_reponses_et_suivant" class="css_pointer btn btn-primary">
					@traduction('module_sur_fiche.fiche.campagne_de_prospection.enregistrer_suivant')
				</span>

				<a class="btn btn-primary" href="{{ $campagne_lien_element_suivant }}"> &raquo;</a>
			@elseif(isset($source_lancement_prospection))
				<a class="btn btn-primary" href="{{$source_lancement_prospection}}">
					@traduction('module_sur_fiche.fiche.campagne_de_prospection.terminer')
				</a>
			@endif
		</div>
	</div>
</div>

@endsection

@push('donnees_pour_vuejs_data')
	campagne_de_prospection_id : {{$campagne_de_prospection_en_cours->id}},
@endpush

@push('donnees_pour_vuejs_methods')

enregistrer_reponses : async function(){

	var vue_instance = this;

	loading(true);

	var refs = this.$refs;

	var erreurs = [];

	for(ref of Object.keys(refs)){

		if(ref.includes('questionnaire_')){
			var retour = await this.$refs[ref].enregistrer(true);

			if(retour.retour !== true)
				erreurs.push(retour);
		}
	}

	loading(false);

	if(erreurs.length == 0){
		info(vue_instance.$root.traduction('messages.js.enregistrement_succes'));

		return true;
	}
	else{
		var erreurs_afficher = '';

		for(erreur_texte of erreurs){

			erreurs_afficher += "Erreur questionnaire : " + erreur_texte.questionnaire.nom + "<br>"+erreur_texte.erreur+"<hr>";
		}

		alerte_eden(erreurs_afficher);

		return false;
	}
},

enregistrer_reponses_et_suivant : async function(){

	var erreur_texte = await this.enregistrer_reponses();

	@if(isset($campagne_lien_element_suivant))
		if(erreur_texte)
			location.href = '{{ $campagne_lien_element_suivant }}';
	@endif
},
@endpush