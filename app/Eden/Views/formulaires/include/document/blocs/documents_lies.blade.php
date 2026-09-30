@if(fonctionnalite('gescom_afficher_recap_documents_lies_avec_details') === false)
	<div v-if="documents_lies_affichees.length > 0" class="card mb-3 dl-carte">
		<div class="card-header">
			<h4>@traduction('document.blocs.documents_lies.titre')</h4>
			<span class="dl-compteur" v-if="documents_lies_affichees.length > 0">@{{ documents_lies_affichees.length }}</span>
		</div>
		<div class="card-body">

			<div class="dl-liste-simple">

				<template v-for="document of documents_lies_affichees">

					<div class="dl-ligne-simple dl-ligne-simple-recurrente" v-if="est_representant_recurrence(document)">
						<div class="dl-ligne-simple-recurrente-toggle" @click="bascule_recurrence(document.modele.id_recurrence)" :aria-expanded="!!recurrence_ouverte[document.modele.id_recurrence]">
							<i class="fas fa-sync-alt dl-ligne-simple-icone"></i>
							<span class="dl-ligne-simple-type">@{{ document.type_nom }}</span>
							<span v-html="document.affichage_lien"></span>
							<span>du @{{ document.affichage_champs.date }}</span>
							<i class="fas fa-chevron-down dl-icone-chevron"></i>
						</div>

						<div class="dl-recurrence-collapse" :class="{ ouvert: !!recurrence_ouverte[document.modele.id_recurrence] }">
							<a class="dl-recurrence-item" :class="{ actuel: element_actuel(document_recurrence.type_element, document_recurrence.modele.id) }" v-for="document_recurrence of recurrence_du_document(document)" :href="element_actuel(document_recurrence.type_element, document_recurrence.modele.id) ? 'javascript:void(0)' : ('{{ URL::to('/eden/document') }}/' + document_recurrence.type_element + '/' + document_recurrence.modele.id)">
								<span class="dl-recurrence-item-reference">@{{ document_recurrence.modele.reference_document }}</span>
								<span class="dl-recurrence-item-date">@{{ document_recurrence.affichage_champs.date }}</span>
								<span class="dl-recurrence-item-montant">
									@if(fonctionnalite('gescom_documents_lies_montant_en_ht_ou_ttc') == 'HT')
										@{{ document_recurrence.modele.montant_document_ht | montant }}
									@else
										@{{ document_recurrence.modele.montant_document_ttc | montant }}
									@endif
								</span>
							</a>
						</div>
					</div>

					<div class="dl-ligne-simple" :class="{ 'lien-direct': document.lien_direct }" v-else>
						<i class="fas fa-link dl-ligne-simple-icone"></i>
						<span class="dl-ligne-simple-type">@{{ document.type_nom }}</span>
						<span v-html="document.affichage_lien"></span>
						<span>du @{{ document.affichage_champs.date }}</span>
						<span class="dl-ligne-simple-montant">@{{ document.affichage_champs.montant_document_ttc }} {{ maquette('devise_application_symbole') }} TTC</span>
					</div>
				</template>
			</div>
		</div>
	</div>
