<input type="hidden" name="parametrage_facturation_electronique" :value="JSON.stringify(parametrage_facturation_electronique)" />

<div class="css_parametrage_facturx">
	<div class="css_parametrage_facturx_bloc" v-for="type_element in types_elements_facturation_electronique" :key="type_element">

		<div class="css_parametrage_facturx_entete">
			<span class="css_parametrage_facturx_titre" v-html="$root.traduction('tables_libres.' + type_element + '.element_pluriel')"></span>
			<span class="css_parametrage_facturx_code" v-if="parametrage_facturation_electronique[type_element].type_code">@{{ parametrage_facturation_electronique[type_element].type_code }}</span>
		</div>

		<div class="css_parametrage_facturx_corps">

			<div class="css_parametrage_facturx_colonnes">
				<div>
					<label>@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.type_code')</label>
					<select class="form-control" v-model.number="parametrage_facturation_electronique[type_element].type_code">
						<option value="">-</option>
						<option v-for="valeur in $root.valeurs_listes_formatees[730]" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
					</select>
				</div>
				<div>
					<label>@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.cadre_facturation')</label>
					<select class="form-control" v-model="parametrage_facturation_electronique[type_element].cadre_facturation">
						<option value="">-</option>
						<option v-for="valeur in $root.valeurs_listes_formatees[731]" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
					</select>
				</div>
			</div>

			<div class="css_parametrage_facturx_mentions">

				<div class="css_parametrage_facturx_mentions_titre">@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.mentions_legales')</div>

				<div class="css_parametrage_facturx_mention">
					<label>@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.mention_indemnite_forfaitaire')</label>
					<textarea class="form-control" rows="2" v-model="parametrage_facturation_electronique[type_element].mention_indemnite_forfaitaire"></textarea>
				</div>

				<div class="css_parametrage_facturx_mention">
					<label>@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.mention_penalites_retard')</label>
					<textarea class="form-control" rows="2" v-model="parametrage_facturation_electronique[type_element].mention_penalites_retard"></textarea>
				</div>

				<div class="css_parametrage_facturx_mention">
					<label>@traduction('formulaires.modele_de_document.parametrage_facturation_electronique.mention_escompte')</label>
					<textarea class="form-control" rows="2" v-model="parametrage_facturation_electronique[type_element].mention_escompte"></textarea>
				</div>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	parametrage_facturation_electronique : {},
	champs_valeur_defaut : {
		cadre_facturation : 'M1',
		mention_indemnite_forfaitaire : this.$root.traduction('messages.php.facture_vente.parametrage_facturx.mention_indemnite_forfaitaire_defaut'),
		mention_penalites_retard : this.$root.traduction('messages.php.facture_vente.parametrage_facturx.mention_penalites_retard_defaut'),
		mention_escompte : this.$root.traduction('messages.php.facture_vente.parametrage_facturx.mention_escompte_defaut')
	},
	types_code_defaut : {
		facture_vente : 380,
		avoir_vente : 381,
	},
@endpush

@push('donnees_pour_vuejs_mounted')

	this.parametrage_facturation_electronique = JSON.parse(this.modele_de_document.parametrage_facturation_electronique ?? '{}');

	this.initialise_parametrage_facturation_electronique();

	this.$watch('types_elements_facturation_electronique', () => this.initialise_parametrage_facturation_electronique());
@endpush

@push('donnees_pour_vuejs_methods')

	initialise_parametrage_facturation_electronique(){

		for(type_element of this.types_elements_facturation_electronique){

			if(this.parametrage_facturation_electronique[type_element] === undefined)
				this.$set(this.parametrage_facturation_electronique, type_element, {});

			for(cle of Object.keys(this.champs_valeur_defaut)){

				if(this.parametrage_facturation_electronique[type_element][cle] === undefined)
					this.$set(this.parametrage_facturation_electronique[type_element], cle, this.champs_valeur_defaut[cle]);
			}

			if(this.parametrage_facturation_electronique[type_element].type_code === undefined)
				this.$set(this.parametrage_facturation_electronique[type_element], 'type_code', this.types_code_defaut[type_element]);
		}
	},
@endpush

@push('donnees_pour_vuejs_computed')

	types_elements_facturation_electronique(){

		var selection = this.modele_de_document.type_element || [];

		var ids_selectionnes = (Array.isArray(selection) ? selection : Object.keys(selection)).map(Number);

		return Object.keys(this.$root.ids_facturation_electronique_type_element)
			.filter(type_element => ids_selectionnes.includes(Number(this.$root.ids_facturation_electronique_type_element[type_element]))
				|| this.modele_de_document.type_element_autres == type_element);
	},
@endpush
