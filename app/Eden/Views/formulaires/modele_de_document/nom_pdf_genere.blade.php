<div class="row">
    <div class="col-sm-2">
        {!! management('modele_de_document')->champ('nom_pdf_genere')->nom_vue() !!}
    </div>
    <div class="col-sm-10">
        <input-parametrage :type_utilisateur="$root.moi.type_utilisateur"
            at_custom="#" name="nom_pdf_genere"
            :vmodel="modele_de_document"
            :donnees="champs_libres_type_element"></input-parametrage>
    </div>
</div>

@push('donnees_pour_vuejs_data')
	champs_libres_type_element: [],
	champs_piece_jointe : [],
@endpush

@push('donnees_pour_vuejs_mounted')

	this.chargement_champs_libres_type_element();

@endpush

@push('donnees_pour_vuejs_methods')

	chargement_champs_libres_type_element : function(){

		if(this.modele_de_document.type_element_autres == null || this.modele_de_document.type_element_autres == '')
			return;

		$.ajax({

			url: "{{ URL::to('/eden/champs/valeurs') }}/" + this.modele_de_document.type_element_autres,
			dataType: "json"
		}).done((donnees) => {
			this.champs_piece_jointe = [{
				type_element : this.modele_de_document.type_element_autres,
				index_traduction : 'tables_libres.'+this.modele_de_document.type_element_autres+'.nom_table',
				champs_libres : donnees.filter((champ) => {
					return champ.type == 7;
				})
			}];

			this.champs_libres_type_element = donnees.map((champ_libre) => {
				return {
					id: '#'+champ_libre.nom_sql+'#',
					name: this.$root.traduction(champ_libre.index_traduction+'.nom'),
				};
			});
		});
	},
@endpush

@push('donnees_pour_vuejs_watch')
    'modele_de_document.type_element_autres' : function(){
        this.chargement_champs_libres_type_element();
    },
@endpush