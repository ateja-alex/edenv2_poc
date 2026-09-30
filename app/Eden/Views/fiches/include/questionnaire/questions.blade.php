<div class="card mb-3">
	<div class="card-header js_fermeture_bloc">
		@if(isset($afficher_par_defaut))
			@if($afficher_par_defaut === true)
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-up"></span>
				</span>

			@else
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-down"></span>
				</span>
			@endif
		@endif
		<h4>
			@traduction('module_sur_fiche.questionnaire.questions.titre')
			<span class="css__lien" style="margin-left: 50px;" @click="modale_ajouter_question()">@traduction('module_sur_fiche.questionnaire.questions.ajouter')</span>
		</h4>
	</div>
	<div class="card-body" @if(isset($afficher_par_defaut) && $afficher_par_defaut === false) style="display: none;" @endif>

		<div class="row">
			<div class="col-sm-6">
				<b>{!! management('questionnaire_question')->champ('question')->nom_vue() !!}</b>
			</div>
			<div class="col-sm-2">
				<b>{!! management('questionnaire_question')->champ('type')->nom_vue() !!}</b>
			</div>
			<div class="col-sm-1">
				<b>{!! management('questionnaire_question')->champ('taille')->nom_vue() !!}</b>
			</div>
			<div class="col-sm-1">
				<b>{!! management('questionnaire_question')->champ('obligatoire')->nom_vue() !!}</b>
			</div>
			<div class="col-sm-2">
				<b>@traduction('module_sur_fiche.questionnaire.questions.options')</b>
			</div>
		</div>
		<draggable :list="questions" @end="enregistrer_ordre_questions">
			<div :key="question.id" :question_id="question.id" v-for="(question, index) in questions">
				<div class="row">
					<div class="col-sm-4">
						<i class="fas fa-arrows-alt"></i>
						<span class="css__lien" @click="modifier_question(question)">@{{ question.question }}</span>
					</div>
					<div class="col-sm-2">
						<a href="javascript:;" v-show="question.type == 5" @click="modifier_options_question(question);">@traduction('module_sur_fiche.questionnaire.questions.modifier_liste')</a>
					</div>
					<div class="col-sm-2">
						<span class="badge badge-default" :style="'background: '+couleurs_questions[question.type]">@{{ question.type | nom_valeur_liste_formatee(46) }}</span>
					</div>
					<div class="col-sm-1">
						<span v-html="question.taille"></span>
					</div>
					<div class="col-sm-1">
						<span v-show="question.type != 7" :class="'badge badge-'+(question.obligatoire ? 'success' : 'default')" @click="changement_obligatoire(question)">
							@{{ question.obligatoire | nom_valeur_liste_formatee(14) }}
						</span>
					</div>
					<div class="col-sm-2">
						<span class="css__lien" @click="supprimer_question(question, index)">@traduction('module_sur_fiche.questionnaire.questions.supprimer')</span>
					</div>
				</div>
			</div>
		</draggable>

	</div>
</div>

