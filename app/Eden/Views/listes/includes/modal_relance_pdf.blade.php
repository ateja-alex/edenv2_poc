@push('vue_liste_actions')

	<?php
		// On va chercher les différents modèles de PDF
		$modeles_pdf_std = array();
		$modeles_pdf_spe = array();

		if (is_dir(app_path().'/Eden/Views/recouvrement'))
			$modeles_pdf_std = scandir(app_path().'/Eden/Views/recouvrement');
		
		if (is_dir(resource_path().'/views/vendor/eden/recouvrement'))
			$modeles_pdf_spe = scandir(resource_path().'/views/vendor/eden/recouvrement');

		// On retire les . et .. ainsi que template + on retire le .blade.php
		if (!empty($modeles_pdf_std)) {
			
			foreach ($modeles_pdf_std as $id => &$modele) {
			
				if ($modele == '.' || $modele == '..' || $modele == 'template.blade.php') 
					unset($modeles_pdf_std[$id]);

				else
					$modele = substr($modele, 0, -10);
			}
		}
			
		if (!empty($modeles_pdf_spe)) {
			foreach ($modeles_pdf_spe as $id => &$modele) {
			
				if ($modele == '.' || $modele == '..' || $modele == 'template.blade.php') 
					unset($modeles_pdf_spe[$id]);

				else
					$modele = substr($modele, 0, -10);
			}	
		}
	?>

    <div class="modal fade" id="modal_relance_pdf_{{$id_liste}}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@traduction('interface.listes.relance_pdf')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form class="modal-body css_form" id="formulaire_relance_pdf_en_masse_{{ $id_liste }}" style="color:#363636;">
                	@traduction('interface.listes.quel_modele_de_pdf')
                	<select name="modele_pdf">
                		<optgroup label="Standard">
                			@foreach($modeles_pdf_std as $modele_pdf)
	                			<option value="{{ $modele_pdf}}">{{ $modele_pdf}}</option>
	                		@endforeach
                		</optgroup>
                		
                		<optgroup label="Spécifique">
                			@foreach($modeles_pdf_spe as $modele_pdf)
	                			<option value="{{ $modele_pdf}}">{{ $modele_pdf}}</option>
	                		@endforeach
                		</optgroup>
                	</select>
                	<br><br>
                	@traduction('interface.listes.quelles_factures_relance_pdf')<br/><br/>
                    <span class="btn btn-xs btn-danger js_action_lignes_selectionnees" :class="this.liste.lignes_selectionnees.length == 0 ? 'disabled ' : ''" @click="eden_relance_pdf_elements_selectionnes({{$id_liste}})">@traduction('interface.listes.elements_selectionnes') (<span class="js_nombre_lignes_selectionnees" v-html="this.liste.lignes_selectionnees.length"></span>)</span><br/><br/>
                    <span class="btn btn-xs btn-danger" @click="eden_relance_pdf_tous_les_elements({{$id_liste}})">@traduction('interface.listes.tous_les_elements')</span><br/>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.listes.fermer')</button>
                </div>
            </div>
        </div>
    </div>
@endpush