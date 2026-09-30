@extends('eden::templates.template')

@section('title') Liste libre @stop

@section('styles')

<style>

input.js_filtre_sur_liste {

	border-color:#DCDCDC;
}
</style>

@endsection

@section('options_fil_ariane')

	@if(editeur())
		<span class="css_ajouter_element ml-auto css__lien" data-toggle="tooltip" data-placement="left" title="Rafraîchir ce composant liste">
			<a @click="regenere_fichier_composant"><i class="css_action_icon fa fa-refresh"></i></a>
		</span>
	@endif

	@if(!empty($liste_libre->id_rapport))
		@if($rapport->inactif != 1)
			<span @click="supprimer_liste" class="css_ajouter_element ml-auto css__lien" data-toggle="tooltip" data-placement="left" title="{{$liste_standard === false ? 'Supprimer cette liste' : 'Désactiver cette liste'}}">
				<i class="css_action_icon fas fa-trash"></i>
			</span>
		@else
			<span @click="activer_liste" class="css_ajouter_element ml-auto css__lien" data-toggle="tooltip" data-placement="left" title="Réactiver cette liste">
				<i class="css_action_icon fas fa-trash-restore"></i>
			</span>
		@endif
	@endif
@endsection

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
				array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
				array('nom' => 'Listes')
			)])

			<div class="row" v-if="typeof rapport.kanban != 'undefined'">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<template v-if="rapport.liste_sur_fiche == 1">
								<h4>
									Liste sur fiche
								</h4>
							</template>
							<template v-else-if="rapport.export == 1">
								<h4>
									Export
								</h4>
							</template>
							<template v-else-if="rapport.intranet == 1">
								<h4>
									Liste intranet
								</h4>
							</template>
							<template v-else>
								<h4>
									Rapport
								</h4>
								<span data-placement="left" class="ml-auto">
									<span data-toggle="tooltip" title="" class="css_ajouter_element" data-original-title="Enregistrer" @click="enregistrer_parametres_rapport"><i class="css_action_icon far fa-save css_font_16" aria-hidden="true"></i></span>
								</span>
							</template>
						</div>
						<div class="card-body">
							<div class="table-responsive css_form">
								<div class="row">
									<div class="col-md-12">
										<traduction-table  categorie="10" :filtrage_index="rapport.index_traduction+'.'"></traduction-table>
									</div>
								</div>
								<template v-if="rapport.liste_sur_fiche != 1 && rapport.export != 1 && rapport.intranet != 1">

									<div class="row">
										<div class="col-md-3">Disponible extranet</div>
										<select class="col-md-9" v-model="rapport.extranet">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div class="row">
										<div class="col-md-3">Type</div>
										<select class="col-md-9" v-model="rapport.type" @change="changement_type_rapport()">
											<option value="liste_libre">Liste classique</option>
											<option value="requete_sql">Liste basée sur une requête sql</option>
											@if($champs_libres->whereIn('type', [1, 20, 42])->first()!= null)
												<option value="kanban">Kanban</option>
											@endif
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Colonne Kanban</div>
										<select v-model="rapport.kanban" class="col-md-9" @change="charger_parametres_rapport()">
											@foreach($champs_libres->whereIn('type', [1, 20, 42]) as $nom_sql => $champ)
												<option value="{{$champ->nom_sql}}">{{$champ->nom}}</option>
											@endforeach
										</select>
									</div>

									<div class="row" v-if="rapport.kanban && !rapport.exclusion_colonnes">
										<div class="col-md-3">Afficher toutes les colonnes</div>
                                        <label class="switch">
                                            <input name="toutes_les_colonnes" type="checkbox" v-model="rapport.toutes_les_colonnes" :true-value="1" :false-value="0" @click="rapport.toutes_les_colonnes = $root.toggle_0_1(rapport.toutes_les_colonnes);" >
                                            <span class="slider round"></span>
                                        </label>
                                        <select style="display: none;">
                                            <option value="0" selected>Non</option>
                                            <option value="1">Oui</option>
                                        </select>
									</div>

									<div class="row" v-if="rapport.kanban && !rapport.toutes_les_colonnes">
										<div class="col-md-3">Exclusion des colonnes ?</div>
                                        <label class="switch">
                                            <input name="exclusion_colonnes" type="checkbox" v-model="rapport.exclusion_colonnes" :true-value="1" :false-value="0" @click="rapport.exclusion_colonnes = $root.toggle_0_1(rapport.exclusion_colonnes);" >
                                            <span class="slider round"></span>
                                        </label>
                                        <select style="display: none;">
                                            <option value="0" selected>Non</option>
                                            <option value="1">Oui</option>
                                        </select>
									</div>

									<div class="row" v-if="rapport.kanban && !rapport.toutes_les_colonnes">
										<div class="col-md-3"></div>

										<form class="col-md-9 form_colonnes_kanban" style="padding:0px;">

											<champ-multiple v-if="champ_kanban && champ_kanban.type == 42" :modele="rapport" composant_enfant="champ-selection-element" 
												:composant_enfant_props="{
													ref: 'colonnes_kanban',
													type_element_origine : type_element,
													type_element: champ_kanban.type_element_ajax,
													nom_sql: 'colonnes_kanban',
													modele : rapport,
													name: 'colonnes_kanban',
													desactiver_creation_a_la_volee: true,
												}"></champ-multiple>

											<fielset v-else class=" js_colonnes_kanban ui-sortable" style="border-style: groove;display: block;padding:10px;">

												<label class="row" v-for="colonne in colonnes_kanban" style="font-size:18px;" :style="{ color: (in_array(colonne.valeur, rapport.kanban_colonnes_decode) ? 'black' : '#ccc') }">
													<div class="col-sm-0">
														<input  type="checkbox"
																:name="'colonne_'+colonne.valeur"
																:value="colonne.valeur"
																:checked="in_array(colonne.valeur, rapport.kanban_colonnes_decode)"
																onclick="if(this.checked) {$(this).parent().parent().css('color', 'black')} else {$(this).parent().parent().css('color', '#ccc')}">
													</div>
													<div class="col-sm-1" style="cursor:grab">
														<i aria-hidden="true" class="fas fa-align-justify" style="margin-right: 3%;"></i>
													</div>
													<div class="col-sm-10">
														@{{colonne.texte}}
													</div>
												</label>

											</fieldset>

										</form>

									</div>

									<div class="row" v-if="rapport.kanban && champ_kanban && champ_kanban.type == 42">
										<div class="col-md-3">Trier les colonnes par</div>
										<input type="hidden" name="kanban_tri_champ" v-model="rapport.kanban_tri_champ" />
										<select-champs-libres :champs_libres="champs_libres_pour_select.filter(c => c.champ_liaison == champ_kanban.nom_sql)"
											:nom_sql="rapport.kanban_tri_champ"
											:type_element_origine="champ_kanban.type_element_ajax"
											:type_element="champ_kanban.type_element_ajax"
											@changement_select_champs_libres="rapport.kanban_tri_champ = $event.nom_sql">
										</select-champs-libres>
									</div>

									<div class="row" v-if="rapport.kanban && champ_kanban && champ_kanban.type == 42 && rapport.kanban_tri_champ">
										<div class="col-md-3">Sens du tri des colonnes</div>
										<select v-model="rapport.kanban_tri_sens" class="col-md-9 form-control">
											<option value="ASC">Croissant</option>
											<option value="DESC">Décroissant</option>
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Activer calcul somme en entête de colonne</div>
										<select v-model="rapport.kanban_entete_calcul_somme" class="col-md-9">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div class="row" v-if="rapport.kanban && rapport.kanban_entete_calcul_somme == '1'">
										<div class="col-md-3">Colonne pour la somme</div>
										<select v-model="rapport.kanban_entete_calcul_champ" class="col-md-9">
											@foreach($champs_libres->whereIn('type', [2,3]) as $nom_sql => $champ)
												<option value="{{$champ->nom_sql}}">{{$champ->nom}}</option>
											@endforeach
										</select>
									</div>

									<div class="row" v-if="rapport.kanban && rapport.kanban_entete_calcul_somme == '1'">
										<div class="col-md-3">Unité pour la somme</div>
										<input type="text" class="col-md-9" v-model="rapport.kanban_entete_calcul_unite" />
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Activer affichage nombre d'éléments en entête de colonne</div>
										<select v-model="rapport.kanban_entete_calcul_nombre" class="col-md-9">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Champ image 1</div>
										<select v-model="rapport.kanban_afficher_utilisateur_1" class="col-md-9 form-control">
                                            <option value="">Sans valeur</option>
											@foreach(champs_libres_liste_utilisateurs($type_element) as $champ_utilisateur)
												<option value="{{ $champ_utilisateur->nom_sql }}">{{ $champ_utilisateur->nom }}</option>
											@endforeach
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Champ image 2</div>
										<select v-model="rapport.kanban_afficher_utilisateur_2" class="col-md-9 form-control">
                                            <option value="">Sans valeur</option>
											@foreach(champs_libres_liste_utilisateurs($type_element) as $champ_utilisateur)
												<option value="{{ $champ_utilisateur->nom_sql }}">{{ $champ_utilisateur->nom }}</option>
											@endforeach
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Affichage kanban vertical</div>
										<select v-model="liste_libre.affichage_kanban_vertical" class="col-md-9">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Désactiver le drag & drop ( Kanban )</div>
										<select v-model="liste_libre.desactiver_drag_drop_kanban" class="col-md-9">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div class="row" v-if="rapport.kanban">
										<div class="col-md-3">Cacher les colonnes vides de l'affichage kanban</div>
										<select v-model="liste_libre.desactiver_kanban_sans_valeur" class="col-md-9">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>

									<div v-if="rapport.type == 'requete_sql'">

										<div class="row" v-if="rapport.cle_etrangere != '' && rapport.cle_etrangere != null">
											<div class="col-md-3">Clé étrangère</div>
											<input class="col-md-8" type="text" v-model="rapport.cle_etrangere">
										</div>

										<div class="row">
											<div class="col-md-3">Requête SQL</div>
											<textarea class="col-md-8" v-model="rapport.requete_sql"></textarea>
											<i class="fas fa-question-circle css_pointer" title="Aide requête" onclick="$('#tooltip_requete').toggle();"  style="padding: 10px; background:red;color: white;height:30px;text-align: center;"></i>
										</div>
										<div class="row" id="tooltip_requete" style="display:none">
											<div class="col-md-3"></div>
											<div class="col-md-9 alert alert-danger">
												<b>Quelques règles concernant la requête:</b>
												<br>
												1 - Il faut que le type élement de la liste apparaisse dans la requête
												<br>
												2 - La colonne "Alias dans la requête" sert à remplacer l'alias par la gestion du filtre EDEN
												(Exemple : #parametres_eden_1# pourra être remplacé par nom_sql BETWEEN date_debut AND date_fin)
												<br>
												3 - La colonne champ sert à définir le nom de la colonne qui va être filtrée. Attention à bien le préfixer par le type élément
												(Exemple : client.cree_le)
												<br>
												4 - Des alias généraux servent à gérer certaines fonctionnalités d'EDEN :
												<br>
												#eden_recherche# : afin de gérer la recherche dans les listes
												<br>
												#eden_order_by# : afin de gérer l'order by
												<br>
												#eden_profils_et_inactif# : afin de gérer la colonne inactif et la gestion des droits au niveau des profils eden ou extranet
												<br>
												#eden_join# : afin de gérer les joins éventuels liés aux filtres eden
												<br>
												#eden_filtres# : afin de gérer les filtres eden
												<br>
												#eden_filtres_pour_fiche# : afin de gérer les filtres pour fiche si c'est un rapport pour fiche, le champ filtré sera celui indiqué
												dans "Clé étrangère". Attention à bien le préfixer par le type élément
												<br>
												Exemple de requête : SELECT client.id ,client.nom as raison_sociale,5 as chiffre_aleatoire FROM client #eden_join# WHERE #parametres_eden_1# AND COALESCE(client.inactif,0) = 0 AND #eden_recherche# AND #eden_filtres# #eden_order_by#
											</div>
										</div>
										<div class="row">
											<div class="col-md-3">Paramétres de la requête</div>
											<table class="col-md-9 table table-bordered table-hover">
												<thead>
													<th>Nom du filtre</th>
													<th>Champ</th>
													<th>Alias dans la requête</th>
													<th>Type de filtre</th>
													<th><i class="fas fa-plus" @click="rapport.parametres_requete.push({
														nom: '',
														champ: '',
														alias_requete: '#parametres_eden_' + (rapport.parametres_requete.length + 1) + '#',
														type_filtre: 'filtre-date'
													})"></i></th>
												</thead>
												<tbody>
													<tr v-for="(parametre,index_parametre) in rapport.parametres_requete">
														<td>
															<input type="text" v-model="parametre.nom" />
														</td>
														<td>
															<input type="text" v-model="parametre.champ" />
														</td>
														<td>
															<input type="text" v-model="parametre.alias_requete" />
														</td>
														<td>
															<select v-model="parametre.type_filtre">
																<option value="filtre-date">Filtre date</option>
																<option value="filtre-montant">Filtre montant</option>
																<option value="filtre-texte">Filtre texte</option>
															</select>

															<select v-if="['filtre-recherche-element','filtre-liste-formatee','filtre-liste-libre'].includes(parametre.type_filtre)">

															</select>
														</td>
														<td><i class="fas fa-trash" @click="rapport.parametres_requete.splice(index_parametre,1)"></i></td>
													</tr>
												</tbody>
											</table>
										</div>
									</div>
								</template>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row" v-if="!rapport.kanban || liste_libre.affichage_kanban_vertical == 1">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header d-flex align-items-center">
							<h4>
								Colonnes
							</h4>
							<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouvelle colonne" @click="ajouter"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
							@if(empty($id_rapport))
								<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=5&recherche=liste.{{$type_element}}" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Affichage traduction global">
									<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
								</a>
							@else
								<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=5&recherche=rapport.{{$id_rapport}}" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Affichage traduction global">
									<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
								</a>
							@endif
						</div>
						<div class="card-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover" width="100%" cellspacing="0">
									<thead>
										<tr>
											<th scope="col">#</th>
											<th scope="col">Nom</th>
											<th scope="col">Valeur</th>
											<th scope="col">Responsive</th>
											<th scope="col">Tri par défaut</th>
										</tr>
									</thead>
									<tbody class="sortable" type_element='colonne'>
										<tr v-for="colonne in colonnes" :key='colonne.id' :data-ordre="colonne.ordre" :data-id_colonne_sortable="colonne.id">
											<td><span class="css_ajouter_element css__lien"  @click="modifier" :id_colonne="colonne.id">@{{ colonne.id }}</span></td>
											<td>
												<template v-if="colonne.index_traduction != '' && colonne.index_traduction != null">
													@{{ traduction(colonne.index_traduction,'nom') }}
												</template>
												<template v-else>
													@{{ colonne.nom }}
												</template>
                                            </td>
											<td>
												<span v-show="colonne.type == 'standard'"><span class="badge badge-default">Standard</span> @{{ colonne.valeur }}</span>
												<span v-show="colonne.type == 'methode'"><span class="badge badge-warning">Méthode</span> @{{ colonne.methode }}</span>
												<span v-show="colonne.type == 'champ'"><span class="badge badge-danger">Champ</span> @{{ colonne.champ }}</span>
												<span v-show="colonne.type == 'concatenation'"><span class="badge badge-info">Concaténation</span> @{{ colonne.valeur }}</span>
												<span v-show="colonne.type == 'calcul'"><span class="badge badge-primary">Calcul</span> @{{ colonne.type_calcul }} : @{{ colonne.source_calcul }} @{{ colonne.champ_calcul ? '=> '+colonne.champ_calcul : '' }}</span>
											</td>
											<td>
												<div class="badge badge-success" @click="changement_etat('responsive', colonne.id, 0)" v-show="colonne.responsive == 1">Oui</div>
												<div class="badge badge-danger"  @click="changement_etat('responsive', colonne.id, 1)" v-show="colonne.responsive == 0 || colonne.responsive == null">Non</div>
											</td>
											<td>
												<template v-if="colonne.tri_par_defaut">
													<div class="badge badge-success" @click="changement_etat('sens_tri_par_defaut', colonne.id, 1)" v-show="colonne.sens_tri_par_defaut == 0 || colonne.sens_tri_par_defaut == null">ASC</div>
													<div class="badge badge-danger"  @click="changement_etat('sens_tri_par_defaut', colonne.id, 0)" v-show="colonne.sens_tri_par_defaut == 1">DESC</div>
												</template>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			@if($liste_libre->export != 1)
				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									Filtres
								</h4>
								<span data-placement="left" class="ml-auto">
									<input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.listes.recherche')" style="padding-left: 5px;width:auto;" type="text" name="recherche" v-model="recherche_filtres" />
									<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Nouveau filtre" @click="ajouter_filtre_liste_libre"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
								</span>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover" width="100%" cellspacing="0">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Nom</th>
												<th scope="col">Emplacement</th>
											</tr>
										</thead>
										<tbody class="sortable" type_element='filtre'>
											<tr v-for="filtre in filtres" v-if="correspond_recherche(filtre.nom_sql ,recherche_filtres)" :key='filtre.id' :data-ordre="filtre.ordre" :data-id_colonne_sortable="filtre.id">
												<td><span class="css_ajouter_element css__lien"  @click="modifier_filtre_liste_libre" :id_filtre="filtre.id">@{{ filtre.id }}</span></td>
												<td v-html="traduction('champs_libres.'+(filtre.type_element !== '' && filtre.type_element !== null ? filtre.type_element : '{{$type_element}}')+'.'+filtre.nom_sql,'nom')"></td>
												<td>
													@{{ filtre.emplacement == '2' ? 'Filtre statut' : filtre.emplacement == '1' ? 'Menu gauche' : 'Filtres standards' }}
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									Calculs
								</h4>
								<span data-placement="left" class="ml-auto">
									<input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.listes.recherche')" style="padding-left: 5px;width:auto;" type="text" name="recherche" v-model="recherche_calculs" />
									<span data-toggle="tooltip" title="" class="css_ajouter_element" data-original-title="Nouveau calcul" @click="ajouter_calcul"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
								</span>
								@if(empty($id_rapport))
									<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=6&recherche=liste.{{$type_element}}" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Affichage traduction global">
										<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
									</a>
								@else
									<a style="margin-left: 10px;" target="_blank" href="eden/parametrage/traduction?categorie=6&recherche=rapport.{{$id_rapport}}" data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element" data-original-title="Affichage traduction global">
										<i class="css_action_icon fas fa-flag" aria-hidden="true"></i>
									</a>
								@endif
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover" width="100%" cellspacing="0">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Nom</th>
												<th scope="col">Donnée</th>
												<th scope="col">Type</th>
												<th scope="col">Split</th>
												<th scope="col">Unité</th>
											</tr>
										</thead>
										<tbody class="sortable" type_element='calcul'>
											<tr v-for="calcul in calculs" v-if="correspond_recherche(calcul.nom,recherche_calculs)" :key='calcul.id' :data-ordre="calcul.ordre" :data-id_colonne_sortable="calcul.id">
												<td><span class="css_ajouter_element css__lien"   @click="modifier_calcul" :id_calcul="calcul.id">@{{ calcul.id }}</span></td>
												<td>@{{ traduction(calcul.index_traduction,'nom') }}</td>
												<td>@{{ calcul.nom_sql }}</td>
												<td>@{{ calcul.type_calcul }}</td>
												<td>@{{ calcul.split }}</td>
												<td>@{{ calcul.unite }}</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<recherche-avancee v-if="rapport.type != 'requete_sql'" :parametres_recherche_avancee="{type_element: liste_libre.type_element, type : 'liste', id_cible : liste_libre.id}"></recherche-avancee>

				<recherche-avancee v-if="rapport.type != 'requete_sql'" :bloc_unitaire="true" :parametres_recherche_avancee="{type_element: liste_libre.type_element, type : 'filtres_appliques', id_cible : liste_libre.id}"></recherche-avancee>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									Couleurs
								</h4>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouvelle couleur" @click="ajouter_couleur"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-1" data-original-title="Sauvegarder les couleurs" @click="sauvegarder_couleurs"><i class="css_action_icon fa fa-fw fa-save" aria-hidden="true"></i></span>
							</div>
							<div class="card-body">
								<div v-for="(la_couleur,index) in les_couleurs" :key="la_couleur.id">
									<div class="row">
										<div class="col-sm-12 css_form_ligne_titre">
											Couleur @{{index+1}}
											<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Supprimer couleur" @click="les_couleurs.splice(index,1)">
												<i class="fa fa-fw fa-trash" aria-hidden="true"></i>
											</span>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-2">
											Couleur
										</div>
										<div class="col-sm-4">
											<input type="color" v-model.lazy="la_couleur.couleur" name="couleur">
										</div>
									</div>
									<div class="row">
										<div class="col-sm-2">
											Filtres
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">
											<recherche-avancee ref="recherche_avancee" :chargement_externe="true" :bloc_unitaire="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: liste_libre.type_element, type : 'listes_libres_couleur', id_cible : la_couleur.id}" :informations_complementaires="{element_a_filtrer:la_couleur}"></recherche-avancee>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									Autres vues
								</h4>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouvelle vue" @click="ajouter_autrevue"><i class="css_action_icon fa fa-fw fa-plus-square" aria-hidden="true"></i></span>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover" width="100%" cellspacing="0">
										<thead>
											<tr>
												 <th scope="col">ID</th>
												<th scope="col">Type élément</th>
												<th scope="col">Action</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="autrevue in autresvues">
												 <td>@{{ autrevue.liste_libre_id_2 }}</td>
												<td>@{{ autrevue.type_element_2 }} <span v-if="autrevue.id_rapport != null">( @{{ autrevue.id_rapport }} )</span></td>
												<td><span style="text-decoration: underline;cursor: pointer;color: red;" @click="supprimer_autrevue(autrevue.id)">Supprimer</span></td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									Autres paramètres
								</h4>
								<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Enregistrer" @click="enregistrer_autres_parametres"><i class="css_action_icon far fa-save css_font_16" aria-hidden="true"></i></span>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
										<thead>
											<tr>
												<th scope="col">Paramètre</th>
												<th scope="col">Valeur</th>
											</tr>
										</thead>
										<tbody>

											<tr v-if="rapport.kanban && liste_libre.affichage_kanban_vertical != 1">
												<td>Tri par</td>
												<td>

													<draggable
															:list="liste_libre.tri_kanban"
															class="list-group"
															ghost-class="ghost"
															@start="dragging = true"
															@end="dragging = false"
														>
														<table width="100%">
															<tr v-for="(champ, index) in liste_libre.tri_kanban" :key="champ.nom_sql">
																<td>@{{champ.nom_sql}}</td>
																<td>
																	<span v-if="champ.direction == 'ASC'" class="badge badge-default" @click="champ.direction='DESC';"><i class="fa fa-arrow-up"></i> ASC</span>
																	<span v-else class="badge badge-default" @click="champ.direction='ASC';"><i class="fa fa-arrow-down"></i> DESC</span>
																</td>
																<td><i class="fa fa-trash" @click="liste_libre.tri_kanban.splice(index,1)"></i></td>
															</tr>
														</table>

													</draggable>

													<select onchange="vue_instance.liste_libre.tri_kanban.push({nom_sql:this.value, direction:'ASC'}); vue_instance.$forceUpdate();">
														<option value=""></option>
														@foreach($champs_libres as $nom_sql => $champ)
															<option value="{{$champ->nom_sql}}">{{$champ->nom}}</option>
														@endforeach
													</select>
												</td>
											</tr>

											<tr>
												<td>Désactiver les filtres</td>
												<td>
													<select v-model="liste_libre.desactiver_filtres">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
												<td>Désactiver la recherche avancée</td>
												<td>
													<select v-model="liste_libre.desactiver_recherche_avancee">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											<tr>

											</tr>
											<tr>
												<td>Désactiver les actions</td>
												<td>
													<select v-model="liste_libre.desactiver_actions">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
                                            <tr>
                                                <td>Désactiver actions individuellement</td>
                                                <td>
                                                    <template v-for="nom_action in actions_liste">

                                                        <input type="checkbox" v-model="liste_libre.desactiver_actions_individuelle" :value="nom_action"> @{{ nom_action }}<br>
                                                    </template>
                                                </td>
                                            </tr>
											<tr>
												<td>Désactiver la recherche</td>
												<td>
													<select v-model="liste_libre.desactiver_recherche">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Désactiver les exports</td>
												<td>
													<select v-model="liste_libre.desactiver_export">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Désactiver "nouveau"</td>
												<td>
													<select v-model="liste_libre.desactiver_creation">
														<option value="0">Non</option>
														<option value="1">Oui</option>
														<option value="2">Oui sous condition</option>
													</select>
												</td>
											</tr>
											<tr v-if="liste_libre.desactiver_creation == 2">
												<td>Condition vueJS pour désactivation "nouveau"</td>
												<td>
													<input v-model="liste_libre.condition_desactiver_creation" style="width: 100%;">
												</td>
											</tr>
											<tr>
												<td>Formulaire associé à la liste</td>
												<td>
													<select v-model="liste_libre.formulaire_libre">
														@foreach(\DB::table('eden_formulaireslibres')->where('nom_formulaire','like','%' . $type_element . '%')->orWhere('type_element',$type_element)->get() as $formulaire)
															<option value="{{ $formulaire->nom_formulaire }}" v-html="'<b>'+traduction('{{ $formulaire->index_traduction }}','titre')+'</b> ({{ $formulaire->nom_formulaire }})'"></option>
														@endforeach
													</select>
												</td>
											</tr>
                                            <tr>
												<td>Désactiver les options</td>
												<td>
													<select v-model="liste_libre.desactiver_options">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Désactiver options individuellement</td>
												<td>
													<div style="display:flex;flex-direction: column;">
														<div v-for="option_liste in options_liste">
															<input :id="'desactiver_option_liste_'+option_liste" type="checkbox" v-model="liste_libre.desactiver_options_individuelle" :value="option_liste">
															<label :for="'desactiver_option_liste_'+option_liste" v-html="option_liste"></label>
														</div>
													</div>
												</td>
											</tr>
											<tr>
												<td>Options présentes en mobile</td>
												<td>
													<div style="display:flex;flex-direction: column;">
														<div v-for="option_liste in options_liste">
															<input :id="'mobile_option_liste_'+option_liste" type="checkbox" v-model="liste_libre.options_mobile" :value="option_liste">
															<label :for="'mobile_option_liste_'+option_liste" v-html="option_liste"></label>
														</div>
													</div>
												</td>
											</tr>
											<tr>
												<td>Nombre de lignes par page</td>
												<td>
													<input type="number" name="lignes_par_page" v-model="liste_libre.lignes_par_page" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
												</td>
											</tr>
											<tr>
												<td>Activer la création de tâches en masse</td>
												<td>
													<select v-model="liste_libre.creation_taches_en_masse">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Formulaire dans une modale</td>
												<td>
													<select v-model="liste_libre.formulaire_modale">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Afficher les images</td>
												<td>
													<select v-model="liste_libre.afficher_images">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
											<tr>
												<td>Modèle d'email par défaut</td>
												<td>
													<select v-model="liste_libre.modele_email_defaut">
														<option :value="null">--</option>
														@foreach(modele('modele_email')->get() as $modele_email)
															<option value="{{ $modele_email->id }}">{{ $modele_email->nom }}</option>
														@endforeach
													</select>
												</td>
											</tr>
											<tr>
												<td>Afficher les calculs en haut de la liste</td>
												<td>
													<select v-model="liste_libre.afficher_calculs_haut_liste">
														<option value="0">Non</option>
														<option value="1">Oui</option>
													</select>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			@endif

		</div>
	</div>
	<!-- Modal ajout élément -->
	<template v-if="modal_ajout_element">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Gestion des colonnes de la liste</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span @click="modal_ajout_element = false">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="#" method="post" class="css_form" id="formulaire_champ_libre">
								{{ csrf_field() }}
								<input type="hidden" name="id" v-model="colonne.id" />
								<input type="hidden" name="type" v-model="colonne.type" />
								<input type="hidden" name="liste_libre_id" value="{{ $liste_libre_id }}" />

								<template v-if="rapport.type != 'requete_sql'">
									<!-- choix du type de colonne -->
									<div class="row">
										<div class="col-sm-12 css_form_ligne_titre">Type de colonne</div>
									</div>
									<div class="row">
										<div class="d-flex" style="width: 100%;">
											<div v-for="type in types_colonnes" class="p-2" style="flex-grow: 1;flex-basis: 0;">
												<div class="css_option_choix_sur_formulaire" :class="{css_option_choix_sur_formulaire_actif: colonne.type == type.valeur || (type.valeur == 'standard' && !colonne.type)}" @click="modifier_type_colonne(type.valeur)">
													<span class="css_option_choix_sur_formulaire_titre">@{{ type.traduction }}</span>
													@{{ type.description }}
												</div>
											</div>
										</div>
									</div>
								</template>

								<div class="row">
									<div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
								</div>
								<div class="row" v-if="colonne.index_traduction != '' && colonne.index_traduction != null">
									<div class="col-sm-12">
										<traduction-table ref="traduction_table_colonne"  categorie="5" :filtrage_index="colonne.index_traduction+'.'"></traduction-table>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Nom</div>
									<div class="col-sm-4">
                                        <input v-if="!colonne.index_traduction" type="text" name="nom" v-model="colonne.nom" />
                                        <input v-else type="text" disabled :value="$root.traduction(colonne.index_traduction + '.nom')" />
                                    </div>
								</div>
								<template v-if="rapport && rapport.type == 'requete_sql'">
									<div class="row">
										<div class="col-sm-2">Valeur</div>
										<div class="col-sm-4">
											<input type="text" name="valeur" v-model="colonne.valeur"></input>
										</div>
									</div>
									<div class="row" v-if="colonne.valeur != 'id'">
										<div class="col-sm-2">Champ de référence</div>
										<div class="col-sm-4">
											<input type="hidden" name="champ" v-model="colonne.champ" />
											<select-champs-libres :champs_libres="champs_libres_champ"
												:nom_sql="colonne.champ"
												:type_element_origine="type_element"
												:type_element="type_element"
												@changement_select_champs_libres="changement_valeur_select($event, 'champ')">
											</select-champs-libres>
										</div>
									</div>
								</template>
								<div v-else-if="colonne.type == 'concatenation'" class="row">
									<div class="col-sm-2">Valeur</div>
									<div class="col-sm-4">
										<input-parametrage :type_utilisateur="$root.moi.type_utilisateur" at_custom="#" name="valeur" :vmodel="colonne" :donnees="champs_libres_input_parametrage" :champs_de_liaison="true"></input-parametrage>
									</div>
								</div>
								<div class="row" v-else-if="colonne.type == 'champ'">
									<input type="hidden" name="champ" v-model="colonne.champ" />
									<div class="col-sm-2">Champ</div>
									<div class="col-sm-4">
										<select-champs-libres :champs_libres="champs_libres_champ"
											:nom_sql="colonne.champ"
											:type_element_origine="type_element"
											:type_element="type_element"
											@changement_select_champs_libres="changement_valeur_select($event, 'champ')">
										</select-champs-libres>
									</div>
								</div>
								<template v-else-if="colonne.type == 'methode'">
									<div class="row" style="gap: 5px 0">
										<div class="col-sm-2">Méthode</div>
										<div class="col-sm-4"><input type="text" name="methode" v-model="colonne.methode" /></div>
                                        
										<div class="col-sm-2">Arguments</div>
										<div class="col-sm-4"><input type="text" name="arguments" v-model="colonne.arguments" /></div>
                                        <input type="hidden" name="valeur" v-model="colonne.valeur" />
										<div class="col-sm-2">Champ de référence</div>
										<div class="col-sm-4">
											<select-champs-libres :champs_libres="champs_libres_pour_select.filter(c => c.champ_liaison == null)"
												:nom_sql="colonne.valeur"
												:type_element_origine="type_element"
												:type_element="type_element"
												@changement_select_champs_libres="changement_valeur_select($event, 'valeur')">
											</select-champs-libres>
										</div>
									</div>
								</template>
								<template v-else-if="colonne.type == 'calcul'">
									<div class="row">
										<div class="col-sm-2">Source du calcul</div>
										<div class="col-sm-10">
											<parametrage-lien-champ
												:lien_champ="colonne.source_calcul"
												@changement_lien_champ="$set(colonne,'source_calcul',$event);chargement_colonne_calcul();"
												@changement_filtrages="colonne_calcul.filtrages = $event"
												:recherches_avancees="colonne.filtrages_calcul"
												:type_element="liste_libre.type_element"
												:filtres_valeur_final="{
													tables_libres_final : 'tous',
												}"></parametrage-lien-champ>
											<input type="hidden" name="source_calcul" :value="colonne.source_calcul">
											<input type="hidden" name="filtrages_calcul" :value="JSON.stringify(colonne_calcul.filtrages)">
										</div>
									</div>
									<template v-if="colonne.source_calcul != null">
										<div class="row">
											<div class="col-sm-2">Type de calcul</div>
											<div class="col-sm-4">
												<select name="type_calcul" name="type_calcul" v-model="colonne.type_calcul" @change="chargement_colonne_calcul">
													<option value="count">Nombre</option>
													<option value="sum">Somme</option>
													<option value="avg">Moyenne</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="colonne.type_calcul != null && colonne.type_calcul != 'count'">
											<div class="col-sm-2">Champ de calcul</div>
											<div class="col-sm-4">
												<select-champs-libres
													:champs_libres="[{
														type_element : colonne_calcul.type_element,
														index_traduction : 'tables_libres.'+colonne_calcul.type_element+'.nom_table',
														champs_libres : colonne_calcul.champs_libres.filter(champ => [2,3].includes(champ.type))
													}]"
													:type_element_origine="colonne_calcul.type_element"
													:type_element="colonne_calcul.type_element"
													@changement_select_champs_libres="$set(colonne,'champ_calcul',$event.nom_sql)"
													:nom_sql="colonne.champ_calcul"
												></select-champs-libres>
												<input type="hidden" name="champ_calcul" :value="colonne.champ_calcul">
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2">Groupement du calcul</div>
											<div class="col-sm-4">
												<select-champs-libres
													:champs_libres="[{
														type_element : colonne_calcul.type_element,
														index_traduction : 'tables_libres.'+colonne_calcul.type_element+'.nom_table',
														champs_libres : colonne_calcul.champs_libres.filter(champ => [1,20,42,4,5].includes(champ.type))
													}]"
													:type_element_origine="colonne_calcul.type_element"
													:type_element="colonne_calcul.type_element"
													@changement_select_champs_libres="$set(colonne,'groupement_calcul',$event.nom_sql)"
													:nom_sql="colonne.groupement_calcul"
												></select-champs-libres>
												<input type="hidden" name="groupement_calcul" :value="colonne.groupement_calcul">
											</div>
										</div>
										<div class="row" v-if="colonne_calcul.champs_libres.find(c => c.nom_sql == colonne.groupement_calcul && [4,5].includes(c.type))">
											<div class="col-sm-2">Périodicité du calcul</div>
											<div class="col-sm-4">
												<select name="periodicite_calcul" v-model="colonne.periodicite_calcul">
													<option value="">Aucune</option>
													<option v-for="periodicite in colonne_calcul.periodicites" :value="periodicite">@{{traduction('rapport.divers.' + periodicite)}}</option>
												</select>
											</div>
										</div>
									</template>
								</template>
								<div v-else class="row">
									<input type="hidden" name="valeur" v-model="colonne.valeur" />
									<div class="col-sm-2">Valeur</div>
									<div class="col-sm-4">
										<select-champs-libres :champs_libres="champs_libres_pour_select"
											:nom_sql="nom_sql_pour_select_champs_libres"
											:type_element_origine="type_element"
											:type_element="type_element_select_champs_libres"
											@changement_select_champs_libres="changement_valeur_select($event, 'valeur')">
										</select-champs-libres>
									</div>
								</div>
								<div class="row">
                                	<template v-if="['standard', 'concatenation'].includes(colonne.type)">    
										<div class="col-sm-2">Lien</div>
                                    	<div class="col-sm-4">
											<select name="lien_vers_element" v-model="colonne.lien_vers_element">
												<option value="0">Non</option>
												<option value="1">Oui</option>
											</select>
										</div>
									</template>
									<template v-if="['standard', 'concatenation'].includes(colonne.type) && colonne.lien_vers_element == 1"> 
										<div class="col-sm-2">Lien vers</div>
                                        <div class="col-sm-4">
                                            <select name="lien_vers_autre_element" v-model="colonne.lien_vers_autre_element">
                                                <option :value=null>Élement actuel</option>
                                                @foreach($champs_libres_type as $champ_libre_type)
                                                    <option value="{{$champ_libre_type->nom_sql}}">{{$champ_libre_type->nom}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </template>
								</div>
								<div class="row">
									<template v-if="['standard', 'concatenation'].includes(colonne.type) && colonne.lien_vers_element == 1 && colonne.lien_vers_autre_element == null">    
										<div class="col-sm-2">Afficher le formulaire de l'élément</div>
                                    	<div class="col-sm-4">
											<select name="afficher_formulaire_element" v-model="colonne.afficher_formulaire_element">
												<option value="0">Non</option>
												<option value="1">Oui</option>
											</select>
										</div>
									</template>
								</div>
								<div class="row" v-if="colonne.type == 'standard' && afficher_option_avatar(colonne.valeur)">
									<div class="col-sm-2">Afficher les avatars des utilisateurs</div>
									<div class="col-sm-4">
										<select name="afficher_avatars_utilisateurs" v-model="colonne.afficher_avatars_utilisateurs">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>
								</div>
                                <div class="row" style="gap: 5px 0" v-show="colonne.type == 'standard' || colonne.type == 'concatenation' || colonne.type == 'champ' || (colonne.type == 'methode' && colonne.valeur != '' && colonne.valeur != null)">
                                    <div class="col-sm-2">Tri désactivé</div>
                                    <div class="col-sm-4">
                                        <select name="tri_desactive" v-model="colonne.tri_desactive">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                    <template v-if="colonne.tri_desactive != 1">
                                        <div class="col-sm-2">Tri par défaut</div>
                                        <div class="col-sm-4">
                                            <select name="tri_par_defaut" v-model="colonne.tri_par_defaut">
                                                <option value="0">Non</option>
                                                <option value="1">Oui</option>
                                            </select>
                                        </div>
                                        <template v-if="colonne.tri_par_defaut == 1">
                                            <div class="col-sm-2">Sens du tri par défaut</div>
                                            <div class="col-sm-4">
                                                <select name="sens_tri_par_defaut" v-model="colonne.sens_tri_par_defaut">
                                                    <option value="0">ASC</option>
                                                    <option value="1">DESC</option>
                                                </select>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                                <div class="row">
                                    <template v-if="colonne.type == 'standard' || colonne.type == 'champ' || colonne.type == 'concatenation'">
										<div class="col-sm-2">Retour à la ligne impossible</div>
										<div class="col-sm-4">
											<select name="retour_a_la_ligne_impossible" v-model="colonne.retour_a_la_ligne_impossible">
												<option value="0">Non</option>
												<option value="1">Oui</option>
											</select>
										</div>
                                        <div class="col-sm-2">Nombre de caractères max</div>
                                        <div class="col-sm-4"><input type="text" name="caracteres_max" placeholder="Non limité" v-model="colonne.caracteres_max" /></div>
                                    </template>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Alignement colonne</div>
                                    <div class="col-sm-4">
                                        <select v-model="colonne.alignement_colonne" name="alignement_colonne">

                                            <option value="left">Gauche</option>
                                            <option value="center">Centre</option>
                                            <option value="right">Droite</option>

                                        </select>
                                    </div>
                                    
                                    <div class="col-sm-2">Ordre</div>
                                    <div class="col-sm-4"><input type="text" name="ordre" v-model="colonne.ordre" /></div>
                                </div>
								<div class="row">
									<template v-if="colonne.id > 0 ">
										<div class="col-sm-2">Profils autorisés </div>
										<div class="col-sm-4">
											<profil-droits-divers type="listes_libres_colonnes" :index="colonne.id"></profil-droits-divers>
										</div>
									</template>
								</div>
								<div class="row">
									<div class="col-sm-2">Couleur</div>
									<div class="col-sm-4">
                                        <input type="color" v-model="colonne.couleur_colonne" name="couleur_colonne">
									</div>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modal_ajout_element = false">Fermer</button>
							<button type="button" class="btn btn-danger" @click="supprimer" v-show="colonne.id != undefined">Supprimer</button>
							<button type="button" class="btn btn-primary" @click="enregistrer">Enregistrer</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

	<!-- Modal ajout filtre -->
	<template v-if="modal_ajout_filtre">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Gestion des filtres de la liste</h5>
							<button type="button" class="close" @click="modal_ajout_filtre = false;" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="#" method="post" class="css_form" id="formulaire_filtre">
								{{ csrf_field() }}
								<input type="hidden" name="id" v-model="filtre.id" />
								<input type="hidden" name="liste_libre_id" value="{{ $liste_libre_id }}" />
								<div class="row">
									<div class="col-sm-2">Table</div>
									<div class="col-sm-10">
										<input type="hidden" name="type_element" v-if="filtre.tableau_liaison" v-model="filtre.tableau_liaison.type_element">
										<input type="hidden" name="champ_de_liaison" v-if="filtre.tableau_liaison" v-model="filtre.tableau_liaison.champ_de_liaison">
										<select v-model="filtre.tableau_liaison">
											<option selected :value="{'type_element' : '','champ_de_liaison' : null}">Table actuelle ({{ $type_element }})</option>
											@foreach($champs_libres_liaison_par_type as $informations)
												@if($informations['type'] == 21)
													<option :value="{'type_element' : '{{$informations['type_element']}}','champ_de_liaison' : '{{$informations['id_element_dynamique']}}'}">{{table_libre($informations['type_element'])->nom_table}} ({{ $informations['id_element_dynamique'] }})</option>
												@else
													<option :value="{'type_element' : '{{$informations['type_element']}}','champ_de_liaison' : '{{$informations['champ_de_liaison']}}'}">{{table_libre($informations['type_element'])->nom_table}} ({{ $informations['champ_de_liaison'] }})</option>
												@endif
											@endforeach
										</select>
									</div>
								</div>
								<div class="row" v-if="!filtre.tableau_liaison || filtre.tableau_liaison.type_element == ''">
									<div class="col-sm-2">Valeur</div>
									<div class="col-sm-10">
										<select name="nom_sql" v-model="filtre.nom_sql">
											@foreach($champs_libres_par_type as $type_de_champ => $champs_libres_pour_ce_type)
												<optgroup label="{{ $type_de_champ }}">
													@foreach($champs_libres_pour_ce_type as $champ_libre)
														<option value="{{ $champ_libre->nom_sql }}">{{ $champ_libre->nom }} ({{ $champ_libre->nom_sql }})</option>
													@endforeach
												</optgroup>
											@endforeach
										</select>
									</div>
								</div>
								<template v-else>
									@foreach($champs_libres_liaison_par_type as $informations)
										<div class="row" v-if="'{{$informations['type_element']}}' == filtre.tableau_liaison.type_element && 
											'{{ $informations['type'] == 21 ? $informations['id_element_dynamique'] : $informations['champ_de_liaison']}}' == filtre.tableau_liaison.champ_de_liaison ">
											<div class="col-sm-2">Valeur</div>
											<div class="col-sm-10">
												<select name="nom_sql" v-model="filtre.nom_sql">
													@foreach($informations['champs_libres'] as $type_de_champ_dynamique => $champs_libres_pour_ce_type_dynamique)
														<optgroup label="{{ $type_de_champ_dynamique }}">
															@foreach($champs_libres_pour_ce_type_dynamique as $champ_libre_dynamique)
																@if(is_object($champ_libre_dynamique))
																	<option value="{{ $champ_libre_dynamique->nom_sql }}">{{ $champ_libre_dynamique->nom }}</option>
																@endif
															@endforeach
														</optgroup>
													@endforeach
												</select>
											</div>
										</div>
									@endforeach
								</template>
								<div class="row">
									<div class="col-sm-2">Type</div>
									<div class="col-sm-10">
										<select name="type_filtre" v-model="filtre.type_filtre">
											<option value="">Standard</option>
											<option value="select">Select (liste déroulante)</option>
											<option value="typeahead">Typeahead</option>

										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Emplacement</div>
									<div class="col-sm-10">
										<select name="emplacement" v-model="filtre.emplacement">
											<option value="">Standard</option>
											<option value="1" v-show="[1,20,42].includes(filtre.type_de_champ) === true">Menu gauche</option>
										</select>
									</div>
								</div>

								<div class="row" v-show="[12].includes(filtre.type_de_champ) === true">
									<div class="col-sm-2">Afficher les catégories</div>
									<div class="col-sm-10">
										<select name="afficher_categories_liste" v-model="filtre.afficher_categories_liste">
											<option value="0">Non</option>
											<option value="1">Oui</option>
										</select>
									</div>
								</div>

								<div class="row" v-if="filtre.id > 0">
									<div class="col-sm-2">Profils autorisés </div>
									<div class="col-sm-10">
										<profil-droits-divers type="eden_listes_libres_filtres" :index="filtre.id"></profil-droits-divers>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Ordre</div>
									<div class="col-sm-10"><input type="text" name="ordre" v-model="filtre.ordre" /></div>
								</div>

							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modal_ajout_filtre = false;">Fermer</button>
							<button type="button" class="btn btn-danger" @click="modal_ajout_filtre = false;supprimer_filtre_liste_libre()" v-show="filtre.id != undefined">Supprimer</button>
							<button type="button" class="btn btn-primary" @click="enregistrer_filtre_liste_libre">Enregistrer</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>

	<!-- Modal ajout calcul -->
	<template v-if="modal_ajout_calcul">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title">Gestion des calculs de la liste</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span @click="modal_ajout_calcul = false">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<form action="#" method="post" class="css_form" id="formulaire_calcul">
								{{ csrf_field() }}
								<input type="hidden" name="id" v-model="calcul.id" />
								<input type="hidden" name="liste_libre_id" value="{{ $liste_libre_id }}" />
								<div class="row" v-if="calcul.index_traduction != '' && calcul.index_traduction != null">
									<div class="col-sm-12">
										<traduction-table ref="traduction_table_calcul"  categorie="6" :filtrage_index="calcul.index_traduction+'.'"></traduction-table>
									</div>
								</div>
								<div class="row" v-else>
									<div class="col-sm-2">Nom</div>
									<div class="col-sm-10"><input type="text" name="nom" v-model="calcul.nom" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">Table</div>
									<div class="col-sm-10">
										<input type="hidden" name="type_element" v-if="calcul.table_liaison" v-model="calcul.table_liaison.type_element">
										<input type="hidden" name="champ_de_liaison" v-if="calcul.table_liaison" v-model="calcul.table_liaison.champ_de_liaison">
										<select v-model="calcul.table_liaison" @change="changement_table_calcul()">
											<option selected :value="{'type_element' : '','champ_de_liaison' : null}">Table actuelle ({{ $type_element }})</option>
											@foreach($champs_libres_liaison_par_type as $informations)
												@if($informations['type'] == 21)
													<option :value="{'type_element' : '{{$informations['type_element']}}','champ_de_liaison' : '{{$informations['id_element_dynamique']}}'}">{{table_libre($informations['type_element'])->nom_table}} ({{ $informations['id_element_dynamique'] }})</option>
												@else
													<option :value="{'type_element' : '{{$informations['type_element']}}','champ_de_liaison' : '{{$informations['champ_de_liaison']}}'}">{{table_libre($informations['type_element'])->nom_table}} ({{ $informations['champ_de_liaison'] }})</option>
												@endif
											@endforeach
										</select>
									</div>
								</div>
								<div class="row" v-if="!calcul.table_liaison || calcul.table_liaison.type_element == ''">
									<div class="col-sm-2">Donnée</div>
									<div class="col-sm-10">
										<input type="text" v-if="rapport.type == 'requete_sql'" name="nom_sql" v-model="calcul.nom_sql">
										<select name="nom_sql" v-else v-model="calcul.nom_sql">
											@foreach($champs_libres_par_type as $type_de_champ => $champs_libres_pour_ce_type)
												<optgroup label="{{ $type_de_champ }}">
													@foreach($champs_libres_pour_ce_type as $champ_libre)
														<option value="{{ $champ_libre->nom_sql }}">{{ $champ_libre->nom }} ({{ $champ_libre->nom_sql }})</option>
													@endforeach
												</optgroup>
											@endforeach
										</select>
									</div>
								</div>
								<template v-else>
									@foreach($champs_libres_liaison_par_type as $informations)
										<div class="row" v-if="'{{$informations['type_element']}}' == calcul.table_liaison.type_element && 
											'{{ $informations['type'] == 21 ? $informations['id_element_dynamique'] : $informations['champ_de_liaison']}}' == calcul.table_liaison.champ_de_liaison ">
											<div class="col-sm-2">Donnée</div>
											<div class="col-sm-10">
												<select name="nom_sql" v-model="calcul.nom_sql">
													@foreach($informations['champs_libres'] as $type_de_champ_dynamique => $champs_libres_pour_ce_type_dynamique)
														<optgroup label="{{ $type_de_champ_dynamique }}">
															@foreach($champs_libres_pour_ce_type_dynamique as $champ_libre_dynamique)
																@if(is_object($champ_libre_dynamique))
																	<option value="{{ $champ_libre_dynamique->nom_sql }}">{{ $champ_libre_dynamique->nom }}</option>
																@endif
															@endforeach
														</optgroup>
													@endforeach
												</select>
											</div>
										</div>
									@endforeach
								</template>
								<div class="row" v-if="rapport && rapport.type == 'requete_sql' && calcul.nom_sql != 'id' &&
									champs_libres.filter(champ => calcul.nom_sql == champ.nom_sql).length == 0">
									<div class="col-sm-2">Champ de référence</div>
									<div class="col-sm-6">
										<select name="champ_reference" v-model="calcul.champ_reference">
											<option :value=null>-- Pas de champ de référence --</option>
											@foreach($champs_libres_par_type as $type_de_champ => $champs_libres_pour_ce_type)
												<optgroup label="{{ $type_de_champ }}"></optgroup>
												@foreach($champs_libres_pour_ce_type as $champ_libre)
													<option value="{{$champ_libre->nom_sql}}">{{$champ_libre->nom}}</option>
												@endforeach
											@endforeach
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Type</div>
									<div class="col-sm-10">
										<select name="type_calcul" v-model="calcul.type_calcul">
											<option value="SUM">Somme</option>
											<option value="COUNT">Nombre</option>
											<option value="AVG">Moyenne</option>
											<option value="AVG_COUNT">Moyenne groupée</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Unité</div>
									<div class="col-sm-10"><input type="text" name="unite" v-model="calcul.unite" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">Split (Table | colonne)</div>
									<div class="col-sm-5">
										<input type="hidden" name="split" v-model="calcul.split" />
										<input type="hidden" name="type_element_split" v-model="calcul.type_element_split" />
										<select name="split_champ_liaison" v-model="split_champ_liaison">
											<option v-if="!calcul.table_liaison.champ_de_liaison && !calcul.table_liaison.type_element" :value="null">Table actuelle ({{ $type_element }})</option>
											@foreach($champs_libres_liaison_par_type as $informations)
												@php
													$valeur = $informations['type'] == 21
														? $informations['id_element_dynamique']
														: $informations['champ_de_liaison'];
												@endphp

												<option v-if="(calcul.table_liaison.type_element && calcul.table_liaison.champ_de_liaison && '{{ $valeur }}' == calcul.table_liaison.champ_de_liaison) || (!calcul.table_liaison.type_element && !calcul.table_liaison.champ_de_liaison)"
														:value="'{{ $valeur }}'">{{ table_libre($informations['type_element'])->nom_table }} ({{ $valeur }})
												</option>
											@endforeach
										</select>
									</div>
									<div class="col-sm-5" v-if="!calcul.split || !calcul.split.includes('.')">
										<select name="split_nom_sql" v-model="split_nom_sql">
											@foreach($champs_libres_par_type as $type_de_champ => $champs_libres_pour_ce_type)
												<optgroup label="{{ $type_de_champ }}">
													@foreach($champs_libres_pour_ce_type as $champ_libre)
														<option value="{{ $champ_libre->nom_sql }}">{{ $champ_libre->nom }} ({{ $champ_libre->nom_sql }})</option>
													@endforeach
												</optgroup>
											@endforeach
										</select>
									</div>
									<div class="col-sm-5" v-else>
										@foreach($champs_libres_liaison_par_type as $informations)
											<select v-if="'{{ $informations['type'] == 21 ? $informations['id_element_dynamique'] : $informations['champ_de_liaison']}}' == split_champ_liaison && (!calcul.type_element_split || '{{ $informations['type_element'] }}' == calcul.type_element_split)" name="split_nom_sql" v-model="split_nom_sql">
												@foreach($informations['champs_libres'] as $type_de_champ_dynamique => $champs_libres_pour_ce_type_dynamique)
													<optgroup label="{{ $type_de_champ_dynamique }}">
														@foreach($champs_libres_pour_ce_type_dynamique as $champ_libre_dynamique)
															@if(is_object($champ_libre_dynamique))
																<option value="{{ $champ_libre_dynamique->nom_sql }}">{{ $champ_libre_dynamique->nom }}</option>
															@endif
														@endforeach
													</optgroup>
												@endforeach
											</select>
										@endforeach
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Top</div>
									<div class="col-sm-10"><input type="text" name="top" v-model="calcul.top" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">Afficher la somme</div>
									<div class="col-sm-10"><select name="afficher_somme" v-model="calcul.afficher_somme"><option value="1">Oui</option><option value="0">Non</option></select></div>
								</div>

								<div class="row">
									<div class="col-sm-2">Toujours déployé</div>
									<div class="col-sm-10"><select name="toujours_deploye" v-model="calcul.toujours_deploye"><option value="1">Oui</option><option value="0">Non</option></select></div>
								</div>
								<div class="row" v-if="calcul.id > 0">
									<div class="col-sm-2">Profils autorisés </div>
									<div class="col-sm-10">
										<profil-droits-divers type="eden_listes_libres_calculs" :index="calcul.id"></profil-droits-divers>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-2">Ordre</div>
									<div class="col-sm-10"><input type="text" name="ordre" v-model="calcul.ordre" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">V-if</div>
									<div class="col-sm-10"><input type="text" name="v_if" v-model="calcul.v_if" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">Taille</div>
									<div class="col-sm-10"><input type="text" name="taille" v-model="calcul.taille" /></div>
								</div>
								<div class="row">
									<div class="col-sm-2">Icône</div>
									<div class="col-sm-1" style="display:flex;gap:2px">
										<button type="button" class="btn btn-primary iconpicker-component" style="position:relative;">
											<i :class="calcul.icone"></i>
											<span v-if="calcul.icone" @click="calcul.icone = ''" class="fa fa-times btn-primary" style="position:absolute;border-radius:10px;top:-10px;right:-10px;padding: 2px 5px;"></span>
										</button>
										<button type="button" class="icp icp-dd btn btn-primary dropdown-toggle calcul" data-selected="fa-car" data-toggle="dropdown" :id="calcul" >
											<span class="caret"></span>
											<span class="sr-only">Icône</span>
										</button>
										<div class="dropdown-menu"></div>
										<span class="css_ajouter_element mr-3" data-toggle="tooltip" data-placement="top" title="Supprimer la valeur" @click="calcul.icone = ''">
											<i class="css_action_icon fas fa-trash"></i>
										</span>
									</div>
									<input type="hidden" name="icone" v-model="calcul.icone" />
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" @click="modal_ajout_calcul = false">Fermer</button>
							<button type="button" class="btn btn-danger" @click="supprimer_calcul" v-show="calcul.id != undefined">Supprimer</button>
							<button type="button" class="btn btn-primary" @click="enregistrer_calcul">Enregistrer</button>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</template>


	<!-- Modal ajout autre vue -->
	<div class="modal fade" id="modal_ajout_autrevue" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Gestion des autres vues</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" id="formulaire_autrevue">
						{{ csrf_field() }}
						<input type="hidden" name="liste_libre_id" value="{{ $liste_libre_id }}" />
						<div class="row">
							<div class="col-sm-4">Liste libre à relier</div>
							<div class="col-sm-8">
									<select name="liste_libre_2">
										@if(!empty($listes_libres_du_type_element))
											<optgroup label="Du même type élement">
												@foreach($listes_libres_du_type_element as $liste_libre_tmp)
													<option value="{{ $liste_libre_tmp->id }}">{{ $liste_libre_tmp->type_element }}@if($liste_libre_tmp->id_rapport != null) ( {{ $liste_libre_tmp->id_rapport }} )@endif</option>
												@endforeach
											</optgroup>
										@endif
										<optgroup label="Autre listes">
											@foreach($listes_libres as $liste_libre_tmp)
												<option value="{{ $liste_libre_tmp->id }}">{{ $liste_libre_tmp->type_element }}@if($liste_libre_tmp->id_rapport != null) ( {{ $liste_libre_tmp->id_rapport }} )@endif</option>
											@endforeach
										</optgroup>
									</select>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
					<button type="button" class="btn btn-primary" @click="enregistrer_autrevue">Enregistrer</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')
	colonnes: {!! $colonnes !!},
	filtres: {!! $filtres !!},
	calculs: {!! $calculs !!},
	autresvues: {!! $autresvues !!},
	colonne: {},
	filtre: {},
	calcul: {
		table_liaison: {
			type_element : '',
			champ_de_liaison : null,
		},
		type_element_split: '',
	},
	couleur: '',
	les_couleurs: {!! $couleurs !!},
	id_rapport: '{!! $id_rapport !!}',
	liste_libre: {!! $liste_libre !!},
	actions_liste: {!! $actions_liste !!},
	recherche_filtres_appliques: '',
	recherche_filtres: '',
	recherche_calculs: '',
	rapport: {!! $rapport !!},
	modal_ajout_element: false,
    modal_ajout_filtre: false,
	modal_ajout_calcul: false,
	colonnes_kanban: {},
	choix_colonnes_kanban: {},
	options_liste: {!! $options_liste !!},
	champs_libres_par_type: {!! collect($champs_libres_par_type) !!},
	champs_libres_element : {},
    champs_libres: {!! collect($champs_libres) !!},
	champs_libres_liaison_par_type: {!! collect($champs_libres_liaison_par_type) !!},
	champs_libres_pour_select: {!! $champs_libres_pour_select !!},
	type_element: '{{ $type_element }}',
	types_colonnes: [
		{valeur: 'standard', traduction: 'Standard', description: "Ajoute une colonne basée sur un ou des champs de la table."},
		{valeur: 'concatenation', traduction: 'Concaténation', description: "Ajoute une colonne basée sur la concaténation de plusieurs champs."},
		{valeur: 'champ', traduction: 'Champ modifiable', description: "Ajoute une colonne pour modifier les valeurs de la table."},
		{valeur: 'calcul', traduction: 'Calcul', description: "Ajoute une colonne basée sur un calcul."},
		{valeur: 'methode', traduction: 'Méthode', description: "Ajoute une colonne basée sur une méthode du management."},
	],
	colonne_calcul : {
		type_element: null,
		champs_libres: [],
		filtrages: [],
		periodicites: ["quotidienne","hebdomadaire","mensuelle","trimestrielle","semestrielle","annuelle"],
	},
@endpush

<script>
@push('donnees_pour_vuejs_methods')
	ajouter() {

		this.modal_ajout_element = true;

		var ordre = this.colonnes.length + 1;

		this.colonne = {
			nom: '',
			valeur: '',
			champ: '',
			type: 'standard',
			ordre: ordre,
			index_traduction: '',
		};
	},

	choix_champ_libre_pour_colonne(nom_sql, nom_champ, colonne) {

		colonne.valeur = '#'+nom_sql+'#';
		colonne.nom = nom_champ;

		nom_sql = nom_sql.replace('.', '_');

	},

	modifier_type_colonne: function(nouvelle_valeur) {

		this.colonne.type = nouvelle_valeur;

		this.colonne.valeur = null;
		this.colonne.champ = null;
	},

	modifier(event) {

		var event = $(event.target);

		var vue_contexte = this;

		// on récupère les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/colonne") }}/"+event.attr('id_colonne'),
			dataType: "json"
		}).done(async (colonne) => {

			this.colonne = colonne;

            this.modal_ajout_element = true;

			await vue_contexte.$refs.traduction_table_colonne !== undefined;

			vue_contexte.$refs.traduction_table_colonne.charger_traductions();

			this.chargement_colonne_calcul();
		});
	},

	enregistrer() {

		loading(true);

		var vue_contexte = this;
    
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.colonne.enregistrer", [$liste_libre_id]) }}",
			dataType: "json",
			data: $('#formulaire_champ_libre').serialize()
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			vue_contexte.modal_ajout_element = false;

			vue_contexte.colonnes = donnees.colonnes;

			vue_contexte.mise_a_jour_traductions_valeurs();
		});

	},

	supprimer() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/colonne") }}/"+$('#formulaire_champ_libre input[name=id]').val()+'/supprimer',
			dataType: "json"
		}).done(function(donnees) {

			loading(false);
			vue_contexte.colonnes = donnees.colonnes;

			vue_contexte.modal_ajout_element = false;
		});

		return false;
	},

	// gestion des filtres
	ajouter_filtre_liste_libre(event) {

		var ordre = this.filtres.length + 1;

		this.modal_ajout_filtre = true;

		this.filtre = {

			id: '',
			nom_sql: '',
			ordre: ordre,
			tableau_liaison: {
				type_element : '',
				champ_de_liaison : null,
			},
		};
	},

	// gestion des couleurs
	ajouter_couleur() {

		this.les_couleurs.push({
			id: this.les_couleurs.length == 0 ? 1 : (Math.max(...this.les_couleurs.map(couleur => couleur.id)) + 1),
			couleur: null,
		});

		this.$forceUpdate();

		this.$nextTick(() => {
			this.$refs.recherche_avancee[(this.$refs.recherche_avancee.length-1)].chargement_initial({
				champs_libres: this.champs_libres_element
			});
		});
	},

	sauvegarder_couleurs : function(){

		loading(true);

		$.post({
			url : '{{route('parametrage.liste_libre.enregistrer_couleurs',$liste_libre_id)}}',
			data : {
				couleurs : this.les_couleurs,
			},
			dataType: 'json'
		}).done(() => {

			loading(false);
		});
	},

	chargement_filtres_couleurs: function(){

		$.post({
			url : '{{route('base_eden.recherche_avancee.donnees_initialisation')}}',
			data : {
				type_element : this.liste_libre.type_element,
				type : 'listes_libres_couleur',
				id_cible : this.les_couleurs.map(couleur => couleur.id)
			},
			dataType: 'json'
		}).done((donnees) => {

			this.champs_libres_element = donnees.champs_libres;

			if(!this.$refs.recherche_avancee)
				return;

			for(ref_recherche_avancee of this.$refs.recherche_avancee){

				if(ref_recherche_avancee.parametres_recherche_avancee.type != 'listes_libres_couleur')
					continue;

				var donnees_recherche = structuredClone(donnees);

				if(donnees_recherche.recherches_par_categories && donnees_recherche.recherches_par_categories[0]){

					var recherche_trouve = false;

					for(recherche of donnees_recherche.recherches_par_categories[0].recherches_avancees){

						var recherche_correspondant = true;

						for(parametre in ref_recherche_avancee.parametres_recherche_avancee){

							if(recherche[parametre] != ref_recherche_avancee.parametres_recherche_avancee[parametre])
								recherche_correspondant = false;
						}

						if(recherche_correspondant === true)
							recherche_trouve = recherche;
					}

					if(recherche_trouve === false)
						donnees_recherche.recherches_par_categories[0].recherches_avancees = [];
					else
						donnees_recherche.recherches_par_categories[0].recherches_avancees = [recherche_trouve];
				}

				ref_recherche_avancee.chargement_initial(donnees_recherche);
			}
		});
	},

	// gestion des autres vues
	ajouter_autrevue(event) {

		$('#modal_ajout_autrevue').modal('show');
	},

	modifier_filtre_liste_libre(event) {

		var event = $(event.target);

		var vue_contexte = this;

		// on récupère les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/filtre") }}/"+event.attr('id_filtre'),
			dataType: "json"
		}).done((filtre) => {

			vue_contexte.filtre = filtre;

			this.modal_ajout_filtre = true;
		});
	},

	changement_type_rapport(){

		if(this.rapport.type == 'liste_libre')
			this.rapport.kanban = "";
		else if(this.rapport.type == 'requete_sql'){
			this.rapport.kanban = "";
			this.$set(this.rapport,'parametres_requete',[]);
		}
		else{
			@if($champs_libres->whereIn('type', [1, 20, 42])->first()!= null)
				this.rapport.kanban = "{{$champs_libres->whereIn('type', [1, 20, 42])->first()->nom_sql}}";
			@else
				this.rapport.kanban = "";
				this.rapport.type = 'liste_libre';
			@endif

			this.charger_parametres_rapport();
		}

	},

	charger_parametres_rapport() {
    
        if(this.champ_kanban && this.champ_kanban.type === 42)
            return;
        
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.rapport.charger", [$liste_libre_id]) }}",
			dataType: "json",
			data: this.rapport
		}).done(async (donnees) => {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;

			} else {

				this.colonnes_kanban = donnees.champ;

				$('.js_colonnes_kanban').sortable();
			}
		});

	},

	async enregistrer_parametres_rapport() {

		loading(true);

		var vue_contexte = this;

		donnees = structuredClone(vue_instance.rapport);
        
        if(!this.champ_kanban || this.champ_kanban.type !== 42)
		    donnees.colonnes_kanban = $('.form_colonnes_kanban').serialize();

        if(this.champ_kanban && !this.rapport.toutes_les_colonnes && !this.rapport.exclusion_colonnes && donnees.colonnes_kanban.length === 0) {
            await erreur(this.$root.traduction('message.js.kanban.erreur_colonne_vide'));
            loading(false);
            return;
        }
		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.rapport.enregistrer", [$liste_libre_id]) }}",
			dataType: "json",
			data: donnees
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			this.rapport.parametres_requete = donnees.parametres_requete;

			info("Enregistrement bien effectué");

			// les paramètres kanban de la liste_libre (affichage vertical, drag & drop...) sont affichés dans cette même carte mais enregistrés séparément
			this.enregistrer_autres_parametres();
		});

	},


	enregistrer_filtre_liste_libre() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.filtre.enregistrer", [$liste_libre_id]) }}",
			dataType: "json",
			data: $('#formulaire_filtre').serialize()
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			this.modal_ajout_filtre = false;

			vue_contexte.filtres = donnees.filtres;
		});

	},
	supprimer_filtre_liste_libre() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/filtre") }}/"+$('#formulaire_filtre input[name=id]').val()+'/supprimer',
			dataType: "json"
        }).done(function(donnees) {
            
            loading(false);
            
            vue_contexte.filtres = donnees.filtres;
            
            vue_contexte.$forceUpdate();
        });
        
    return false;
},

	// gestion des calculs
	ajouter_calcul(event) {

		this.$set(this, 'modal_ajout_calcul', true);

		var ordre = this.calculs.length + 1;

		this.$set(this, 'calcul', {

			id: '',
			index_traduction: '',
			ordre: ordre,
			icone: '',
			split: '',
			type_element_split: '',
			table_liaison: {
				type_element : '',
				champ_de_liaison : null,
			},
		});

		this.$nextTick(() => {
			
			$('.icp-dd').iconpicker({
				defaultValue: false,
				placement: 'bottomLeft',
				hideOnSelect: false,
			});
			
			$('.icp').on('iconpickerSelected',(e) => {
	
				this.calcul.icone = e.iconpickerValue;
				this.$forceUpdate();
			});
		});

		
	},
	modifier_calcul(event) {

		this.modal_ajout_calcul = true;

		var event = $(event.target);

		// on récupère les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/calcul") }}/"+event.attr('id_calcul'),
			dataType: "json"
		}).done(async (calcul) => {

			this.$set(this, 'calcul', calcul);

			await this.$refs.traduction_table_calcul !== undefined;

			this.$refs.traduction_table_calcul.charger_traductions();
			
			$('.icp-dd').iconpicker({
				defaultValue: false,
				placement: 'bottomLeft',
				hideOnSelect: false,
			});

			$('.icp').on('iconpickerSelected',(e) => {
		
				this.calcul.icone = e.iconpickerValue;
				this.$forceUpdate();
			});
		});
	},
	enregistrer_calcul() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.calcul.enregistrer", [$liste_libre_id]) }}",
			dataType: "json",
			data: $('#formulaire_calcul').serialize()
		}).done(async function(donnees) {

			loading(false);

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			vue_contexte.modal_ajout_calcul = false;

			vue_contexte.calculs = donnees.calculs;

			vue_contexte.mise_a_jour_traductions_valeurs();
		});

	},

	supprimer_calcul() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.ajax({

			url: "{{ URL::to("eden/parametrage/liste_libre/calcul") }}/"+$('#formulaire_calcul input[name=id]').val()+'/supprimer',
			dataType: "json"
		}).done(function(donnees) {

			loading(false);

			vue_contexte.calculs = donnees.calculs;
			vue_contexte.modal_ajout_calcul = false;
		});

		return false;
	},

	supprimer_autrevue(id) {

		loading(true);

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ URL::to("eden/parametrage/liste_libre/autrevue/supprimer") }}/"+id,
			dataType: "json",
			data: $('#formulaire_autrevue').serialize()
		}).done(async function(donnees) {

			if(donnees !== true) {

				await erreur(donnees);
				return;
			}

			for (var i = 0; i < vue_instance.autresvues.length; i++) {
				if (vue_instance.autresvues[i]['id'] == id) {
					vue_instance.autresvues.splice(i,1);
				}
			}

			$('#modal_ajout_autrevue').modal('hide');

			loading(false);
		});
	},

	changement_valeur_select(event, variable){
		var valeur = '';

		if(event.champ_liaison != undefined && event.champ_liaison != null && event.champ_liaison != '')
			valeur = event.champ_liaison+'.'+event.nom_sql;
		else
			valeur = event.nom_sql;

		this.$set(this.colonne, variable, valeur);

		if(this.colonne.id == null)
			this.colonne.nom = event.nom_sql == 'id' ? 'ID' : this.$root.traduction(event.modele_champ.index_traduction + '.nom');
	},

	enregistrer_autrevue() {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route("parametrage.liste_libre.autrevue.enregistrer", [$liste_libre_id]) }}",
			dataType: "json",
			data: $('#formulaire_autrevue').serialize()
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$('#modal_ajout_autrevue').modal('hide');

			vue_contexte.autresvues = donnees.autresvues;

			loading(false);
		});

	},

	enregistrer_autres_parametres() {

		loading(true);

		this.liste_libre.desactiver_options_individuelle = JSON.stringify(this.liste_libre.desactiver_options_individuelle);
		this.liste_libre.options_mobile = JSON.stringify(this.liste_libre.options_mobile);
		this.liste_libre.desactiver_actions_individuelle = JSON.stringify(this.liste_libre.desactiver_actions_individuelle);

		// on enregistre les infos du champ libre
		$.post({

			url: "{{ route('parametrage.liste_libre.autres_parametres.enregistrer', [$liste_libre_id]) }}",
			dataType: "json",
			data: this.liste_libre
		}).done(async (donnees) => {


			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				loading(false);
				return;
			}

			loading(false);
			this.liste_libre.desactiver_options_individuelle = JSON.parse(this.liste_libre.desactiver_options_individuelle);
			this.liste_libre.options_mobile = JSON.parse(this.liste_libre.options_mobile);
			this.liste_libre.desactiver_actions_individuelle = JSON.parse(this.liste_libre.desactiver_actions_individuelle);
		});

	},

	changement_etat(parametre, id, nouvelle_valeur) {

		loading(true);

		var vue_contexte = this;

		// on enregistre les infos du champ libre
		$.post({
			url: "{{ route('parametrage.liste_libre.colonne.changement_etat') }}",
			data:{
				id: id,
				parametre: parametre,
				nouvelle_valeur: nouvelle_valeur
			},
			dataType: "json"
		}).done(function(donnees) {

			loading(false);
			vue_contexte.colonnes = donnees.colonnes;
		});
	},

	correspond_recherche(nom,recherche){

		if(nom == null)
			return false;

		return nom.includes(recherche);
	},


	in_array(needle, haystack) {

		if(haystack == null)
			return;

	    var length = haystack.length;
	    for(var i = 0; i < length; i++) {
	        if(haystack[i] == needle) return true;
	    }
	    return false;
	},

	regenere_fichier_composant: function(){

		loading(true);

		$.ajax({
			url : '{{ URL::to("eden/maintenance/generation_fichier/composants/liste/id/".$liste_libre->id) }}',
		}).done(function(){
			loading(false);
			info('Action effectuée avec succès !');
		});
	},

	afficher_option_avatar: function(valeur){
		if(valeur == null)
			return false;
		const champ_valeur = valeur.includes('.') ? valeur.split('.').slice(-1)[0] : valeur;
		for(champ of Object.values(this.champs_libres)){
			if(champ.nom_sql == champ_valeur && champ.type_element_ajax == "utilisateur")
				return true;
		}
		return false;
	},
	
	changement_table_calcul: function(){
		this.calcul.split = null;
		this.split_champ_liaison = null;
		this.split_table_liaison = null;
		this.calcul.nom_sql = null;
	},

	chargement_colonne_calcul : async function(){

		if(this.colonne.source_calcul == null)
			return;
		
		var type_element = this.colonne.source_calcul.split('|').at(-1).split('.')[0];

		this.colonne_calcul.type_element = type_element;

		this.colonne_calcul.champs_libres = await $.ajax({
			url : 'eden/champs/valeurs/'+type_element,
			dataType : 'json',
		});
	},

	@if(!empty($liste_libre->id_rapport))
		supprimer_liste : async function(){

			//@todo TRADUCTIONS
			if(!await confirm_eden())
				return false;

			loading(true);

			$.ajax({
				url : '{{ route('parametrage.rapport.supprimer', [$liste_libre->id_rapport]) }}',
				dataType : 'json'
			}).done(function(donnees){

				if(donnees.retour !== true){

					loading(false);
					//@todo TRADUCTIONS
					erreur_toast(donnees.message)
					return;
				}

				window.location = '{{route('parametrage.table_libre.zoom',[$liste_libre->type_element])}}';
			});
		},

		activer_liste : async function(){

			//@todo TRADUCTIONS
			if(!await confirm_eden())
				return false;

			loading(true);

			$.ajax({
				url : '{{ route('parametrage.rapport.activer', [$liste_libre->id_rapport]) }}',
				dataType : 'json'
			}).done(function(donnees){

				if(donnees.retour !== true){
					loading(false);
					//@todo TRADUCTIONS
					erreur_toast(donnees.message)
					return;
				}

				location.reload();
			});
		},
	@endif

