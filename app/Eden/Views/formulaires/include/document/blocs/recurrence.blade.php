<div class="card mb-3">
	<div class="card-header">
		<h4>
			@traduction('document.recurrence.titre')
		</h4>
	</div>
	<div class="card-body">

		<input type="hidden" name="id_recurrence" v-model="recurrence.id" />

		<div class="row">
			<div class="col-sm-12">
				<label><input type="radio" name="recurrence_activee" v-model="recurrence.recurrence_activee" value="0" />
					<span v-if="recurrence.id > 0">@traduction('document.recurrence.desactiver')</span>
					<span v-else >@traduction('document.recurrence.ne_pas_activer')</span>
				</label>
			</div>
			<div class="col-sm-8">
				<label><input type="radio" name="recurrence_activee" v-model="recurrence.recurrence_activee" value="1" @change="activation_recurrence()"  />@traduction('document.recurrence.activer')</label>
			</div>
		</div>
		<div v-show="recurrence.recurrence_activee == 1">
			<div class="row">
				<div class="col-sm-6">
					<label><input :disabled="recurrence.id > 0" type="radio" name="mode_recurrence" v-model="recurrence.mode_recurrence" value="0" @change="activation_recurrence()" />@traduction('document.recurrence.chaque_mois')</label>
				</div>
				<template v-if="recurrence.mode_recurrence == 0">
					<div class="col-sm-4">
						@traduction('document.recurrence.date_occurence') :
					</div>
					<div class="col-sm-2" v-html="calcul_prochaine_recurrence()"></div>
				</template>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label><input :disabled="recurrence.id > 0" type="radio" name="mode_recurrence" v-model="recurrence.mode_recurrence" value="3" @change="activation_recurrence()" />@traduction('document.recurrence.trois_mois')</label>
				</div>
				<template v-if="recurrence.mode_recurrence == 3">
					<div class="col-sm-4">
						@traduction('document.recurrence.date_occurence') :
					</div>
					<div class="col-sm-2" v-html="calcul_prochaine_recurrence()"></div>
				</template>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label><input type="radio" :disabled="recurrence.id > 0" name="mode_recurrence" v-model="recurrence.mode_recurrence" value="2" @change="activation_recurrence()" />@traduction('document.recurrence.chaque_annee')</label>
				</div>
				<template v-if="recurrence.mode_recurrence == 2">
					<div class="col-sm-4">
						@traduction('document.recurrence.date_occurence') :
					</div>
					<div class="col-sm-2" v-html="calcul_prochaine_recurrence()"></div>
				</template>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<label><input type="radio" :disabled="recurrence.id > 0" name="mode_recurrence" v-model="recurrence.mode_recurrence" value="1" @change="activation_recurrence()" />@traduction('document.recurrence.periode_determinee')</label>
				</div>
				<template v-if="recurrence.mode_recurrence == 1">
					<div class="col-sm-4">
						@traduction('document.recurrence.date_occurence') :
					</div>
					<div class="col-sm-2" v-html="calcul_prochaine_recurrence()"></div>
				</template>
			</div>
			<div class="row" v-if="recurrence.mode_recurrence == 1">
				<div class="col-sm-6"></div>
				<div class="col-sm-4">
					@traduction('document.recurrence.date_derniere_occurence') :
				</div>
				<div class="col-sm-2" v-html="calcul_derniere_recurrence()"></div>
			</div>
			<div v-show="[0, '0', 3, '3'].includes(recurrence.mode_recurrence)">
				@traduction('document.recurrence.date_nouveau_document') :
				<select :disabled="recurrence.id > 0" name="rdi_date_generation" v-model="recurrence.rdi_date_generation">
					<option value="0">{{traduction('document.recurrence.dernier_jour')}}</option>
					<option></option>
					@for($i = 1; $i <= 31; $i++)
						<option value="{{ $i }}">{{ $i }}</option>
					@endfor
				</select><br/>
				@traduction('document.recurrence.modele') :
				<select :disabled="recurrence.id > 0" name="rdi_id_modele" v-model="recurrence.rdi_id_modele">
					<option value="0">{{traduction('document.recurrence.premier_document')}}</option>
					<option value="1">{{traduction('document.recurrence.dernier_document')}}</option>
				</select><br/>
			</div>
			<div v-show="recurrence.mode_recurrence == 2">
				@traduction('document.recurrence.date_nouveau_document') :
				<div class="row">
					<div class="col-sm-2">
						@traduction('document.recurrence.jour') :
					</div>
					<div class="col-sm-10">
						<select :disabled="recurrence.id > 0" name="rdi_date_generation" v-model="recurrence.rdi_date_generation">
							<option value="0">{{traduction('document.recurrence.dernier_jour')}}</option>
							<option></option>
							@for($i = 1; $i <= 31; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
							@endfor
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						@traduction('document.recurrence.mois') :
					</div>
					<div class="col-sm-10">
						<select :disabled="recurrence.id > 0" name="rdi_mois_generation" v-model="recurrence.rdi_mois_generation">
							@for($i = 1; $i <= 12; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
							@endfor
						</select>
					</div>
				</div>
				@traduction('document.recurrence.modele') :
				<select :disabled="recurrence.id > 0" name="rdi_id_modele" v-model="recurrence.rdi_id_modele">
					<option value="0">{{traduction('document.recurrence.premier_document')}}</option>
					<option value="1">{{traduction('document.recurrence.dernier_document')}}</option>
				</select><br/>
			</div>
			<div v-show="recurrence.mode_recurrence == 1">
				@traduction('document.recurrence.date_nouveau_document') :
				<template v-if="recurrence.rdd_periodicite == 0 || recurrence.rdd_periodicite == 3">
					<select :disabled="recurrence.id > 0" name="rdd_date_generation" v-model="recurrence.rdd_date_generation">
						<option value="0">{{traduction('document.recurrence.dernier_jour')}}</option>
						<option></option>
						@for($i = 1; $i <= 31; $i++)
							<option value="{{ $i }}">{{ $i }}</option>
						@endfor
					</select><br/>
				</template>
				<template v-if="recurrence.rdd_periodicite == 1">
					<div class="row">
						<div class="col-sm-2">
							@traduction('document.recurrence.jour') :
						</div>
						<div class="col-sm-10">
							<select name="rdd_date_generation" v-model="recurrence.rdd_date_generation">
								@for($i = 1; $i <= 31; $i++)
									<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-2">
							@traduction('document.recurrence.mois') :
						</div>
						<div class="col-sm-10">
							<select name="rdd_mois_generation" v-model="recurrence.rdd_mois_generation">
								@for($i = 1; $i <= 12; $i++)
									<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
					</div>
				</template>
				@traduction('document.recurrence.nombre') :
				<select :disabled="recurrence.id > 0" name="rdd_occurences" v-model="recurrence.rdd_occurences">
					@for($i = 1; $i <= 36; $i++)
						<option value="{{ $i }}">{{ $i }}</option>
					@endfor
				</select><br/>
				@traduction('document.recurrence.periodicite') :
				<select :disabled="recurrence.id > 0" name="rdd_periodicite" @change="changement_periodicite" v-model="recurrence.rdd_periodicite">
					<option value="0">{{traduction('document.recurrence.mensuelle')}}</option>
					<option value="3">{{traduction('document.recurrence.trimestrielle')}}</option>
					<option value="1">{{traduction('document.recurrence.annuelle')}}</option>
				</select><br/>
				Génération progressive :
				<select :disabled="recurrence.id > 0" name="generation_progressive" v-model="recurrence.generation_progressive">
					<option value="0">Non</option>
					<option value="1">Oui</option>
				</select><br/>
			</div>
			<div v-if="[0,2,3,'0','2','3'].includes(recurrence.mode_recurrence) || recurrence.generation_progressive == 1">
				Nombre de jours d'avance pour générer le document :
				<input type="number" name="delai_generation" v-model="recurrence.delai_generation" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
			</div>
		</div>
	</div>
</div>



@push('donnees_pour_vuejs_data')
	@if(!empty($recurrence))
		recurrence: {!! $recurrence !!},
	@else
		recurrence: {
			recurrence_activee: 0,
			mode_recurrence: 0,
			rdi_date_generation: 0,
			rdi_mois_generation: 0,
			rdd_date_generation: 0,
			rdd_mois_generation: 0,
			rdd_occurences: 0,
			rdd_periodicite: 0,
			rdi_id_modele: 1,
			delai_generation: 0,
			generation_progressive: 0,
		},
	@endif
@endpush

@push('donnees_pour_vuejs_methods')

	calcul_prochaine_recurrence : function(recuperer_date = false){

		if([0,2,3,'0','2','3'].includes(this.recurrence.mode_recurrence) && (this.recurrence.rdi_date_generation == undefined || this.recurrence.rdi_date_generation === '' || ((this.recurrence.rdi_mois_generation == undefined || this.recurrence.rdi_mois_generation == '') && this.recurrence.mode_recurrence == 2)))
			return '';

		if(this.recurrence.mode_recurrence == 1 && (this.recurrence.rdd_date_generation == undefined || this.recurrence.rdd_date_generation == '' || ((this.recurrence.rdd_mois_generation == undefined || this.recurrence.rdd_mois_generation == '') && this.recurrence.rdd_periodicite == 1)))
			return '';

		var date_prochaine_occurence = '';
		var prochaine_occurence = '';

		if(this.recurrence.rdi_prochaine_occurence != undefined ){
			var date_recurrence = new Date(this.recurrence.rdi_prochaine_occurence);

			var annee = date_recurrence.getFullYear();
			var mois = (date_recurrence.getMonth()+1).toString();

			if(mois.length == 1)
			mois = '0' + mois;

			var jour = date_recurrence.getDate().toString();

			if(jour.length == 1)
			jour = '0' + jour;

			prochaine_occurence = jour + '/' + mois + '/' + annee;

			return prochaine_occurence;
		}
		else
			var date_recurrence = new Date(this.document.date);

		var jour = date_recurrence.getDate();
		var mois = date_recurrence.getMonth() + 1;

		if(this.recurrence.mode_recurrence == 0){

			var ajout = 1;

			if(this.recurrence.rdi_date_generation == 0)
				ajout = 2;

			date_prochaine_occurence = new Date(date_recurrence.getFullYear(), date_recurrence.getMonth()+ajout, this.recurrence.rdi_date_generation);

		}

		else if(this.recurrence.mode_recurrence == 3){

			var ajout = 3;

			if(this.recurrence.rdi_date_generation == 0)
				ajout = 4;

			date_prochaine_occurence = new Date(date_recurrence.getFullYear(), date_recurrence.getMonth()+ajout, this.recurrence.rdi_date_generation);

		}

		else if (this.recurrence.mode_recurrence == 1){

			if(this.recurrence.rdd_periodicite == 0){

				var ajout = 1;

				if(this.recurrence.rdd_date_generation == 0)
					ajout = 2;

				date_prochaine_occurence = new Date(date_recurrence.getFullYear(), date_recurrence.getMonth()+ajout, this.recurrence.rdd_date_generation);

			}else if(this.recurrence.rdd_periodicite == 3){

				var ajout = 3;

				if(this.recurrence.rdd_date_generation == 0)
					ajout = 4;

				date_prochaine_occurence = new Date(date_recurrence.getFullYear(), date_recurrence.getMonth()+ajout, this.recurrence.rdd_date_generation);

			}
			else
				date_prochaine_occurence = new Date(date_recurrence.getFullYear() +1 , this.recurrence.rdd_mois_generation - 1, this.recurrence.rdd_date_generation);
		}

		else if(this.recurrence.mode_recurrence == 2) {

			if(this.recurrence.rdi_prochaine_occurence != undefined)
				date_prochaine_occurence = new Date(date_recurrence.getFullYear(), this.recurrence.rdi_mois_generation - 1, this.recurrence.rdi_date_generation);
			else
				date_prochaine_occurence = new Date(date_recurrence.getFullYear() + 1, this.recurrence.rdi_mois_generation - 1, this.recurrence.rdi_date_generation);
		}

		if(recuperer_date == true)
			return date_prochaine_occurence;

		if(date_prochaine_occurence != ''){

			var annee = date_prochaine_occurence.getFullYear();
			var mois = (date_prochaine_occurence.getMonth()+1).toString();

			if(mois.length == 1)
				mois = '0' + mois;

			var jour = date_prochaine_occurence.getDate().toString();

			if(jour.length == 1)
				jour = '0' + jour;

			prochaine_occurence = jour + '/' + mois + '/' + annee;

		}

		return prochaine_occurence;
	},

	calcul_derniere_recurrence : function(){

		var date_premiere_occurence = new Date(this.document.date);

		if(date_premiere_occurence == '' || this.recurrence.rdd_occurences == undefined || this.recurrence.rdd_occurences == '')
			return '';

		var date_derniere_occurence = '';
		var derniere_occurence = '';

		var ajout = 0;

		if(this.recurrence.rdd_date_generation == 0)
			ajout += 1;
		if(this.recurrence.rdd_periodicite == 3)
			ajout += this.recurrence.rdd_occurences * 3;

		if(this.recurrence.rdd_periodicite == 0 || this.recurrence.rdd_periodicite == 3)
			date_derniere_occurence = new Date(date_premiere_occurence.getFullYear(), date_premiere_occurence.getMonth()+ parseInt(this.recurrence.rdd_occurences) + ajout, this.recurrence.rdd_date_generation);
		else
			date_derniere_occurence = new Date(date_premiere_occurence.getFullYear() + parseInt(this.recurrence.rdd_occurences) , this.recurrence.rdd_mois_generation + ajout, this.recurrence.rdd_date_generation);

		if(date_derniere_occurence != ''){

			var annee = date_derniere_occurence.getFullYear();
			var mois = (date_derniere_occurence.getMonth()+1).toString();

			if(mois.length == 1)
				mois = '0' + mois;

			var jour = date_derniere_occurence.getDate().toString();

			if(jour.length == 1)
				jour = '0' + jour;

			derniere_occurence = jour + '/' + mois + '/' + annee;

		}

		return derniere_occurence;
	},

	activation_recurrence : function(){

		if(this.recurrence.id)
			return;

		var date_document = new Date(this.document.date);

		this.recurrence.rdi_id_modele = 1;
		this.recurrence.rdd_periodicite = 0;
		this.recurrence.rdd_occurences = 11;
		this.recurrence.rdi_date_generation = date_document.getDate();
		this.recurrence.rdi_mois_generation = date_document.getMonth() + 1;
		this.recurrence.rdd_date_generation = date_document.getDate();
		this.recurrence.rdd_mois_generation = date_document.getMonth() + 1;

		this.$forceUpdate();
	},

	changement_periodicite : function(){

		if(this.recurrence.rdd_periodicite == 0)
			this.recurrence.rdd_occurences = 11;
		else if(this.recurrence.rdd_periodicite == 3)
			this.recurrence.rdd_occurences = 3;
		else
			this.recurrence.rdd_occurences = 1;

		this.$forceUpdate();
	},
@endpush