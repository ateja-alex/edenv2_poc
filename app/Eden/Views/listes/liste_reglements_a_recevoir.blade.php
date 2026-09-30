@extends('eden::composants_vue.js.liste_libre')

@section('actions_specifiques_sur_liste')
	<form action="{{ route('reglements_a_recevoir.traitement', [], false) }}" id="formulaire_reglements_a_recevoir" method="post">
		<div class="card-body">
			<div class="row">
				<div class="col-md-12">
					@traduction('interface.listes.compte_bancaire')
					<select name="compte_bancaire_id">
						@foreach(modele('compte_bancaire')->get() as $compte_bancaire)
							<option value="{{ $compte_bancaire->id }}">{{ $compte_bancaire->nom }}</option>
						@endforeach
					</select>

					@traduction('interface.listes.mode_de_paiement')
					<select name="mode_paiement_id">
						@foreach(modele('mode_paiement')->get() as $mode_paiement)
							<option value="{{ $mode_paiement->id }}">{{ $mode_paiement->nom }}</option>
						@endforeach
					</select>

					<div @click="enregistre_reglements_a_recevoir" class="btn btn-primary btn-xs">@traduction('interface.listes.enregistrer_les_paiements')</div>

				</div>
			</div>
		</div>
	</form>
@endsection

@push('donnees_pour_vuejs_methods')

	enregistre_reglements_a_recevoir: function() {

		$('#formulaire_reglements_a_recevoir').find('.js_reglement_a_recevoir').remove();

		// on récupère tous les montants saisis
		$('.js_reglement_a_recevoir').each(function() {

			var clone = $(this).clone();

			clone.attr('type', 'hidden');

			$('#formulaire_reglements_a_recevoir').append(clone);
			$('#formulaire_reglements_a_recevoir').append('<input type="hidden" name="documents_id[]" class="js_reglement_a_recevoir" value="'+clone.attr('document_id')+'" />');
		});

		$('#formulaire_reglements_a_recevoir').submit();
	},

@endpush