@endpush

@push('donnees_pour_vuejs_mounted')

	var vue_contexte = this;

	if(vue_contexte.rapport != undefined){
		if (vue_contexte.rapport.kanban != '' && vue_contexte.rapport.kanban != null)
			vue_contexte.rapport.type = 'kanban';
		else if (vue_contexte.rapport.type == "" || vue_contexte.rapport.type == undefined)
			vue_contexte.rapport.type = 'liste_libre';
	}

	if(vue_contexte.rapport != undefined && vue_contexte.rapport.type != 'liste_libre') {
		vue_contexte.charger_parametres_rapport();
	}

	this.$on('changement_recherche_avancee',(parametres) => {
		if(parametres.informations_complementaires.element_a_filtrer)
			this.$set(parametres.informations_complementaires.element_a_filtrer,'filtres',parametres.recherche_avancee);
	});

	this.$nextTick(() => {
		this.chargement_filtres_couleurs();
	});

@endpush

</script>
@push('scripts')

	<script type="text/javascript">


	$("span.badge.badge-default.js_filtre_sur_liste").on('click', function(e) {

		if($(e.target).hasClass('badge-success')){

			$(e.target).removeClass('badge-success');

		}
		else {

			$(e.target).addClass('badge-success');
		}
	});

	$("[type_filtre=variable]").on('change',function(e){

		if($(e.target)[0].value != ""){

			var ligne = ($(e.target).closest('td').children("input"));

			ligne.each(function() {

				this.value="";
			});
		}

	});

	$("[type_filtre=date_debut] , [type_filtre=date_fin]").on('change',function(e){

		$(e.target).closest('td').children("select").val("");

	});

    $(".sortable").sortable({
		update : function (event, ui) {

			var position = $(ui.originalPosition['top']);
			var position_origine = $(ui.position['top']);
			var item = ui.item[0];
			var id_pour_drop=item.dataset.id_colonne_sortable;
			var ordre_origine = item.dataset.ordre;
			var difference = position[0] - position_origine[0];
			var element = $(this).attr('type_element');
			var tableaux_nouveaux_ordres = {};

			var tableau_sortable = $(this);
			tableau_sortable.sortable("disable");

			if (difference > 0) {

            	var ordre_nouveau = ui.item[0].nextSibling.attributes[0].nodeValue;
            	var nb_element_a_modifier = ordre_origine - ordre_nouveau;
            	var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) + 1;
            	var element_a_modifier = ui.item[0].nextSibling;
            	var id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;

				tableaux_nouveaux_ordres[id_pour_drop] = ordre_nouveau;

            	for (var i = 1; i <= nb_element_a_modifier; i++) {

					tableaux_nouveaux_ordres[id_pour_bouclage] = nouveau_ordre_element_suivant;

            		nouveau_ordre_element_suivant++;
					if(element_a_modifier != null){
						element_a_modifier = element_a_modifier.nextElementSibling;
						if(element_a_modifier != null){
							id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;
						}
					}
            	}
            }

            else{

            	var ordre_nouveau = ui.item[0].previousSibling.attributes[0].nodeValue;
            	var nb_element_a_modifier = ordre_nouveau - ordre_origine;
				var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) - 1;
				var element_a_modifier = ui.item[0].previousSibling;
				var id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;

				tableaux_nouveaux_ordres[id_pour_drop]=ordre_nouveau;

				for (var i = 0; i < nb_element_a_modifier; i++) {

					tableaux_nouveaux_ordres[id_pour_bouclage]=nouveau_ordre_element_suivant;

					nouveau_ordre_element_suivant--;
					if(element_a_modifier != null){
						element_a_modifier = element_a_modifier.previousElementSibling;
						if(element_a_modifier != null){
							id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;
						}
					}
            	}

            }

			$.post({

				url: "{{ URL::to("/eden/parametrage/liste_libre/colonne/changement_ordre") }}",
				dataType: "json",
				data: {
					tableaux_nouveaux_ordres: tableaux_nouveaux_ordres,
					element: element,
				}
			}).done(function(donnees) {

				vue_instance[donnees.type_element]=donnees.elements;
				vue_instance.$forceUpdate();
				tableau_sortable.sortable("enable");
			});

        }
	});


	$('.js_focus_input_recherche').focus();