<!-- modale pour ajouter des questions -->
<div class="modal fade" id="modal_ajout_question" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.questionnaire.questions.titre_modal_ajouter')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="#" id="formulaire_ajout_question" method="post" class="css_form">
					<div class="row">
						@champ('questionnaire_question','question',6,6)
					</div>
					<div class="row">
						<div class="col-md-6">{!! management('questionnaire_question')->champ('type')->nom_vue() !!}</div>
						<div class="col-md-6">{!! management('questionnaire_question')->champ('type')->attr('@change','changement_type()')->cree() !!}</div>
					</div>
                    <div class="row" v-show="questionnaire_question.type == 1">
                        <div class="col-md-6">Choix du type de note</div>
                        <div class="col-md-4">
                            <select v-model="questionnaire_question.format">
                                <option :value=null>Sous forme d'étoiles</option>
                                <option value="net_promoter_score">Sous forme de Net Promoter Score</option>
                            </select>
                        </div>
						<div class="col-md-2" v-if="questionnaire_question.format == 'net_promoter_score'">
							@for($i = 1 ; $i <= 10 ; $i++)
								<img v-if="{{$i}} >= 1 && {{$i}} <= 6" src="{{asset('eden/images/pictos/nps_pas_content.png')}}" alt="{{ traduction('module_sur_fiche.questionnaire.questions.grimace') }}" style="width: 20px; height: 20px;">
								<img v-else-if="{{$i}} >= 7 && {{$i}} <= 8" src="{{asset('eden/images/pictos/nps_moyen_content.png')}}" alt="{{ traduction('module_sur_fiche.questionnaire.questions.neutre') }}" style="width: 20px; height: 20px;">
								<img v-else src="{{asset('eden/images/pictos/nps_content.png')}}" alt="{{ traduction('module_sur_fiche.questionnaire.questions.sourire') }}" style="width: 20px; height: 20px;">
							@endfor
						</div>
                        <template v-else>
                            <div class="col-md-1">
                                Nombre d'étoiles
                            </div>
                            <div class="col-md-1">
                                <select v-model="questionnaire_question.texte">
                                    @for($i = 1 ; $i <= 10 ; $i++)
                                        <option value="{{$i}}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </template>
                    </div>
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.questionnaire.questions.taille')</div>
						<div class="col-md-6" v-if="questionnaire_question.type != 7">{!! management('questionnaire_question')->champ('taille')->attr('max',12)->attr('min',1)->cree() !!}</div>
						<div class="col-md-6" v-else>{!! management('questionnaire_question')->champ('taille')->attr('disabled','true')->attr('max',12)->attr('min',1)->cree() !!}</div>
					</div>
					<div class="row" v-show="questionnaire_question.type != 7">
						@champ('questionnaire_question','obligatoire',6,6)
					</div>
					<div class="row" v-show="questionnaire_question.type == 7">
						@champ('questionnaire_question','texte',6,6)
					</div>
					<div class="row" v-show="questionnaire_question.type == 8">
						<div class="col-md-6">Checkbox</div>
						<div class="col-md-6">
							<div class="row">
								<div class="col-md-5">
									<b>Nom de la checkbox</b>
								</div>
								<div class="col-md-5">
									<b>Valeur de la checkbox</b>
								</div>
							</div>
							<div class="row" v-for="(checkbox, index) in questionnaire_question.checkbox">
								<div class="col-md-5">
									<input type="text" @change="calcul_nom_sql(checkbox.nom_checkbox, index)" v-model="checkbox.nom_checkbox" :placeholder="checkbox.nom_checkbox">
								</div>
								<div class="col-md-5">
									<input type="text" v-model="checkbox.valeur_checkbox" :placeholder="checkbox.valeur_checkbox">
								</div>
								<div class="col-md-2">
									<i class="far fa-trash-alt" style="cursor: pointer" @click="questionnaire_question.checkbox.splice(index, 1)"></i>
								</div>
							</div>
							<div class="row">
								<div class="col-md-12">
									<button type="button" class="btn btn-primary" @click="ajouter_checkbox">Ajouter checkbox</button>
								</div>
							</div>
						</div>
					</div>

					<template>
						<div class="row">
							<div class="col-md-12" style="display:flex;gap:10px;align-items:center;">
								<h5 class="modal-title">Affichage conditionnel</h5>
								<i v-if="questionnaire_question.affichage_conditionnel == null" class="fas fa-plus" @click="$set(questionnaire_question,'affichage_conditionnel',{})"></i>
								<i v-else class="far fa-trash-alt" style="cursor: pointer" @click="questionnaire_question.affichage_conditionnel = null"></i>
							</div>
						</div>

						<div class="row" v-if="questionnaire_question.affichage_conditionnel != null">
							<div class="col-md-2">
								Question concerné
							</div>
							<div class="col-md-2">
								<select @change="changement_champ_conditionnel" v-model="questionnaire_question.affichage_conditionnel.question_id">
									<option v-for="(question, index) in questions" v-if="questionnaire_question.id != question.id && question.type != 7 && question.type != 2" :value="question.id">@{{ question.question }}</option>
								</select>
							</div>
							<template v-if="questionnaire_question_conditionnel != null">
								<!--              NOTE                  -->
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 1">
									Opérateur
								</div>
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 1">
									<select v-model="questionnaire_question.affichage_conditionnel.operateur">
										<option value="==">Egale</option>
										<option value="!=">Différent</option>
										<option value="<">Inférieur à</option>
										<option value="<=">Strictement inférieur à</option>
										<option value=">">Supérieur à</option>
										<option value=">=">Strictement supérieur à</option>
									</select>
								</div>
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 1 || questionnaire_question_conditionnel.type == 6 || questionnaire_question_conditionnel.type == 8">
									Valeur souhaité
								</div>
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 1">
									<input v-model="questionnaire_question.affichage_conditionnel.valeur_a_tester" type="number" min="0" max="10" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
								</div>
								<template v-if="questionnaire_question_conditionnel.type == 5">
									<div class="col-md-2">
										Réponse
									</div>
									<div class="col-md-6" >

										<select v-model="questionnaire_question.affichage_conditionnel.valeur_a_tester">
											<option v-for="option in options_question[questionnaire_question.affichage_conditionnel.question_id]"
													:value="option.id">@{{ option.option }}</option>
										</select>
									</div>
								</template>
								<!--              DATE                -->
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 6">
									<select v-model="questionnaire_question.affichage_conditionnel.operateur">
										<option value="avant_le">Avant le</option>
										<option value="le">Le</option>
										<option value="apres_le">Après le</option>
									</select>
								</div>
								<div class="col-md-4" v-if="questionnaire_question_conditionnel.type == 6">
									<input type="date" v-model="questionnaire_question.affichage_conditionnel.valeur_a_tester">
								</div>
								<!--              CHOIX MULTIPLE                -->
								<div class="col-md-2" v-if="questionnaire_question_conditionnel.type == 8">
									<select v-model="questionnaire_question.affichage_conditionnel.operateur">
										<option value="contient">Contient</option>
										<option value="tout_sauf">Tout sauf</option>
									</select>
								</div>
								<div class="col-md-4" v-if="questionnaire_question_conditionnel.type == 8">
									<div class="row">
										<div class="col-md-12">
											<template v-for="checkbox in questionnaire_question_conditionnel.checkbox">
												<input type="checkbox" :value="checkbox.valeur_checkbox" v-model="questionnaire_question.affichage_conditionnel.valeur_a_tester">
												@{{ checkbox.nom_checkbox }}<br>
											</template>
										</div>
									</div>
								</div>
								<div class="col-md-4" v-if="questionnaire_question_conditionnel.type == 9">
									<input type="checkbox" v-model="questionnaire_question.affichage_conditionnel.valeur_a_tester">
								</div>
							</template>
						</div>
					</template>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" @click="ajouter_question">{{ traduction('interface.modales.enregistrer') }}</button>
			</div>
		</div>
	</div>
