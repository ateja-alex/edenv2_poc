<form id="formulaire_fiche_annexes" class="css_form">

	<div v-for="(ligne, index) in {{$lignes}}" style="border:solid 1px #e9e9e9;margin-top:5px;padding:0px;">

		<div class="col-md-12" style="min-height: 20px;">
			<div class="row">

				<div class="col-md-12">
					@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_annexes.classe') : <a href="javascript:;" @click="inserer_classe_au_css('{{$type_bloc}}_bloc_'+index)" v-html="'{{$type_bloc}}_bloc_'+index"></a>

					<span @click="pdf_annexes.splice(index,1)" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_annexes.supprimer')}}" style="float:right;">
						<i class="css_action_icon fa fa-fw fa-trash"></i>
					</span>

				</div>

				<div class="col-md-12">
					<textarea-wysiwyg-vue :modele="pdf_annexes" :nom_sql="index" ></textarea-wysiwyg-vue>
				</div>

			</div>
		</div>

	</div>


	<div class="d-flex flex-row justify-content-end">
		<button type="button" @click="enregistrer_les_annexes_document()" class="btn btn-primary">{{traduction('interface.modales.enregistrer')}}</button>
	</div>
</form>



@push('donnees_pour_vuejs_methods')

	enregistrer_les_annexes_document: function() {

		// On affiche le loader
		loading();
		
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.element.enregistrer', ['modele_de_document', $management_element->modele->id]) }}",
			dataType: "json",
			method: 'POST',
			data: { annexes: this.pdf_annexes.length == 0 ? null : this.pdf_annexes }

		}).done(async function(donnees) {
			
			// On retire le loader
			loading(false);
			
			if(donnees.retour !== true) {
			
				await erreur(donnees.retour);
				return;
			}

			toastr.success('{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf.modifications_enregistrees')}}')
		});
	},



@endpush