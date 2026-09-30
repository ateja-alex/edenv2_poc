@extends('eden::composants_vue.js.liste_libre')

@push('donnees_pour_vuejs_methods')

	charger_pieces_jointes : function(id_email_recu){

		loading(true);

		$.post({
			url : 'eden/email_recus/'+id_email_recu+'/charger_pieces_jointes',
			dataType: 'json'
		}).done((retour) => {

			loading(false);

			if(retour.erreur === true){
				toastr.error(retour.message);
				return;
			}

			this.actualisation_filtres();
		});
	},

@endpush