@else
	<div v-if="documents_lies_affichees.length > 1" class="card mb-3 documents_lies dl-carte">
		<div class="card-header">
			<h4>@traduction('document.blocs.documents_lies.titre')</h4>
			<div class="dl-segmente">
				<span class="dl-segmente-curseur" :class="{ tout: afficher_tous_documents_lies }"></span>
				<button type="button" :class="{ actif: !afficher_tous_documents_lies }" @click="afficher_tous_documents_lies = false">Liés</button>
				<button type="button" :class="{ actif: afficher_tous_documents_lies }" @click="afficher_tous_documents_lies = true">Tout</button>
			</div>
		</div>
		<div class="card-body">

			<div class="dl-grille" v-if="documents_lies_affichees.length > 1">
				<div :class="bloc_documents_lies.length === 1 ? 'dl-colonne' : 'dl-colonne-multiple'" v-for="(bloc_documents_lies, index_bloc) in documents_lies_par_bloc" :ref="'bloc_documents_lies_' + index_bloc">
					<template v-for="type_element_bloc of bloc_documents_lies">
						<div :class="bloc_documents_lies.length > 1 ? 'dl-colonne' : ''">
							<div :style="type_element_bloc.documents.length > 0 ? '' : 'opacity: 0.35;'">
								<div class="dl-colonne-entete" :ref="'entete_dl_' + type_element_bloc.type_element">
									<div class="dl-colonne-entete-libelle">
										<span v-html="$root.traduction('tables_libres.'+type_element_bloc.type_element+'.nom_table')"></span>
									</div>
									<div class="dl-colonne-entete-montant">@{{ type_element_bloc.total ?? 0 }} {{ maquette('devise_application_symbole') }}</div>
								</div>

								<div class="dl-colonne-corps" :ref="'corps_dl_' + type_element_bloc.type_element">

									<div class="dl-mouvement-haut" v-if="index_debut[type_element_bloc.type_element] > 0" @click="mouvement_documents_lies(type_element_bloc.type_element, -1)">
										<a class="fas fa-chevron-up"></a>
										<span class="dl-mouvement-montant" v-html="montants_avant[type_element_bloc.type_element]"></span>
									</div>

									<template v-for="(document_bloc, index_document_lie) in type_element_bloc.documents">
										<div class="dl-doc" :class="{ actuel: element_actuel(type_element_bloc.type_element, document_bloc.modele.id), recurrent: est_representant_recurrence(document_bloc), 'lien-direct': document_bloc.lien_direct }" :document_id="document_bloc.modele.id" :ref="index_document_lie === index_debut[type_element_bloc.type_element] ? ('doc_dl_' + type_element_bloc.type_element) : null" v-if="index_debut[type_element_bloc.type_element] <= index_document_lie && index_document_lie <= index_fin[type_element_bloc.type_element]">

											<template v-if="est_representant_recurrence(document_bloc)">

												<div class="dl-doc-toggle" @click="bascule_recurrence(document_bloc.modele.id_recurrence)" :aria-expanded="!!recurrence_ouverte[document_bloc.modele.id_recurrence]">
													<div class="dl-doc-ligne1">
														<span class="dl-doc-reference" v-if="document_bloc.affichage_champs.fournisseur_id && type_element.includes('vente')">
															@{{ document_bloc.affichage_champs.fournisseur_id }} -
														</span>
														<span class="dl-doc-reference" v-if="document_bloc.affichage_champs.client_id && type_element.includes('achat')">
															@{{ document_bloc.affichage_champs.client_id }} -
														</span>
														<span class="dl-doc-reference">
															<i class="fas fa-sync-alt dl-icone-recurrence"></i>@{{ document_bloc.modele.reference_document }}
														</span>
														<span class="dl-doc-date">@{{ document_bloc.affichage_champs.date }}</span>
													</div>
													<div class="dl-doc-ligne2">
														<span>
															@if(fonctionnalite('gescom_documents_lies_montant_en_ht_ou_ttc') == 'HT')
																@{{ document_bloc.modele.montant_document_ht | montant }}
															@else
																@{{ document_bloc.modele.montant_document_ttc | montant }}
															@endif
														</span>
														<span v-if="document_bloc.badge != ''">
															<component class="statuts" :is="affichage_badge_document_lie(document_bloc)"></component>
														</span>
													</div>
													<i class="fas fa-chevron-down dl-icone-chevron"></i>
												</div>

												<div class="dl-recurrence-collapse" :class="{ ouvert: !!recurrence_ouverte[document_bloc.modele.id_recurrence] }">
													<div class="dl-recurrence-item" :class="{ actuel: element_actuel(document.type_element, document.modele.id) }" v-for="document of recurrence_du_document(document_bloc)">
														<div class="dl-doc-ligne1">
															<span class="dl-recurrence-item-reference" v-if="document_bloc.affichage_champs.fournisseur_id && type_element.includes('vente')">
																@{{ document_bloc.affichage_champs.fournisseur_id }} -
															</span>
															<span class="dl-recurrence-item-reference" v-if="document_bloc.affichage_champs.client_id && type_element.includes('achat')">
																@{{ document_bloc.affichage_champs.client_id }} -
															</span>
															<a :href="element_actuel(document.type_element, document.modele.id) ? 'javascript:void(0)' : ('{{ URL::to('/eden/document') }}/' + document.type_element + '/' + document.modele.id)">
																@{{ document.modele.reference_document }}
															</a>
															<span class="dl-recurrence-item-date">
																@{{ document.affichage_champs.date }}
															</span>
														</div>
														<div class="dl-doc-ligne2">
															<span>
																@if(fonctionnalite('gescom_documents_lies_montant_en_ht_ou_ttc') == 'HT')
																	@{{ document.modele.montant_document_ht | montant }}
																@else
																	@{{ document.modele.montant_document_ttc | montant }}
																@endif
															</span>
														</div>
													</div>
												</div>

											</template>
											<template v-else>

												<div class="dl-doc-ligne1">
													<a class="dl-doc-reference" :href="'{{ URL::to('/eden/document') }}/' + type_element_bloc.type_element + '/' + document_bloc.modele.id">
														<span class="dl-doc-reference" v-if="document_bloc.affichage_champs.fournisseur_id && type_element.includes('vente')">
															@{{ document_bloc.affichage_champs.fournisseur_id }} -
														</span>
														<span class="dl-doc-reference" v-if="document_bloc.affichage_champs.client_id && type_element.includes('achat')">
															@{{ document_bloc.affichage_champs.client_id }} -
														</span>
														<span class="dl-doc-reference">
															@{{ document_bloc.modele.reference_document }}
														</span>
													</a>
													<span class="dl-doc-date">@{{ document_bloc.affichage_champs.date }}</span>
												</div>
												<div class="dl-doc-ligne2">
													<span>
														@if(fonctionnalite('gescom_documents_lies_montant_en_ht_ou_ttc') == 'HT')
															@{{ document_bloc.modele.montant_document_ht | montant }}
														@else
															@{{ document_bloc.modele.montant_document_ttc | montant }}
														@endif
													</span>
													<span v-if="document_bloc.badge != ''">
														<component class="statuts" :is="affichage_badge_document_lie(document_bloc)"></component>
													</span>
												</div>

											</template>
										</div>
									</template>

									<div class="dl-mouvement-bas" v-if="index_fin[type_element_bloc.type_element] < type_element_bloc.documents.length - 1" @click="mouvement_documents_lies(type_element_bloc.type_element, 1)">
										<span class="dl-mouvement-montant" v-html="montants_apres[type_element_bloc.type_element]"></span>
										<a class="fas fa-chevron-down"></a>
									</div>
								</div>
							</div>
						</div>
					</template>
				</div>
			</div>
		</div>
	</div>
