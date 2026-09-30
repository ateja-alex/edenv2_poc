@extends('eden::templates.template')

@section('title') ADV @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('nom' => @traduction('interface.adv.titre'))
				)])
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								@traduction('interface.adv.titre')
							</h4>
						</div>
						<div class="card-body">
							<div class="row">
								<div class="col-md-3">
									<div class="css_conteneur_gauche_ticket">
										<h4 style="background: #b7b7b7;display: block;padding: 8px;text-align: center;">@traduction('interface.adv.titre_categorie_filtre.clients')</h4>
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.devis')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('devis_vente_lignes',{{$listes['devis_vente_lignes']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('devis_vente_lignes'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.a_transformer')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['devis_vente_lignes']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div> 
										
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.commandes')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('commande_vente_lignes',{{$listes['commande_vente_lignes']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('commande_vente_lignes'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.recapitulatif')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['commande_vente_lignes']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div> 
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('commande_vente_lignes_a_commander',{{$listes['commande_vente_lignes_a_commander']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('commande_vente_lignes_a_commander'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.a_commander')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['commande_vente_lignes_a_commander']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div> 
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('commande_vente_lignes_a_recevoir',{{$listes['commande_vente_lignes_a_recevoir']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('commande_vente_lignes_a_recevoir'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.attente_livraison')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['commande_vente_lignes_a_recevoir']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div> 
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('commande_vente_lignes_a_livrer',{{$listes['commande_vente_lignes_a_livrer']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('commande_vente_lignes_a_livrer'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.pret')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['commande_vente_lignes_a_livrer']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div> 
										
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.bon_preparation')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('bon_preparation_vente_lignes_a_expedier',{{$listes['bon_preparation_vente_lignes_a_expedier']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('bon_preparation_vente_lignes_a_expedier'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.a_produire')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['bon_preparation_vente_lignes_a_expedier']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div>
										
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.bon_livraison')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('bl_vente_lignes_a_facturer',{{$listes['bl_vente_lignes_a_facturer']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('bl_vente_lignes_a_facturer'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste" style="vertical-align: middle;">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.a_facturer')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['bl_vente_lignes_a_facturer']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div>
										
										<h4 style="background: #b7b7b7;display: block;padding: 8px;text-align: center;margin-top: 8%;">@traduction('interface.adv.titre_categorie_filtre.fournisseurs')</h4>
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.commandes')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('commande_achat_lignes_a_recevoir',{{$listes['commande_achat_lignes_a_recevoir']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('commande_achat_lignes_a_recevoir'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span> 
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.a_recevoir')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['commande_achat_lignes_a_recevoir']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div>
										<h5>@traduction('interface.adv.titre_sous_categorie_filtre.bon_livraison')</h5>
										<div class="css_ligne_bloc_gauche_ticket js_filtre_ticket_statut js_filtre">
											<span @click="change_liste_active_adv('bl_achat_lignes_recues',{{$listes['bl_achat_lignes_recues']['id_liste']}})" :style="{fontWeight: style_liste_active_adv('bl_achat_lignes_recues'), 'display': 'flex', 'align-items': 'center', 'vertical-align' : 'middle'}">
												<span class="js_filtre_sur_liste">
													<img src="{{asset('eden/images/pictos/enregistre.png')}}" class="css_pictos_statut_ticket" style="vertical-align: middle;">
												</span>
												<span class="css_texte_filtre_ticket_gauche js_filtre_sur_liste">
													@traduction('interface.adv.titre_filtre.recues')
													<span class="badge badge-default">{{ '{{ affiche_nombre_elements_adv('.$listes['bl_achat_lignes_recues']['id_liste'].') }'.'}' }}</span>
												</span>
											</span>
										</div>
									</div>
								</div>
								<div class="col-md-9">
									@foreach($listes as $nom_liste => $infos_liste)
										<span v-show="liste_active_adv == '{{$nom_liste}}'">
											@include('eden::listes.includes.liste', [

												'type_element' => $infos_liste['type_element'],
												'id_liste' => $infos_liste['id_liste'],
												'options_liste' => $infos_liste['options_liste'],
											])
										</span>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_methods')
	
	change_liste_active_adv: function(nom_liste,id_liste) {
		
		//console.log(id_liste);
		
		this.liste_active_adv = nom_liste;
		this.$refs['liste_libre_'+id_liste].actualisation_filtres();
	}, 
	style_liste_active_adv: function(nom_liste) {
		
		if(this.liste_active_adv == nom_liste)
			return 'bold';
		
		return '300';
	}, 
	affiche_nombre_elements_adv: function(id_liste) {
		
		if(!this.$refs['liste_libre_'+id_liste])
			return '';
		
		return this.$refs['liste_libre_'+id_liste].liste.nombre_elements.split(' ')[0];
	}, 
@endpush

@push('donnees_pour_vuejs_data')
	
	liste_active_adv: 'devis_vente_lignes',
@endpush


