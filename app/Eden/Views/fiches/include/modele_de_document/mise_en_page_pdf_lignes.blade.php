<div class="row" style="display:none">

	<!-- Centraliser variables disponibles -->
	@php
	/*

	@foreach($variables_disponibles as $type => $champs)

		<h6 class="col-md-12">{{ $type }}</h6>

		@foreach($champs as $nom_sql => $nom)
			<div class="col-sm-6" style="cursor: pointer;" @click="choix_champ_libre_pour_colonne('{{$nom_sql}}', '{{addslashes($nom)}}', colonne);">{{$nom}} <i style="font-size: smaller">({{$nom_sql}})</i></div>
		@endforeach

	@endforeach
	*/
	@endphp


</div>


<form id="formulaire_fiche" class="css_form">

	<div v-for="(ligne, index) in {{$lignes}}" style="border:solid 1px #e9e9e9;margin-top:5px;padding:0px;">

		<div class="col-md-12" style="background:#e9e9e9;padding:3px;margin-bottom:5px;">
			<span @click="{{$lignes}}.splice(index+1, 0, [])" class="css_ajouter_element ml-auto pull-right" data-toggle="tooltip" data-placement="top" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.ajouter_ligne') }}">
				<i class="css_action_icon fa fa-fw fa-plus-square"></i>
			</span>

			@if($lignes == 'pdf_body')

				<span @click="{{$lignes}}.splice(index+1, 0, {type:'div',contenu:''})" class="css_ajouter_element ml-auto pull-right" data-toggle="tooltip" data-placement="top" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.ajouter_paragraphe') }}">
					<i class="css_action_icon fa fa-fw fa-paragraph"></i>
				</span>

				@if(($management_element->modele->type_de_document == 0 || in_array($management_element->modele->type_element_autres,\App\Eden\Variables::$documents_gescom)))
					<span @click="{{$lignes}}.splice(index+1, 0, {type:'articles', colonnes:[]})" class="css_ajouter_element ml-auto pull-right" data-toggle="tooltip" data-placement="top" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.ajouter_articles') }}">
						<i class="css_action_icon fa fa-fw fa-table"></i>
					</span>
				@endif

			@endif

			<span @click="{{$lignes}}.splice(index, 1)" class="css_ajouter_element ml-auto pull-right" data-toggle="tooltip" data-placement="top" title="{{ traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.supprimer_ligne') }}">
				<i class="css_action_icon fa fa-fw fa-trash"></i>
			</span>
		</div>

		<div v-if="ligne.type && ligne.type == 'articles'">
			<table width="100%">
				<thead style="background:#e9e9e9">
					<tr>
						<th v-for="(ligne, index_colonne) in ligne.colonnes">
							<label>
								<input type="checkbox" v-model="{{$lignes}}[index].colonnes[index_colonne].afficher_remise">
								@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.afficher_que_si_remise')
							</label>

							<a href="javascript:;" @click="{{$lignes}}[index].colonnes.splice(index_colonne, 0, {label:'',contenu:''})" style="float:left;margin-right:5px;"><span class="fa fa-plus"></span></a>

							<a href="javascript:;" @click="{{$lignes}}[index].colonnes.splice(index_colonne,1)" style="float:right;margin-right:5px;"><span class="fa fa-trash"></span></a>

						</th>

						<th>
							<a href="javascript:;" @click="{{$lignes}}[index].colonnes.splice({{$lignes}}[index].colonnes.length, 0, {label:'',contenu:''})"><span class="fa fa-plus"></span></a>
						</th>
					</tr>
					<tr>
						<th v-for="(ligne, index_colonne) in ligne.colonnes">

							<input type="text" v-model="{{$lignes}}[index].colonnes[index_colonne].label" placeholder="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.label_ligne')}}" @blur="creer_classe_css(index, index_colonne)">

							<a href="javascript:;" v-text="{{$lignes}}[index].colonnes[index_colonne].classe" @click="inserer_classe_au_css({{$lignes}}[index].colonnes[index_colonne].classe)"></a>

						</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th v-for="(ligne, index_colonne) in ligne.colonnes">
							<input type="text" v-model="{{$lignes}}[index].colonnes[index_colonne].contenu" placeholder="{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.contenu_ligne')}}">
						</th>
					</tr>

			        @include('eden::fiches.include.modele_de_document.totaux')

				</tbody>
			</table>
		</div>

		<div v-else-if="ligne.type && ligne.type == 'div'">
			@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.type_saisie') :
			<select v-model="ligne['type_saisie']" style="width:auto">
				<option value="">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.wysiwyg')}}</option>
				<option value="textarea">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.textarea')}}</option>
			</select>
			<textarea v-if="ligne['type_saisie'] == 'textarea'" v-model="ligne.texte"></textarea>
			<textarea-wysiwyg-vue v-else :modele="ligne" nom_sql="texte" ></textarea-wysiwyg-vue>
		</div>

		<div v-else class="col-md-12" style="min-height: 20px;">
			<div class="row">

				<div v-for="(cellule, index_cell) in ligne" :class="'col-md-'+cellule['col']">
					<div class="row">
						<div class="col-md-12">

							<a href="javascript:;" @click="{{$lignes}}[index].splice(index_cell,1)"><span class="fa fa-trash"></span></a>

							<select v-model="cellule['type']" style="width:auto">
								<option value="html">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.html')}}</option>
								<option value="vue">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.vue')}}</option>
                                <option value="totaux_sans_detail">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.totaux_sans_detail')}}</option>
                                <option value="totaux">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.totaux_avec_detail')}}</option>
                                <option value="recap_tva">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.recap_tva')}}</option>
                                <option value="recap_option">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.recap_option')}}</option>
							</select>

							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.taille') :
							<select v-model="cellule['col']" style="width:50px;">
								@for($i = 1; $i <= 12; $i++)
									<option value="{{$i}}">{{$i}}</option>
								@endfor
							</select>

							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.type_saisie') :

							<select v-model="cellule['type_saisie']" v-if="cellule.type == 'html'" style="width:auto">
								<option value="">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.wysiwyg')}}</option>
								<option value="textarea">{{traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.textarea')}}</option>
							</select>

							@traduction('module_sur_fiche.fiche.modele_de_document.mise_en_page_pdf_lignes.classe') : <a href="javascript:;" @click="inserer_classe_au_css('{{$type_bloc}}_bloc_'+index+'_'+index_cell)" v-html="'{{$type_bloc}}_bloc_'+index+'_'+index_cell"></a>

						</div>

						<div class="col-md-12" v-if="cellule['type'] == 'html'">
							<textarea v-if="cellule['type_saisie'] == 'textarea'" v-model="cellule.contenu"></textarea>
							<textarea-wysiwyg-vue v-else :modele="cellule" nom_sql="contenu" ></textarea-wysiwyg-vue>
						</div>

                        @php
                            $nom_std = collect(rglob(app_path().'/Eden/Views/pdf/*.blade.php'))->map(function ($pdf) {
                                return str_replace([app_path().'/Eden/Views/', '.blade.php'], '', $pdf);
                            });

                            $noms_spe = collect(rglob(resource_path().'/views/vendor/eden/pdf/*.blade.php'))->map(function ($pdf) {
                                return str_replace([resource_path().'/views/vendor/eden/', '.blade.php'], '', $pdf);
                            });
                        @endphp

                        <div class="col-md-12" v-if="cellule['type'] == 'vue'">
                            <select v-model="cellule['vue']">
                                <optgroup label="Standard">
                                    @foreach($nom_std as $pdf)
                                        @if(!$noms_spe->contains($pdf))
                                            <option value="{{ $pdf }}">{{ $pdf }}</option>
                                        @endif
                                    @endforeach
                                </optgroup>

                                <optgroup label="Spécifique">
                                    @foreach($noms_spe as $pdf)
                                        <option value="{{ $pdf }}">{{ $pdf }}</option>
                                    @endforeach
                                </optgroup>

                            </select>
                        </div>

                        <div class="col-md-12" v-if="cellule['type'] == 'totaux_sans_detail'">
                            <table width="100%">

                                @php
                                    $noColonnes = 0;
                                @endphp

                                @include('eden::fiches.include.modele_de_document.totaux_sans_detail',['cellule' => true])

                            </table>
                        </div>

						<div class="col-md-12" v-if="cellule['type'] == 'totaux'">
							<table width="100%">

								@php
									$noColonnes = 0;
								@endphp

			        			@include('eden::fiches.include.modele_de_document.totaux',['cellule' => true])

							</table>
						</div>

                        <div class="col-md-12" v-if="cellule['type'] == 'recap_tva'">
                            <table width="100%" class="recap_tva">

                                @php
                                    $noColonnes = 0;
                                @endphp

                                @include('eden::fiches.include.modele_de_document.recap_tva')

                            </table>
                        </div>

						<div class="col-md-12" v-if="cellule['type'] == 'recap_option'">
							<table width="100%" class="recap_option" style="border: 2px solid #000000; text-align: center;">

								@php
									$noColonnes = 0;
								@endphp

								@include('eden::fiches.include.modele_de_document.recap_option')

							</table>
						</div>

					</div>
				</div>
			</div>

			<a href="javascript:;" @click="{{$lignes}}[index].push({col:4, type:'html'})" style="position:absolute;top:0px;right:0px;"><em class="fa fa-plus"></em></a>

		</div>

	</div>


	<div class="d-flex flex-row justify-content-end">
		<button type="button" @click="enregistrer_{{$lignes}}_document()" class="btn btn-primary">{{traduction('interface.modales.enregistrer')}}</button>
	</div>
</form>



@push('donnees_pour_vuejs_methods')

	enregistrer_{{$lignes}}_document: function() {

		// On affiche le loader
		loading();

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('base_eden.element.enregistrer', ['modele_de_document', $management_element->modele->id]) }}",
			dataType: "json",
			method: 'POST',
			data: { {{str_replace('pdf_', '', $lignes)}}:JSON.stringify( vue_instance.{{$lignes}} )}

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