@endif

@push('donnees_pour_vuejs_data')

	documents_lies: {!! collect($documents_lies) !!},
	documents_par_recurrence: {!! collect($documents_par_recurrence ?? []) !!},
	blocs_documents_lies : {!! collect(service('interface')->blocs_documents_lies($management->est_une_vente())) !!},
	recurrence_ouverte : {},
	index_debut : {},
	index_fin : {},
	afficher_tous_documents_lies : localStorage.getItem('documents_lies_afficher_tous') === '1',
@endpush

@push('donnees_pour_vuejs_methods')

	element_actuel : function(type_element, id_document_lie){

		if(type_element == this.$root.type_element && this.$root.id_element != undefined && this.$root.id_element == id_document_lie)
			return true;

		return false;

	},

	montant_du_document : function(document){

		if(document == undefined || document.modele == undefined)
			return 0;

		@if(fonctionnalite('gescom_documents_lies_montant_en_ht_ou_ttc') == 'HT')
			var montant = parseFloat(document.modele.montant_document_ht);
		@else
			var montant = parseFloat(document.modele.montant_document_ttc);
		@endif

		return isNaN(montant) ? 0 : montant;
	},

	recurrence_du_document : function(document){

		var id_recurrence = document.modele.id_recurrence;

		if(!id_recurrence || this.documents_par_recurrence[id_recurrence] == undefined)
			return [];

		return this.documents_par_recurrence[id_recurrence];
	},

	est_representant_recurrence : function(document){

		var documents_recurrence = this.recurrence_du_document(document);

		if(documents_recurrence.length == 0)
			return false;

		// le représentant est la 1ère occurence de la récurrence (celle qui porte le collapse)
		return documents_recurrence[0].type_element == document.type_element && documents_recurrence[0].modele.id == document.modele.id;
	},

	bascule_recurrence : function(id_recurrence){

		this.$set(this.recurrence_ouverte, id_recurrence, !this.recurrence_ouverte[id_recurrence]);

		// le dépliage/repliage de la récurrence anime sa hauteur (transition CSS de .25s), on attend la fin
		// de la transition avant de remesurer les colonnes, sans quoi la hauteur lue serait celle en cours d'animation
		var self = this;
		setTimeout(function(){
			self.recalcule_hauteurs_documents_lies();
		}, 300);
	},

	// calcule, pour chaque colonne de documents_lies_par_bloc, le nombre de documents affichables
	// selon la hauteur réellement disponible (une colonne peut être étirée par le CSS grid quand une
	// récurrence dépliée dans une autre colonne de la même ligne agrandit toute la ligne)
	recalcule_hauteurs_documents_lies : function(){

		var self = this;

		// on repart d'une hauteur de référence (3 documents mini) avant de mesurer l'espace disponible,
		// sans quoi une colonne agrandie une fois resterait agrandie même après repliage de la récurrence
		for(type_element in self.documents_lies_par_type_element){

			var documents = self.documents_lies_par_type_element[type_element];
			var index_debut = self.index_debut[type_element] ?? 0;

			self.$set(self.index_fin, type_element, Math.min(documents.length - 1, index_debut + 2));
		}

		this.$nextTick(function(){

			self.documents_lies_par_bloc.forEach(function(bloc, index_bloc){

				// tous ces refs sont déclarés à l'intérieur de v-for : Vue les expose donc toujours comme
				// des tableaux (refInFor), même si une seule instance pousse effectivement sous cette clé
				var bloc_ref = (self.$refs['bloc_documents_lies_' + index_bloc] || [])[0];

				if(!bloc_ref)
					return;

				// hauteur naturelle (non étirée) de la colonne = somme des en-têtes + corps de chaque type_element empilé
				var hauteur_naturelle = bloc.length > 1 ? (bloc.length - 1) * 10 : 0; // gap de .dl-colonne-multiple
				var dernier_type_element = null;

				bloc.forEach(function(type_element_bloc){

					var entete = (self.$refs['entete_dl_' + type_element_bloc.type_element] || [])[0];
					var corps = (self.$refs['corps_dl_' + type_element_bloc.type_element] || [])[0];

					if(!entete || !corps)
						return;

					hauteur_naturelle += entete.offsetHeight + corps.offsetHeight;
					dernier_type_element = type_element_bloc.type_element;
				});

				// l'espace laissé libre par l'étirement de la grille apparaît sous le dernier type_element empilé
				if(!dernier_type_element)
					return;

				var doc_reference = (self.$refs['doc_dl_' + dernier_type_element] || [])[0];

				if(!doc_reference)
					return;

				var hauteur_document = doc_reference.offsetHeight + parseFloat(window.getComputedStyle(doc_reference).marginBottom || 0);
				var hauteur_supplementaire = bloc_ref.clientHeight - hauteur_naturelle;

				var documents_supplementaires = Math.floor(hauteur_supplementaire / hauteur_document);

				if(documents_supplementaires <= 0)
					return;

				var documents = self.documents_lies_par_type_element[dernier_type_element] ?? [];
				var index_fin_reference = self.index_fin[dernier_type_element];

				self.$set(self.index_fin, dernier_type_element, Math.min(documents.length - 1, index_fin_reference + documents_supplementaires));
			});
		});
	},

	mouvement_documents_lies : function(type_element, mouvement){
		this.index_fin[type_element] += mouvement;
		this.index_debut[type_element] += mouvement;
	},


	affichage_badge_document_lie : function(document_lie_retouche){
		var component = {
			template:'<div>'+document_lie_retouche.badge+'</div>',
			methods:this.$options.methods,
			data: function(){
				return{
					document: document_lie_retouche,
				}
			},
		};

		return component;
	},

	gestion_documents_lies : function(){

		this.index_debut = {};
		this.index_fin = {};

		for(type_element of Object.keys(this.documents_lies_par_type_element)){

			if(type_element == this.$root.type_element){

				index = this.documents_lies_par_type_element[type_element].findIndex(document => document.modele.id == this.$root.document.id);

				if(index == 0)
					index = 1;

				if(index == this.documents_lies_par_type_element[type_element].length - 1)
					index = this.documents_lies_par_type_element[type_element].length - 2;

				this.$set(this.index_debut,type_element,index - 1);
				this.$set(this.index_fin,type_element,index + 1);
			}
			else{
				this.$set(this.index_debut,type_element,0);
				this.$set(this.index_fin,type_element,2);
			}
		}
	},