</div>

<!-- modale pour ajouter des options de réponse -->
<div class="modal fade" id="modal_options_question" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.questionnaire.questions.titre_modal_editer')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body css_form">
				<input type="hidden" name="questionnaire_id" value="{{ $management_element->modele->id }}" />

				<div style="display: flex;flex-direction: column;gap: 10px;">
					<div style="display:flex;align-items: center;gap: 15px;">
						<div style="width: 5%;">@traduction('module_sur_fiche.questionnaire.questions.ref')</div>
						<div style="width: 50%;">@traduction('module_sur_fiche.questionnaire.questions.nom')</div>
						<div></div>
					</div>
					<draggable style="display: flex;flex-direction: column;gap: 10px;" :list="options">

						<div style="display: flex;align-items: center;gap: 15px;" :key="option.id" v-for="(option, index) in options">
							<div style="width: 5%;">
								@{{ option.id }}
							</div>
							<div style="width: 50%;">
								<input type="text" class="form-control" v-model="option.option">
							</div>
							<div>
								<i @click="options.splice(index,1)" class="fa fa-trash" aria-hidden="true"></i>
							</div>
						</div>

					</draggable>
				</div>

				<div style="display: flex;margin-top: 20px;border-top: 0.5px solid lightgrey;padding-top: 20px;">

					<input v-model="nouvel_option.option" style="width: 40%;" type="text" class="form-control"  />
					<span class="css_action_icon" @click="ajouter_option_question()">
						<i class="fas fa-plus"></i>
					</span>

				</div>

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('module_sur_fiche.questionnaire.questions.fermer')</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_option">{{ traduction('interface.modales.enregistrer') }}</button>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	questions: {!! collect($questions) !!},
	options_question:{!! collect($options) !!},
	options:[],
	questionnaire_question_initial:{!! modele_par_defaut('questionnaire_question') !!},
	questionnaire_question:{!! modele_par_defaut('questionnaire_question') !!},
	nouvel_option_initial:{!! modele_par_defaut('questionnaire_question_option') !!},
	nouvel_option:{},
	couleurs_questions : {
		1 : '#f4e956',
		2 : '#adc0e4',
		3 : '#f2c646',
		4 : '#f47658',
		5 : '#76f458',
		6 : '#F075E5',
		7 : '#D7C2C2',
		8 : '#E4B3E7'
	},
