<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.email_recus.traitement_du_mail')</div>
</div>
<div class="row">
	@champ('email_recus', 'public', 6,6)
</div>
<div class="row">
	@champ('email_recus', 'traite', 6,6)
</div>
<div class="row">
	@champ('email_recus', 'en_charge', 6,6)
</div>
<div class="row">
	@champ('email_recus', 'client_id', 6,6)
</div>
<div class="row">
	@champ('email_recus', 'projet_id', 6,6)
</div>
<div v-if="email_recus.id != ''">
	<div class="row">
		<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.email_recus.email')</div>
	</div>
	<div class="row">
		<div class="col-sm-4">{!! management('email_recus')->champ('from')->nom() !!}</div>
		<div class="col-sm-8"><b>@{{ email_recus.from }}</b></div>
	</div>
	<div class="row">
		<div class="col-sm-4">@traduction('formulaire.email_recus.expediteur')</div>
		<div class="col-sm-8"><b></b></div>
	</div>

	<div class="row">
		<div class="col-sm-4">{!! management('email_recus')->champ('sujet')->nom() !!}</div>
		<div class="col-sm-8"><b>@{{ email_recus.sujet }}</b></div>
	</div>
	<div class="row">
		<div class="col-sm-4">@traduction('formulaire.email_recus.pieces_jointes')</div>
		<div class="col-sm-8">
			<template v-if="email_recus.pieces_jointes_charges != 1">
				<span class="css_pointer" @click="charger_pieces_jointes">
					<i class="fas fa-sync"></i>
					@traduction('interface.email_recus.charger_pieces_jointes')
				</span>
			</template>
			<template v-else v-for="piece_jointe in pieces_jointes">
				<a :href="'storage/email_recus/'+piece_jointe.fichier" target="_blank">
					<i class="fa fa-fw fa-download"></i>
					@{{ piece_jointe.nom }}
				</a>
			</template>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-4">{!! management('email_recus')->champ('date')->nom() !!}</div>
		<div class="col-sm-8"><b>@{{ email_recus.date }}</b></div>
	</div>
	<div class="row">
		<div class="col-sm-12" v-html="email_recus.texte_html == null ? $root.nl2br(email_recus.texte) : email_recus.texte_html" style="line-height: 15px;"></div>
	</div>
</div>

@push('donnees_pour_vuejs_computed')

	pieces_jointes : function(){

		try{
			return JSON.parse(this.email_recus.pieces_jointes);
		}
		catch(e){
			return [];
		}
	},
@endpush

@push('donnees_pour_vuejs_methods')

	charger_pieces_jointes : function(){

		loading(true);

		$.post({
			url : 'eden/email_recus/'+this.email_recus.id+'/charger_pieces_jointes',
			dataType: 'json'
		}).done((retour) => {

			loading(false);

			if(retour.erreur === true){
				toastr.error(retour.message);
				return;
			}

			this.$set(this,'email_recus',retour.modele);
		});
	},

@endpush