@endpush

@push('donnees_pour_vuejs_mounted')

	this.gestion_documents_lies();
	this.recalcule_hauteurs_documents_lies();
@endpush

@push('donnees_pour_vuejs_watch')

	afficher_tous_documents_lies : function(nouvelle_valeur){

		// la fenêtre de pagination (index_debut/index_fin) de chaque colonne a été calculée sur
		// l'ancienne liste filtrée : sans ce recalcul, basculer vers "Tout" n'affiche aucun des
		// documents nouvellement inclus tant qu'on ne fait pas défiler la colonne
		this.gestion_documents_lies();
		this.recalcule_hauteurs_documents_lies();

		localStorage.setItem('documents_lies_afficher_tous', nouvelle_valeur ? '1' : '0');
	},
@endpush

@push('donnees_pour_vuejs_computed')

	montants_avant: function(){

		var montants_avant = {};

		for(type_element of Object.keys(this.documents_lies_par_type_element)){

			var montant = 0;

			for(index in this.documents_lies_par_type_element[type_element]){

				var document = this.documents_lies_par_type_element[type_element][index];

				if(index < this.index_fin[type_element]){

					montant += this.montant_du_document(document);
				}

			}

			montants_avant[type_element] = montant.toFixed(2) + ' {{ maquette('devise_application_symbole') }}';

		}

		return montants_avant;

	},

	montants_apres: function(){

		var montants_apres = {};

		for(type_element of Object.keys(this.documents_lies_par_type_element)){

			var montant = 0;

			for(index in this.documents_lies_par_type_element[type_element]){

				var document = this.documents_lies_par_type_element[type_element][index];

				if(index > this.index_fin[type_element]){

					montant += this.montant_du_document(document);
				}
			}

			montants_apres[type_element] = montant.toFixed(2) + ' {{ maquette('devise_application_symbole') }}';
		}

		return montants_apres;
	},

	documents_lies_par_bloc : function(){

		var documents_lies_par_bloc = [];

		for(types_elements of this.blocs_documents_lies){

			var bloc = [];

			for(type_element of types_elements){

				var documents = this.documents_lies_par_type_element[type_element] ?? [];

				var total = documents.reduce((total, document) => total + this.montant_du_document(document), 0);

				bloc.push({
					'type_element': type_element,
					'total': total.toFixed(2),
					'documents': documents
				});

			}

			documents_lies_par_bloc.push(bloc);

		}

		return documents_lies_par_bloc;
	},

	documents_lies_affichees : function(){

		var self = this;

		// une récurrence n'est affichée qu'une fois (sous forme de collapse porté par sa 1ère occurence),
		// on masque donc les autres occurences pour éviter de les afficher une seconde fois sous forme de documents classiques
		return this.documents_lies.filter(function(document){

			if(!self.afficher_tous_documents_lies && !document.lien_direct)
				return false;

			var id_recurrence = document.modele.id_recurrence;

			if(!id_recurrence || self.documents_par_recurrence[id_recurrence] == undefined)
				return true;

			return self.est_representant_recurrence(document);
		});
	},

	documents_lies_par_type_element : function(){

		var documents_lies_par_type_element = {};

		var documents_lies = this.documents_lies_affichees;

		documents_lies = documents_lies.sort(function(a,b){

			if(a.modele.date == b.modele.date)
				return a.modele.id - b.modele.id;

			return new Date(a.modele.date) - new Date(b.modele.date);
		});

		for(document_lie of documents_lies){

			if(documents_lies_par_type_element[document_lie.type_element] == undefined)
				documents_lies_par_type_element[document_lie.type_element] = [];

			documents_lies_par_type_element[document_lie.type_element].push(document_lie);

		}

		return documents_lies_par_type_element;
	},
@endpush