@endpush

@push('donnees_pour_vuejs_methods')

	enregistrer_ordre_questions : function(){

		var ordre_questions = this.questions.map(function(question) {
			return question.id;
		});

		$.post({
			url: '{{ URL::to('eden/fiche/questionnaire') }}/'+vue_instance.questionnaire.id+'/post/ordre_questions',
			dataType: "json",
			data:{
				'ordres':ordre_questions
			}
		});

	},

	modale_ajouter_question: function() {

		this.questionnaire_question = structuredClone(this.questionnaire_question_initial);
		this.questionnaire_question.questionnaire_id = this.questionnaire.id;
		this.questionnaire_question.ordre = this.questions.length;
		this.questionnaire_question.taille = 6;
		this.questionnaire_question.affichage_conditionnel = null;
		this.questionnaire_question.format = null;

		$('#modal_ajout_question').modal('show')
	},

	modifier_question: function(question) {

		this.questionnaire_question = question;

		$('#modal_ajout_question').modal('show');
	},

	ajouter_question: function(loader = true) {

		var vue_contexte = this;

		if(loader)
			loading(true);

		var url = '{{ route('base_eden.element.creer', ['questionnaire_question']) }}';

		if(this.questionnaire_question.id != undefined)
			var url = '{{ URL::to('eden/element/questionnaire_question') }}/'+this.questionnaire_question.id+'/enregistrer';

		$.post({
			data:vue_instance.questionnaire_question,
			url: url,
			dataType: "json",
		}).done(async function(donnees) {

			// On retire le loader
			if(loader)
				loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
	
			if(vue_contexte.questionnaire_question.id == undefined){

				if(donnees.element.type == 8 && donnees.element.checkbox != null)
					donnees.element.checkbox = JSON.parse(donnees.element.checkbox);

				if(donnees.element.affichage_conditionnel != null && donnees.element.affichage_conditionnel != '')
					donnees.element.affichage_conditionnel = JSON.parse(donnees.element.affichage_conditionnel);

				vue_contexte.questions.push(donnees.element);

			}

			$('#modal_ajout_question').modal('hide');
		});
	},

	supprimer_question: function(question, index) {

		var vue_contexte = this;

		loading(true);

		$.get({

			url: '{{ URL::to('eden/element/questionnaire_question') }}/'+question.id+'/supprimer',
			dataType: "json",
		}).done(async function(donnees) {

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			vue_contexte.questions.splice(index,1);
		});
	},

    changement_type : function(){

		var questionnaire = this.questionnaire_question;

		if(questionnaire.type == 7)
			questionnaire.taille = 12;

		this.$forceUpdate();
	},

	ajouter_checkbox: function() {

		var nouvelle_checkbox = {
			nom_checkbox: 'Nom checkbox affiché',
			valeur_checkbox: 'valeur_checkbox',
		};

		if(!this.questionnaire_question.checkbox)
			this.$set(this.questionnaire_question,'checkbox',[]);

		this.questionnaire_question.checkbox.push(nouvelle_checkbox);
	},

    calcul_nom_sql: function(nom, index) {

		var vue_contexte = this;

		var nom_sql = nom;

		// on crée le nom_sql
		var accents = [

			/[\300-\306]/g, /[\340-\346]/g, // A, a
			/[\310-\313]/g, /[\350-\353]/g, // E, e
			/[\314-\317]/g, /[\354-\357]/g, // I, i
			/[\322-\330]/g, /[\362-\370]/g, // O, o
			/[\331-\334]/g, /[\371-\374]/g, // U, u
			/[\321]/g, /[\361]/g, // N, n
			/[\307]/g, /[\347]/g, // C, c

		];

		var sans_accents =['A','a','E','e','I','i','O','o','U','u','N','n','C','c'];

		for(var i = 0; i < accents.length; i++){

			nom_sql = nom_sql.replace(accents[i], sans_accents[i]);

		}

		nom_sql = nom_sql.toLowerCase();

		// autres caractères spéciaux
		var a_remplacer = [/[\41-\57]/g, /[\72-\100]/g, /[\133-\140]/g, /[\173-\176]/g, /¤/g, /£/g, /§/g, /µ/g, /¨/g, /;/g, /°/g,/ /g,/’/g];

		for(var n = 0; n < a_remplacer.length; n++){

			nom_sql = nom_sql.replace(a_remplacer[n], "_");

		}

		vue_contexte.questionnaire_question_checkbox[index].valeur_checkbox=nom_sql;

	},

	modifier_options_question: function(question) {

		this.questionnaire_question = question;

		this.options = [];
	
		if(this.options_question[question.id] != undefined)
			this.options = this.options_question[question.id];

		this.nouvel_option = structuredClone(this.nouvel_option_initial);

		this.nouvel_option.question_id = question.id;

		$('#modal_options_question').modal();
	},

	ajouter_option_question: function() {

		this.options.push(structuredClone(this.nouvel_option));

		this.nouvel_option = structuredClone(this.nouvel_option_initial);

		this.nouvel_option.question_id = question.id;
	},

	enregistrer_option:function(){

		var vue_contexte = this;

		loading(true);

		$.ajax({
			url: '{{ URL::to('eden/fiche/questionnaire') }}/'+vue_instance.questionnaire.id+'/enregistrer_options',
			dataType: "json",
			data:{
				options:vue_contexte.options,
				question_id:vue_contexte.questionnaire_question.id
			}
		}).done(function(donnees){

			// On retire le loader
			loading(false);

			if(donnees.retour !== true) {

				erreur(donnees.retour);
				return;
			}

			$.each(donnees.nouvels_id,function(ordre,nouvel_id){

				vue_contexte.options[ordre].id = nouvel_id;
				vue_contexte.options_question[vue_contexte.questionnaire_question.id] = structuredClone(vue_contexte.options);
			});

			$('#modal_options_question').modal('hide');
		});
	},

	changement_champ_conditionnel:function(){

		if(this.questionnaire_question_conditionnel == null)
			return;

		if(this.questionnaire_question_conditionnel.type == 8)
			this.questionnaire_question.affichage_conditionnel.valeur_a_tester = [];
		else
			delete this.questionnaire_question.affichage_conditionnel.valeur_a_tester;

	},

	changement_obligatoire : function(question){

		question.obligatoire = question.obligatoire == 1 ? 0 : 1;

		this.questionnaire_question = question;

		this.ajouter_question(false);
	},
    
@endpush

@push('donnees_pour_vuejs_computed')

	questionnaire_question_conditionnel : function(){

		var questionnaire_question_conditionnel = null;

		if(this.questionnaire_question != undefined &&
			this.questionnaire_question.affichage_conditionnel != undefined &&
			this.questionnaire_question.affichage_conditionnel.question_id != undefined){

			for(question of this.questions){

				if(question.id == this.questionnaire_question.affichage_conditionnel.question_id)
					questionnaire_question_conditionnel = question;
			}
		}

		return questionnaire_question_conditionnel;
	},
@endpush
