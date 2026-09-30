@extends('eden::templates.template')

@section('title') Configuration parametres indicateurs @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Parametres indicateurs
								<span class="css_ajouter_element css__lien" @click="enregistrer_indicateurs"><i class="fa fa-fw fa-plus-square"></i> Enregistrer</span>
							</h4>
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th scope="col">Indicateur</th>
											<th scope="col">Valeur</th>
											<th scope="col">Recalculer</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>Clients : CA</td>
											<td>
												<select v-model="parametres_indicateurs.client_ca">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_ca')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Clients : CA 12 mois glissants</td>
											<td>
												<select v-model="parametres_indicateurs.client_ca_12_mois_glissants">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_ca_12_mois_glissants')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Clients : CA depuis janvier</td>
											<td>
												<select v-model="parametres_indicateurs.client_ca_depuis_janvier">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_ca_depuis_janvier')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Clients : date dernière facture</td>
											<td>
												<select v-model="parametres_indicateurs.client_date_de_derniere_facture">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_date_de_derniere_facture')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Clients : date dernier échange</td>
											<td>
												<select v-model="parametres_indicateurs.client_date_de_dernier_echange">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_date_de_dernier_echange')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Clients : encours</td>
											<td>
												<select v-model="parametres_indicateurs.client_encours">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('client_encours')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Projet : délai création 1er devis</td>
											<td>
												<select v-model="parametres_indicateurs.temps_transfo_premier_devis">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('temps_transfo_premier_devis')">Recalculer</span></td>
										</tr>
										<tr>
											<td>Projet : délai réponse client (acceptation du 1er devis)</td>
											<td>
												<select v-model="parametres_indicateurs.delai_reponse_client">
													<option value="true">Activé</option>
													<option value="false">Désactivé</option>
												</select>
											</td>
											<td><span style="cursor: pointer;text-decoration: underline;" v-on:click="recalculer('delai_reponse_client')">Recalculer</span></td>
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

	parametres_indicateurs: {!! collect($parametres_indicateurs) !!},
@endpush

<script>
@push('donnees_pour_vuejs_methods')

	enregistrer_indicateurs: function() {
		
		loading(true);
		
		var parametres_indicateurs = this.parametres_indicateurs;
		
		$.post({

			url: '{{ route('parametrage.indicateur.enregistrer') }}',
			data: {parametres_indicateurs},
			success: function(data) {

				loading(false);

			}
		});
	},

	recalculer: function(indicateur) {
		
		// console.log(indicateur);
		loading(true);

		$.post({

			url: '{{ route('parametrage.indicateur.recalcule') }}',
			data: {indicateur},
			success: function(data) {

				loading(false);

			}
		});
	},
@endpush
</script>


