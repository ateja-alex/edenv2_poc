@extends('eden::templates.template')

@section('content')
	
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-4" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">{{ montant_lisible($montant_du) }} {{ maquette('devise_application_symbole') }}</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement.titre_module.montant_du')</span>
					</div>
				</div>
				<div class="col-md-4" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">{{ $nombre_documents }}</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement.titre_module.documents')</span>
					</div>
				</div>
				<div class="col-md-4" style="text-align: center; padding: 20px;">
					<div style="border: 1px solid #c3c3c3; background: #eee;">
						<span style="color: #818181; font-size: 50px;">{{ $relances_a_effectuer }}</span><br/>
						<span style="color: #818181; font-size: 30px;">@traduction('interface.recouvrement.titre_module.relances_a_effectuer')</span>
					</div>
				</div>
				
				<div class="col-md-12" v-for="groupe_recouvrement in groupes_recouvrement">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.recouvrement.titre_liste') - @{{ groupe_recouvrement.nom }}
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th>#</th>
											<th>@traduction('interface.recouvrement.colonnes.document')</th>
											<th>@traduction('interface.recouvrement.colonnes.solde')</th>
											<th>@traduction('interface.recouvrement.colonnes.options')</th>
											<th>@{{ groupe_recouvrement.relance_1 }}</th>
											<th>@{{ groupe_recouvrement.relance_2 }}</th>
											<th>@{{ groupe_recouvrement.relance_3 }}</th>
											<th>@{{ groupe_recouvrement.relance_4 }}</th>

											<th>@traduction('interface.recouvrement.colonnes.commentaires')</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="document in groupe_recouvrement.documents">
											<td>@{{ document.id }}</td>
											<td>
												<a :href="'{{ URL::to('/eden/document/facture_vente') }}/'+document.id">@{{ document.reference_document }}</a><br/>
												<span v-html="document.client"></span><br/>
												(@{{ document.modalite_paiement_id }})
											</td>
											<td>@{{ document.solde_document_ttc }} {{ maquette('devise_application_symbole') }}</td>
											<td><span class="fa fa-envelope"></span></td>
											<td><span class="badge" @click="enregistre_relance(document, 'relance_1')" :class="{'badge-success': document.relance_1_ok == 1, 'badge-default': document.relance_1_ok != 1 && document.relance_1 > '{{date('Y-m-d')}}', 'badge-warning': document.relance_1_ok != 1 && document.relance_1 <= '{{date('Y-m-d')}}'}">@{{ document.relance_1 | date }}</span></td>
											<td><span class="badge" @click="enregistre_relance(document, 'relance_2')" :class="{'badge-success': document.relance_2_ok == 1, 'badge-default': document.relance_2_ok != 1 && document.relance_2 > '{{date('Y-m-d')}}', 'badge-warning': document.relance_2_ok != 1 && document.relance_2 <= '{{date('Y-m-d')}}'}">@{{ document.relance_2 | date }}</span></td>
											<td><span class="badge" @click="enregistre_relance(document, 'relance_3')" :class="{'badge-success': document.relance_3_ok == 1, 'badge-default': document.relance_3_ok != 1 && document.relance_3 > '{{date('Y-m-d')}}', 'badge-warning': document.relance_3_ok != 1 && document.relance_3 <= '{{date('Y-m-d')}}'}">@{{ document.relance_3 | date }}</span></td>
											<td><span class="badge" @click="enregistre_relance(document, 'relance_4')" :class="{'badge-success': document.relance_4_ok == 1, 'badge-default': document.relance_4_ok != 1 && document.relance_4 > '{{date('Y-m-d')}}', 'badge-warning': document.relance_4_ok != 1 && document.relance_4 <= '{{date('Y-m-d')}}'}">@{{ document.relance_4 | date }}</span></td>
											<td style="padding: 0px;"><textarea style="border:0px; width: 100%; height: 100%;" v-model="document.commentaires_recouvrement" placeholder="Commentaires" @change="enregistre_commentaire(document)"></textarea></td>
											
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				
				
			</div>
		</div>
	</div>
	
	
@endsection

@push('donnees_pour_vuejs_data')
	groupes_recouvrement: {!! $groupes_recouvrement !!},
@endpush

@push('donnees_pour_vuejs_methods')

	enregistre_relance: function(document, relance) {
		
		document[relance] = '{{ date('Y-m-d') }}';
		document[relance+'_ok'] = 1;
		
		var modifications = {};
		
		modifications[relance] = '{{ date('Y-m-d') }}';
		modifications[relance+'_ok'] = 1;
		
		$.post({
			
			url: "{{ URL::to("eden/element/facture_vente/") }}/"+document.id+"/enregistrer",
			dataType: "json",
			data: modifications
		});
	},
	
	enregistre_commentaire: function(document) {
		
		$.post({
			
			url: "{{ URL::to("eden/element/facture_vente/") }}/"+document.id+"/enregistrer",
			dataType: "json",
			data: {
				
				commentaires_recouvrement: document.commentaires_recouvrement
			}
		}).done(function() {
			
			
		});
	},
@endpush