<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('module_sur_fiche.client.profil_familial.titre')
					<span style="font-size: 12px">@{{ statut_enregistrement_profil_familial }}</span>
				</h4>
			</div>
			<div class="card-body">

			<form id = "formulaire_profil_familial" class="css_form" @change="modifier_profil_familial">
				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.statut')
					</div>

					<div class="col-4">
						<select name="statut">
							<option value="1" @if($profil_familial !== null && $profil_familial->statut == 1) selected @endif >{{traduction('module_sur_fiche.client.profil_familial.celibataire')}}</option>
							<option value="2" @if($profil_familial !== null && $profil_familial->statut == 2) selected @endif >{{traduction('module_sur_fiche.client.profil_familial.marie.e')}}</option>
							<option value="3" @if($profil_familial !== null && $profil_familial->statut == 3) selected @endif >{{traduction('module_sur_fiche.client.profil_familial.divorce.e')}}</option>
						</select>
					</div>
				</div>


				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.prenom_conjoint')
					</div>

					<div class="col-4">
						<input type="text" name="prenom_du_conjoint" value="@if($profil_familial !== null){{ $profil_familial->prenom_du_conjoint }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.nom_conjoint')
					</div>

					<div class="col-4">
						<input type="text" name="nom_du_conjoint" value="@if($profil_familial !== null){{ $profil_familial->nom_du_conjoint }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.date_naissance_conjoint')
					</div>

					<div class="col-4">
						<input type="text" class="datepicker" name="date_de_naissance_du_conjoint" data-date-format="dd/mm/yyyy"  value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_du_conjoint) }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.nombre_enfants')
					</div>

					<div class="col-4">
						<input type="text" name="nombre_enfants" value="@if($profil_familial !== null){{ $profil_familial->nombre_enfants }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-6">
						@traduction('module_sur_fiche.client.profil_familial.date_mariage')
					</div>

					<div class="col-4">
						<input type="text" class="datepicker" name="date_de_mariage" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_mariage) }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.enfant_1')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.enfant_2')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.enfant_3')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.enfant_4')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.enfant_5')
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.nom')
					</div>

					<div class="col-2">
						<input type="text" name="nom_enfant_1" value="@if($profil_familial !== null){{ $profil_familial->nom_enfant_1 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_enfant_2" value="@if($profil_familial !== null){{ $profil_familial->nom_enfant_2 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_enfant_3"  value="@if($profil_familial !== null){{ $profil_familial->nom_enfant_3 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_enfant_4"  value="@if($profil_familial !== null){{ $profil_familial->nom_enfant_4 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_enfant_5"  value="@if($profil_familial !== null){{ $profil_familial->nom_enfant_5 }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.prenom')
					</div>

					<div class="col-2">
						<input type="text" name="prenom_enfant_1"  value="@if($profil_familial !== null){{ $profil_familial->prenom_enfant_1 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_enfant_2"  value="@if($profil_familial !== null){{ $profil_familial->prenom_enfant_2 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_enfant_3"  value="@if($profil_familial !== null){{ $profil_familial->prenom_enfant_3 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_enfant_4"  value="@if($profil_familial !== null){{ $profil_familial->prenom_enfant_4 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_enfant_5"   value="@if($profil_familial !== null){{ $profil_familial->prenom_enfant_5 }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.date_naissance')
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_de_naissance_enfant_1" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_enfant_1) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_de_naissance_enfant_2" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_enfant_2) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_de_naissance_enfant_3" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_enfant_3) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_de_naissance_enfant_4" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_enfant_4) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_de__naissance_enfant_5" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_de_naissance_enfant_5) }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation_1')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation_2')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation_3')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation_4')
					</div>

					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation_5')
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.relation')
					</div>

					<div class="col-2">
						<select name="relation_membre_famille_1">
							<option></option>
							<option value="pere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_1 == 'pere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.pere')}}</option>
							<option value="mere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_1 == 'mere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.mere')}}</option>
							<option value="frere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_1 == 'frere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.frere')}}</option>
							<option value="soeur" {{$profil_familial !== null && $profil_familial->relation_membre_famille_1 == 'soeur' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.soeur')}}</option>
							<option value="autre" {{$profil_familial !== null && $profil_familial->relation_membre_famille_1 == 'autre' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.autre')}}</option>
						</select>
					</div>

					<div class="col-2">
						<select name="relation_membre_famille_2">
							<option></option>
							<option value="pere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_2 == 'pere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.pere')}}</option>
							<option value="mere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_2 == 'mere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.mere')}}</option>
							<option value="frere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_2 == 'frere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.frere')}}</option>
							<option value="soeur" {{$profil_familial !== null && $profil_familial->relation_membre_famille_2 == 'soeur' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.soeur')}}</option>
							<option value="autre" {{$profil_familial !== null && $profil_familial->relation_membre_famille_2 == 'autre' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.autre')}}</option>
						</select>
					</div>

					<div class="col-2">
						<select name="relation_membre_famille_3">
							<option></option>
							<option value="pere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_3 == 'pere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.pere')}}</option>
							<option value="mere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_3 == 'mere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.mere')}}</option>
							<option value="frere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_3 == 'frere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.frere')}}</option>
							<option value="soeur" {{$profil_familial !== null && $profil_familial->relation_membre_famille_3 == 'soeur' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.soeur')}}</option>
							<option value="autre" {{$profil_familial !== null && $profil_familial->relation_membre_famille_3 == 'autre' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.autre')}}</option>
						</select>
					</div>

					<div class="col-2">
						<select name="relation_membre_famille_4">
							<option></option>
							<option value="pere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_4 == 'pere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.pere')}}</option>
							<option value="mere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_4 == 'mere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.mere')}}</option>
							<option value="frere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_4 == 'frere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.frere')}}</option>
							<option value="soeur" {{$profil_familial !== null && $profil_familial->relation_membre_famille_4 == 'soeur' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.soeur')}}</option>
							<option value="autre" {{$profil_familial !== null && $profil_familial->relation_membre_famille_4 == 'autre' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.autre')}}</option>
						</select>
					</div>

					<div class="col-2">
						<select name="relation_membre_famille_5">
							<option></option>
							<option value="pere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_5 == 'pere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.pere')}}</option>
							<option value="mere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_5 == 'mere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.mere')}}</option>
							<option value="frere" {{$profil_familial !== null && $profil_familial->relation_membre_famille_5 == 'frere' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.frere')}}</option>
							<option value="soeur" {{$profil_familial !== null && $profil_familial->relation_membre_famille_5 == 'soeur' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.soeur')}}</option>
							<option value="autre" {{$profil_familial !== null && $profil_familial->relation_membre_famille_5 == 'autre' ? 'selected="selected"' : ''}}>{{traduction('module_sur_fiche.client.profil_familial.autre')}}</option>
						</select>
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.nom')
					</div>

					<div class="col-2">
						<input type="text" name="nom_membre_famille_1" value="@if($profil_familial !== null){{ $profil_familial->nom_membre_famille_1 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_membre_famille_2" value="@if($profil_familial !== null){{ $profil_familial->nom_membre_famille_2 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_membre_famille_3"  value="@if($profil_familial !== null){{ $profil_familial->nom_membre_famille_3 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_membre_famille_4"  value="@if($profil_familial !== null){{ $profil_familial->nom_membre_famille_4 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="nom_membre_famille_5"  value="@if($profil_familial !== null){{ $profil_familial->nom_membre_famille_5 }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.prenom')
					</div>

					<div class="col-2">
						<input type="text" name="prenom_membre_famille_1"  value="@if($profil_familial !== null){{ $profil_familial->prenom_membre_famille_1 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_membre_famille_2"  value="@if($profil_familial !== null){{ $profil_familial->prenom_membre_famille_2 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_membre_famille_3"  value="@if($profil_familial !== null){{ $profil_familial->prenom_membre_famille_3 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_membre_famille_4"  value="@if($profil_familial !== null){{ $profil_familial->prenom_membre_famille_4 }}@endif">
					</div>

					<div class="col-2">
						<input type="text" name="prenom_membre_famille_5"   value="@if($profil_familial !== null){{ $profil_familial->prenom_membre_famille_5 }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.date_naissance')
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_naissance_membre_famille_1" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_naissance_membre_famille_1) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_naissance_membre_famille_2" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_naissance_membre_famille_2) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_naissance_membre_famille_3" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_naissance_membre_famille_3) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_naissance_membre_famille_4" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_naissance_membre_famille_4) }}@endif">
					</div>

					<div class="col-2">
						<input type="text" class="datepicker" name="date_naissance_membre_famille_5" data-date-format="dd/mm/yyyy" value="@if($profil_familial !== null){{ formate_date('d/m/Y', $profil_familial->date_naissance_membre_famille_5) }}@endif">
					</div>
				</div>

				<div class="row">
					<div class="col-2">
						@traduction('module_sur_fiche.client.profil_familial.commentaire')
					</div>

					<div class="col-2">
						<textarea name="commentaire_membre_famille_1">@if($profil_familial !== null){{ $profil_familial->commentaire_membre_famille_1 }}@endif</textarea>
					</div>

					<div class="col-2">
						<textarea name="commentaire_membre_famille_2">@if($profil_familial !== null){{ $profil_familial->commentaire_membre_famille_2 }}@endif</textarea>
					</div>

					<div class="col-2">
						<textarea name="commentaire_membre_famille_3">@if($profil_familial !== null){{ $profil_familial->commentaire_membre_famille_3 }}@endif</textarea>
					</div>

					<div class="col-2">
						<textarea name="commentaire_membre_famille_4">@if($profil_familial !== null){{ $profil_familial->commentaire_membre_famille_4 }}@endif</textarea>
					</div>

					<div class="col-2">
						<textarea name="commentaire_membre_famille_5">@if($profil_familial !== null){{ $profil_familial->commentaire_membre_famille_5 }}@endif</textarea>
					</div>
				</div>
			</form>

			</div>
		</div>
	</div>
</div>



@push('donnees_pour_vuejs_data')
	statut_enregistrement_profil_familial: '{{traduction('module_sur_fiche.client.profil_familial.donnes_a_jour')}}',
@endpush

@push('donnees_pour_vuejs_methods')

	modifier_profil_familial: function() {

		this.statut_enregistrement_profil_familial = '{{traduction('module_sur_fiche.client.profil_familial.enregistrement_en_cours')}}';

		var contexte_vue = this;

		// on enregistre la modification
		$.post({

			url: 'eden/fiche/client/{{$client->id}}/post/enregistrer_profil_familial',
			dataType: "json",
			method: 'POST',
			data: $('#formulaire_profil_familial').serialize()
		}).done(function(donnees) {

			contexte_vue.statut_enregistrement_profil_familial = '{{traduction('module_sur_fiche.client.profil_familial.donnes_a_jour')}}';
		});

	},



@endpush