</script>


@endpush

@push('donnees_pour_vuejs_computed')

    champ_kanban: function() {
        let champ = this.champs_libres.find(champ => champ.nom_sql == this.rapport.kanban );
    
        return champ;
    },

	split_champ_liaison: {
		get(){
			if(!this.calcul.split || !this.calcul.split.includes('.'))
				return '';
			return this.calcul.split?.split('.')[0] ?? null;
		},
		set(valeur){
			var split = this.calcul.split?.split('.') ?? [];

			if(!valeur)
				this.$set(this.calcul,'split', '');
			else 
				this.$set(this.calcul,'split',valeur+'.');
		}	
	},

	split_nom_sql: {
		get(){
			if(!this.calcul.split)
				return '';
			if(!this.calcul.split.includes('.'))
				return this.calcul.split;
			
			return this.calcul.split?.split('.')[1] ?? null;
		},
		set(valeur){

			var split = this.calcul.split?.split('.') ?? [];

			if(split.length < 2){
				this.$set(this.calcul,'split',valeur ?? '');
			} else 
				this.$set(this.calcul,'split',(split[0] ?? '')+'.'+valeur);
		}	
	},

	champs_libres : function(){

		var champs_libres = [];

		Object.values(this.champs_libres_par_type).map(champs => champs_libres = champs_libres.concat(champs));

		return champs_libres;
	},

	nom_sql_pour_select_champs_libres : function(){

		var nom_sql = this.colonne.valeur;

		if(!nom_sql)
			return '';

		if(nom_sql.indexOf('.') > 0)
			return nom_sql.split('.')[1];
		else
			return nom_sql;
		
	},

	type_element_select_champs_libres : function(){

		var nom_sql = this.colonne.valeur;

		if(!nom_sql)
			return null;

		if(nom_sql.indexOf('.') > 0){
			var champ_liaison = nom_sql.split('.')[0];

			if(champ_liaison.includes('|'))
				return champ_liaison.split('|')[1];
			else{
				var champ_libre_liaison = this.champs_libres.find(champ => champ.nom_sql == champ_liaison);
				return champ_libre_liaison.type_element_ajax;
			}
		}else{
			return this.type_element;
		}
	},

	champs_libres_champ : function(){

		return structuredClone(this.champs_libres_pour_select).filter(c => c.champ_liaison == null).map(c => {
			c.champs_libres = c.champs_libres.filter(champ => champ.nom_sql != 'id');
			return c;
		});
		
	},

	champs_libres_input_parametrage : function(){

		var champs_libres_input_parametrage = [];

		for(champs_par_type_element of this.champs_libres_pour_select){

			for(champ of champs_par_type_element.champs_libres){

				var liaison = champs_par_type_element.champ_liaison 
				? ` (${champs_par_type_element.champ_liaison})` 
				: '';

				champs_libres_input_parametrage.push({
					id: champs_par_type_element.champ_liaison != undefined ? '#' + champs_par_type_element.champ_liaison + '.' + champ.nom_sql + '#' : '#' + champ.nom_sql + '#',
					name: this.$root.traduction(champs_par_type_element.index_traduction) + liaison + ' : ' +(champ.nom_sql == 'id' ? 'ID' : this.$root.traduction(champ.index_traduction + '.nom')) + ' (' + champ.nom_sql + ')',
				});
			}

		}

		return champs_libres_input_parametrage;
	},
@endpush

@push('donnees_pour_vuejs_watch')
    'rapport.colonnes_kanban' : function(nouvelle_valeur, ancienne_valeur) {
        if(nouvelle_valeur != ancienne_valeur)
            this.colonnes_kanban = nouvelle_valeur;
    },
    'rapport.kanban' : function() {
        this.colonnes_kanban = [];
        this.rapport.colonnes_kanban = [];
    },
    'split_champ_liaison': function(nouvelle_valeur) {
        if(!nouvelle_valeur) return;

        const infos = Object.values(this.champs_libres_liaison_par_type).find(info => {
            const valeur = info.type == 21 ? info.id_element_dynamique : info.champ_de_liaison;
            return valeur == nouvelle_valeur;
        });
        
        if(infos)
            this.$set(this.calcul, 'type_element_split', infos.type_element);
        else
            this.$set(this.calcul, 'type_element_split', null);

    },
@endpush
