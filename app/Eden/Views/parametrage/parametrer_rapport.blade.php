@extends('eden::templates.template')

@section('title') Gestion rapports paramétrables @stop

@section('content')

	<div id="vue_app" >
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header" style="display: flex; align-items: center; justify-content: space-between">
								<h4>
									Paramétrer le rapport
								</h4>
								<span data-toggle="tooltip" data-placement="left" title="Enregistrer" class="css_ajouter_element ml-auto" @click="enregistrer"><i class="css_action_icon secondaire far fa-save"></i></span>
							</div>
							<div class="card-body">
								<form action="#" method="post" class="css_form" id="formulaire_rapport">

									<div class="row">
										<div class="col-sm-12 css_form_ligne_titre">Paramétrage global</div>
									</div>

									<div class="row">
										<div class="col-sm-2">Catégorie</div>
										<div class="col-sm-4">
											<select name="categorie" v-model="rapport.categorie">
												@foreach($categories as $categorie => $nom)

													<option value="{{$categorie}}">{{ $nom['nom']}}</option>
												@endforeach
											</select>
										</div>
									</div>
                                    <div class="row">
										<div class="col-sm-2">Icone</div>
										<div class="col-sm-4">
											<select name="icone" v-model="rapport.icone">
												<option value="table">Table</option>
												<option value="chart-area">Chart-Area</option>
												<option value="chart-pie">Chart-Pie</option>
												<option value="info">Info</option>
											</select>
										</div>
									</div>
									<div class="row">
										<div class="col-sm-12">
											<traduction-table ref="traduction_table"  categorie="10" :filtrage_index="rapport.index_traduction+'.'"></traduction-table>
										</div>
									</div>
                                    <div class="row">
										<div class="col-sm-2">Ordre</div>
										<div class="col-sm-4"><input required type="text"  v-model="rapport.ordre" name="ordre"  /></div>
									</div>
                                    <div class="row">

										<div class="col-sm-2">Disponible extranet</div>
										<div class="col-sm-4">
											<select id="extranet" name="extranet" v-model="rapport.extranet">
												<option value="0">Non</option>
												<option value="1">Oui</option>
											</select>
										</div>
									</div>
                                    <div class="row" v-show="rapport.type_rapport == 'carte'">
										<div class="col-sm-2">Texte info-bulle</div>
                                        <div class="col-sm-4">
                                            <input-parametrage :type_utilisateur="type_utilisateur" at_custom="#" :vmodel="rapport.parametrage_rapport_libre" name="affichage_info_bulle" :donnees="champs_libres_affichage"></input-parametrage>
                                        </div>
									</div>
                                    <div class="row" v-show="rapport.type_rapport == 'carte'">

										<div class="col-sm-2">@traduction('rapport.divers.positionnement')</div>
										<div class="col-sm-4">
											<select id="positionnement" name="positionnement" v-model="rapport.parametrage_rapport_libre.positionnement">
												<option value="" selected>Centré sur les données</option>
												<option value="point">Point à définir</option>
											</select>
										</div>
                                        <div class="col-sm-2" v-show="rapport.parametrage_rapport_libre.positionnement == 'point'">
                                            @traduction('rapport.divers.zoom')
                                        </div>
                                        <div class="col-sm-4" style="display: flex;align-items: center;gap: 10px;" v-show="rapport.parametrage_rapport_libre.positionnement == 'point'">
											<input type="range" max="20" min="1" step="1" class="form-control" v-model="rapport.parametrage_rapport_libre.zoom" name="zoom" v-show="rapport.type_rapport == 'carte' && rapport.parametrage_rapport_libre.positionnement == 'point'"/>
											<span v-text="rapport.zoom"></span>
                                        </div>
									</div>
                                    <div class="row" v-show="rapport.type_rapport == 'carte' && rapport.parametrage_rapport_libre.positionnement == 'point'">
										<div class="col-sm-2">@traduction('rapport.divers.latitude')</div>
										<div class="col-sm-4">
											<input type="number" id="latitude" name="latitude" v-model="rapport.parametrage_rapport_libre.latitude" placeholder="48.864716" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
										</div>
                                        <div class="col-sm-2">
                                            @traduction('rapport.divers.longitude')
                                        </div>
                                        <div class="col-sm-4">
											<input type="number" id="longitude" name="longitude" v-model="rapport.parametrage_rapport_libre.longitude" placeholder="2.349014" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
                                        </div>
									</div>
									<template v-if="rapport.type_rapport == 'carte'">
										<div class="row">
											<div class="col-sm-2">@traduction('rapport.divers.desactiver_clusterisation')</div>
											<div class="col-sm-4">
												<select id="desactiver_clusterisation" name="desactiver_clusterisation" v-model="rapport.parametrage_rapport_libre.desactiver_clusterisation">
													<option value="0">Non</option>
													<option value="1">Oui</option>
												</select>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2">@traduction('rapport.divers.mappage_latitude')</div>
											<div class="col-sm-4">
												<select-champs-libres 
													:champs_libres="[{ 
															type_element : rapport.type_element,
															index_traduction : 'tables_libres.'+rapport.type_element+'.nom_table',
															champs_libres : champs_mappage_geolocalisation,
														}]"
													:type_element_origine="rapport.type_element"
													:type_element="rapport.type_element"
													:nom_sql="rapport.parametrage_rapport_libre.mappage_latitude"
													@changement_select_champs_libres="$set(rapport.parametrage_rapport_libre,'mappage_latitude',$event.nom_sql);">
												</select-champs-libres>
											</div>
											<div class="col-sm-2">@traduction('rapport.divers.mappage_longitude')</div>
											<div class="col-sm-4">
												<select-champs-libres 
													:champs_libres="[{ 
															type_element : rapport.type_element,
															index_traduction : 'tables_libres.'+rapport.type_element+'.nom_table',
															champs_libres : champs_mappage_geolocalisation,
														}]"
													:type_element_origine="rapport.type_element"
													:type_element="rapport.type_element"
													:nom_sql="rapport.parametrage_rapport_libre.mappage_longitude"
													@changement_select_champs_libres="$set(rapport.parametrage_rapport_libre,'mappage_longitude',$event.nom_sql)">
												</select-champs-libres>
											</div>
										</div>
									</template>
                                    <div class="row" v-show="rapport.type_rapport == 'indicateur'">
										<div class="col-sm-2">Icone dans rapport</div>
                                        <div class="col-sm-4">
                                            <button type="button" class="btn btn-primary iconpicker-component ">
                                                <i class="fas" :class="rapport.icone_dans_rapport"></i>
                                            </button>
                                            <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle menu"
                                                    data-selected="fa-car" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <div class="dropdown-menu"></div>
                                        </div>
										<div class="col-sm-2">Détail rapport</div>
										<div class="col-sm-4">
                                            <select v-model="rapport.lien_rapport" name="lien_rapport">
                                                <option value="">Sans détail</option>
                                                <option value="liste">Liste principale</option>
                                                <option value="rapport">Rapport personnalisé</option>
                                            </select>
                                        </div>
									</div>
                                    <div class="row" v-show="rapport.type_rapport == 'indicateur' && rapport.lien_rapport == 'rapport'">
										<div class="col-sm-2">Rapport détaillé</div>
										<div class="col-sm-4">
                                            <select v-model="rapport.id_rapport_cible" name="id_rapport_cible">
                                                @foreach($categories as $categorie)

                                                    <optgroup label="{{ $categorie['nom'] }}">
                                                        @foreach($categorie['rapports'] as $rapport)
                                                            <option value="{{ $rapport['id_rapport'] }}">{{ $rapport['titre'] }}</option>
                                                        @endforeach
                                                    </optgroup>

                                                @endforeach
                                            </select>
                                        </div>
									</div>

									<!-- rapports Indicateur -->
									<template v-if="rapport.type_rapport == 'indicateur'">
										<div class="row">
											<div class="col-sm-12 css_form_ligne_titre">Paramétrage indicateur</div>
										</div>
										<div class="row">
											<div class="col-sm-2">Format d'affichage</div>
											<div class="col-sm-10">
												<select v-model="rapport.parametrage_rapport_libre.format_affichage">
													<option value="">Aucun</option>
													<option value="montant">Montant</option>
													<option value="montant_lisible">Montant lisibles (K si nécessaire)</option>
													<option value="montant_sans_decimal">Montant sans décimal</option>
												</select>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2">Unité</div>
											<div class="col-sm-10">
												<input type="text" v-model="rapport.parametrage_rapport_libre.unites" />
											</div>
										</div>
										<div class="row">
											<div class="col-md-2">Type de calcul</div>
											<div class="col-md-10">
												<select v-model="rapport.parametrage_rapport_libre.type_calcul">
													<option value="count">Nombre</option>
													<option value="min">Minimum</option>
													<option value="max">Maximum</option>
													<option value="sum">Somme</option>
													<option value="avg">Moyenne</option>
													<option value="sql">SQL</option>
												</select>
											</div>
										</div>

										<div class="row" v-if="rapport.parametrage_rapport_libre.type_calcul === 'sql'">
											<div class="col-md-12">
												Requête SQL<br>
												<i>Attention, la requête doit retourner un élément appelé "resultat". Par exemple SELECT AVG(..) as resultat FROM ...</i><br/>
												<i>De plus si vous souhaitez utiliser les filtres du tableau de bord, vous devez utiliser : #filtre_utilisateurs#, #filtre_entites#, #filtre_dates#<br/>
												Exemple : select count(*) as resultat from client where cree_le like '%2022%' #filtre_utilisateurs# #filtre_entites# #filtre_dates# and cree_par=1
												</i>
											</div>
											<div class="col-md-12">
												<textarea v-model="rapport.parametrage_rapport_libre.sql" name="sql">
												</textarea>
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.type_calcul != null && rapport.parametrage_rapport_libre.type_calcul !== 'sql'">
											<div class="col-md-2">Champ</div>
											<div class="col-md-10">
												<select v-model="rapport.parametrage_rapport_libre.champ_calcul">
													<option :value="champ.nom_sql" v-for="champ in champs_nombre[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
										</div>


										<div class="row" v-for="(filtre, index) in rapport.parametrage_rapport_libre.filtres_rapport" :key="'filtre_rapport_' + index">
											<div class="col-sm-2">Options : filtres sur rapport</div>
											<div class="col-sm-4">
												<select :name="'filtre_rapport_' + index" v-model="rapport.parametrage_rapport_libre.filtres_rapport[index]" @change="$forceUpdate()">
													<option value="">Supprimer</option>
													<option :value="champ.type_element + '.' + champ.nom_sql" v-for="champ in champs_libres_filtre">@{{ champ.nom }} (@{{ champ.type_element + '.' + champ.nom_sql }})</option>
												</select>
											</div>
										</div>
										<div class="row" style="cursor: pointer" @click="ajouter_filtre_rapport">
											<div class="col-sm-2"></div>
											<div class="col-sm-4">
												Ajouter un filtre <span class="fas fa-plus"></span>
											</div>
										</div>

										<!-- les filtres -->
										<template  v-if="rapport.parametrage_rapport_libre.type_calcul != 'sql'">
											<div class="row">
												<div class="col-sm-12 css_form_ligne_titre">Filtres</div>
											</div>
											<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: rapport.type_element, type : 'rapport', id_cible : rapport.id_rapport}" :informations_complementaires="{element_a_filtrer:rapport.parametrage_rapport_libre}"></recherche-avancee>
										</template>

										<!-- les objectifs -->
										<div class="row">
											<div class="col-sm-12 css_form_ligne_titre">Objectifs</div>
										</div>
										<div class="row">
											<div class="col-md-2">Type de calcul</div>
											<div class="col-md-10">
												<select v-model="rapport.parametrage_rapport_libre.type_calcul_objectif">
													<option value="fixe">Valeur fixe</option>
													<option value="count">Nombre</option>
													<option value="min">Minimum</option>
													<option value="max">Maximum</option>
													<option value="sum">Somme</option>
													<option value="avg">Moyenne</option>
													<option value="sql">SQL</option>
												</select>
											</div>
										</div>

										<div class="row" v-if="rapport.parametrage_rapport_libre.type_calcul_objectif === 'sql'">
											<div class="col-md-12">
												Requête SQL<br>
												<i>Attention, la requête doit retourner un élément appelé "resultat". Par exemple SELECT AVG(..) as resultat FROM ...</i><br/>
												<i>De plus si vous souhaitez utiliser les filtres du tableau de bord, vous devez utiliser : #filtre_utilisateurs#, #filtre_entites#, #filtre_dates#<br/>
												Exemple : select count(*) as resultat from client where cree_le like '%2022%' #filtre_utilisateurs# #filtre_entites# #filtre_dates# and cree_par=1
												</i>
											</div>
											<div class="col-md-12">
												<textarea v-model="rapport.parametrage_rapport_libre.sql_objectif" name="sql">
												</textarea>
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.type_calcul_objectif === 'fixe'">
											<div class="col-md-2">Valeur de l'objectif</div>
											<div class="col-md-10">
												<input type="number" v-model="rapport.parametrage_rapport_libre.valeur_objectif">
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.type_calcul_objectif != null && 
											!['fixe', 'sql'].includes(rapport.parametrage_rapport_libre.type_calcul_objectif)">
											<div class="col-md-2">Champ</div>
											<div class="col-md-10">
												<select v-model="rapport.parametrage_rapport_libre.champ_calcul_objectif">
													<option :value="champ.nom_sql" v-for="champ in champs_nombre[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2">Réussi si la valeur est inférieure ?</div>
											<div class="col-sm-10">
												<select v-model="rapport.parametrage_rapport_libre.reussi_si_inferieure">
													<option value="0">Non</option>
													<option value="1">Oui</option>
												</select>
											</div>
										</div>

									</template>

									<!-- rapports PDF -->
									<template v-if="rapport.type_rapport == 'pdf'">
										<div class="row">
											<div class="col-sm-12 css_form_ligne_titre">Paramétrage PDF</div>
										</div>
                                        <div class="row" v-for="(filtre, index) in rapport.parametrage_rapport_libre.filtres_rapport" :key="'filtre_rapport_' + index">
                                            <div class="col-sm-2">Options : filtres sur rapport</div>
                                            <div class="col-sm-4">
                                                <select :name="'filtre_rapport_' + index" v-model="rapport.parametrage_rapport_libre.filtres_rapport[index]">
                                                    <option value="">Supprimer</option>
                                                    <option :value="champ.nom_sql" v-for="champ in champs_libres_filtre">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div
                                                class="row"
                                                style="cursor: pointer"
                                                @click="ajouter_filtre_rapport"
                                        >
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-4">
                                                Ajouter un filtre <span class="fas fa-plus"></span>
                                            </div>
                                        </div>
										<div class="row">
											<div class="col-sm-4">Style css résultats</div>
											<div class="col-sm-8"><textarea v-model="rapport.parametrage_rapport_libre.style_css_resultats"></textarea></div>
										</div>
										<div class="row">
											<div class="col-sm-4">
												Affichage des éléments<br/>

											</div>
											<div class="col-sm-8"><textarea v-model="rapport.parametrage_rapport_libre.affichage_resultats"></textarea></div>
										</div>
										<div class="row">
											<div class="col-sm-12">
												<i style="font-style: italic">
												Utilisation des variables :<br/>
												#nom_sql# => remplace par la valeur du champ, exemple : <span style="color: #f01d1d;">En charge :  #en_charge#</span><br/>
												#nom_sql.nom_sql2# => remplace par la valeur du champ de l'élément lié (type 42), exemple : <span style="color: #f01d1d;">Adresse email :  #client_id.adresse_email#</span><br/>
												#si.nom_sql# => affiche cette chaine que si le champ existe, exemple : <span style="color: #f01d1d;">#si.en_charge#En charge :  #en_charge##si.en_charge#</span><br/>
												</i>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-2"></div>
											<div class="col-sm-12 css_form_ligne_titre">Filtres</div>
										</div>
										<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: rapport.type_element, type : 'rapport', id_cible : rapport.id_rapport}" :informations_complementaires="{element_a_filtrer:rapport.parametrage_rapport_libre}"></recherche-avancee>
									</template>

									<!-- rapports histogramme, courbe, tableau, diagramme circulaire, graphique funnel -->
									<template v-if="['histogramme', 'courbe', 'tableau', 'diagramme_circulaire', 'graphique_funnel', 'carte'].includes(rapport.type_rapport)">

										<div class="row">
											<div class="col-sm-12 css_form_ligne_titre">Paramétrage rapport</div>
										</div>
										<div class="row" v-if="rapport.type_rapport != 'carte' && rapport.type_rapport != 'tableau'">
											<div class="col-sm-2">Légende (nom_sql du champ)</div>
											<div class="col-sm-4">
												<select v-if="rapport.type_rapport == 'diagramme_circulaire' || rapport.type_rapport == 'graphique_funnel'" @change="changement_axe('variable')" name="rapport.parametrage_rapport_libre.variable" v-model="rapport.parametrage_rapport_libre.variable">
													<option :value="champ.nom_sql" v-for="champ in champs_dates_et_liste[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
												<select v-else name="rapport.parametrage_rapport_libre.axe_x" @change="changement_axe('axe_x')" v-model="rapport.parametrage_rapport_libre.axe_x">
													<option :value="champ.nom_sql" v-for="champ in champs_dates_et_liste[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
										</div>
										<div class="row" v-else-if="rapport.type_rapport == 'tableau'">
											<div class="col-sm-2">Axe X</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.axe_x" @change="changement_axe('axe_x')" v-model="rapport.parametrage_rapport_libre.axe_x">
													<option value="serie">Série</option>
													<option :value="champ.nom_sql" v-if="champ.nom_sql != rapport.parametrage_rapport_libre.axe_y" v-for="champ in champs_dates_et_liste[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
											<div class="col-sm-2">
												<span class="badge"
												@click="var tmp = rapport.parametrage_rapport_libre.axe_x;rapport.parametrage_rapport_libre.axe_x = rapport.parametrage_rapport_libre.axe_y ?? 'serie';rapport.parametrage_rapport_libre.axe_y = tmp == 'serie' ? null : tmp;"> <=> </span>
												Axe Y
											</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.axe_y" @change="changement_axe('axe_y')" v-model="rapport.parametrage_rapport_libre.axe_y">
													<option :value=null v-if="rapport.parametrage_rapport_libre.axe_x != 'serie'">Série</option>
													<option :value="champ.nom_sql" v-if="champ.nom_sql != rapport.parametrage_rapport_libre.axe_x" v-for="champ in champs_dates_et_liste[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="rapport.type_rapport == 'histogramme'">
											<div class="col-sm-2">Groupé par</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.groupe_par" v-model="rapport.parametrage_rapport_libre.groupe_par">
													<option value="">Non groupé</option>
													<option :value="champ.nom_sql" v-for="champ in champs_groupe_par">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="rapport.type_rapport == 'histogramme' && legende_champ_date">
											<div class="col-sm-2">Résultats cumulés</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.resultats_cumules" v-model="rapport.parametrage_rapport_libre.resultats_cumules">
													<option value="0">Non</option>
													<option value="1">Oui</option>
												</select>
											</div>
										</div>
                                        <template v-if="rapport.type_rapport == 'carte'">
                                            <div class="row" v-for="(filtre, index) in rapport.parametrage_rapport_libre.types_elements_carte" :key="'types_element_rapport_' + index">
												<div class="col-sm-2"><span v-if="index == 0">Types éléments sur carte</span></div>
                                                <div class="col-sm-4">
                                                    <select :name="'types_elements_carte_' + index" v-model="rapport.parametrage_rapport_libre.types_elements_carte[index]" @change="$forceUpdate()">
                                                        <option value="">Supprimer</option>
                                                        <option :value="type_element" v-for="type_element in types_elements_carte">@{{ type_element }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div
                                                    class="row"
                                                    style="cursor: pointer"
                                                    @click="ajouter_type_element_rapport"
                                            >
                                                <div class="col-sm-2"></div>
                                                <div class="col-sm-4">
                                                    Ajouter un type élément <span class="fas fa-plus"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="row" v-for="(filtre, index) in rapport.parametrage_rapport_libre.filtres_rapport" :key="'filtre_rapport_' + index">
                                            <div class="col-sm-2">Options : filtres sur rapport</div>
                                            <div class="col-sm-4">
                                                <select :name="'filtre_rapport_' + index" v-model="rapport.parametrage_rapport_libre.filtres_rapport[index]">
                                                    <option value="">Sans valeur</option>
                                                    <option :value="champ.type_element + '.' + champ.nom_sql" v-for="champ in champs_libres_filtre">@{{ champ.nom }} (@{{ champ.type_element + '.' + champ.nom_sql }})</option>
                                                </select>
                                            </div>
											<span aria-hidden="true"><i style="width: 30px;height: 30px;line-height: 30px;" class="fas fa-times" @click="supprimer_filtre_rapport(index)"></i></span>
											<template v-if="filtre_champ_date[index] && !legende_champ_date">
												<div class="col-sm-2">Afficher N-1 sur ce filtre</div>
												<div class="col-sm-1">
													<input :name="'filtre_rapport_n_moins_1_' + index"
														type="checkbox"
														:checked="rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1 && rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1.nom == filtre" 
														@change="appliquer_n_moins_1_sur_filtre(index, filtre)"/>
												</div>
											</template>
                                        </div>
                                        <div
                                                class="row"
                                                style="cursor: pointer"
                                                @click="ajouter_filtre_rapport"
                                        >
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-4">
                                                Ajouter un filtre <span class="fas fa-plus"></span>
                                            </div>
                                        </div>
										<div class="row" v-if="(rapport.type_rapport == 'histogramme' || rapport.type_rapport =='tableau') && legende_champ_date">
											<div class="col-sm-2">Options : Périodicité</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.periodicite" v-model="rapport.parametrage_rapport_libre.periodicite">
													<option value="">Aucune</option>
													<option v-for="periodicite in periodicites" :value="periodicite">@{{traduction('rapport.divers.' + periodicite)}}</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="rapport.type_rapport == 'histogramme' && legende_champ_date">
											<div class="col-sm-2">Afficher N-1</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.afficher_n_moins_1" v-model="rapport.parametrage_rapport_libre.afficher_n_moins_1">
													<option value="0">Non</option>
													<option value="1">Oui</option>
												</select>
											</div>
											<template v-if="rapport.parametrage_rapport_libre.afficher_n_moins_1 == 1">
												<div class="col-sm-2">Périodicité N-1</div>
												<div class="col-sm-4">
													<select name="rapport.parametrage_rapport_libre.periodicite_n_moins_1" v-model="rapport.parametrage_rapport_libre.periodicite_n_moins_1">
														<option value=''>Périodicité du rapport</option>
														<option v-for="periodicite in periodicites_n_moins_1" :value="periodicite">@{{traduction('rapport.divers.' + periodicite)}}</option>
													</select>
												</div>
											</template>	
										</div>
										<div class="row" v-if="rapport.type_rapport == 'tableau'">
											<div class="col-sm-2">Options : export excel</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.export_excel" v-model="rapport.parametrage_rapport_libre.export_excel">
													<option value="0">Désactiver l'export excel</option>
													<option value="1">Activer l'export excel</option>

												</select>
											</div>
										</div>
                                        <div class="row" v-if="rapport.type_rapport == 'tableau'">
                                            <div class="col-sm-2">Cacher les colonnes vides</div>
                                            <div class="col-sm-4">
                                                <select name="rapport.parametrage_rapport_libre.cacher_colonnes_vides" v-model="rapport.parametrage_rapport_libre.cacher_colonnes_vides">
                                                    <option value="0">Non</option>
                                                    <option value="1">Oui</option>
                                                </select>
                                            </div>
                                        </div>
										<div class="row" v-if="rapport.type_rapport == 'histogramme'">
											<div class="col-sm-2">Afficher légende vide</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.afficher_sans_valeur" v-model="rapport.parametrage_rapport_libre.afficher_sans_valeur">
													<option value="0">Non</option>
													<option value="1">Oui</option>

												</select>
											</div>
										</div>
										<div class="row" v-if="['histogramme', 'courbe'].includes(rapport.type_rapport)">
											<div class="col-sm-2">Afficher une ligne d'objectif</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.afficher_objectif" v-model="rapport.parametrage_rapport_libre.afficher_objectif">
													<option value="0">Non</option>
													<option value="1">Oui</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.afficher_objectif == 1">
											<div class="col-sm-2">Nom de l'objectif</div>
											<div class="col-sm-4">
												<input type="text" name="rapport.parametrage_rapport_libre.texte_objectif" v-model="rapport.parametrage_rapport_libre.texte_objectif" />
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.afficher_objectif == 1">
											<div class="col-sm-2">Valeur de l'objectif</div>
											<div class="col-sm-4">
												<input type="number" name="rapport.parametrage_rapport_libre.valeur_objectif" v-model="rapport.parametrage_rapport_libre.valeur_objectif" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.afficher_objectif == 1">
											<div class="col-sm-2">Affichage de la ligne d'objectif</div>
											<div class="col-sm-4">
												<select name="rapport.parametrage_rapport_libre.affichage_ligne_objectif" v-model="rapport.parametrage_rapport_libre.affichage_ligne_objectif">
													<option value="Dash">Tirets</option>
													<option value="DashDot">Points & Tirets</option>
													<option value="Dot">Points</option>
													<option value="LongDash">Tirets longs</option>
													<option value="LongDashDot">Points & Tirets longs</option>
													<option value="LongDashDotDot">Double Points & Tirets longs</option>
													<option value="ShortDash">Tirets courts</option>
													<option value="ShortDashDot">Points & Tirets courts</option>
													<option value="ShortDashDotDot">Double Points & Tirets courts</option>
													<option value="ShortDot">Points serrés</option>
													<option value="Solid">Plein</option>
												</select>
											</div>
										</div>
										<div class="row" v-if="rapport.parametrage_rapport_libre.afficher_objectif == 1">
											<div class="col-sm-2">Couleur de la ligne d'objectif</div>
											<div class="col-sm-4">
												<input type="color" name="rapport.parametrage_rapport_libre.couleur_ligne_objectif" v-model="rapport.parametrage_rapport_libre.couleur_ligne_objectif" />
											</div>
										</div>
										<div class="row" v-if="['diagramme_circulaire', 'graphique_funnel', 'histogramme', 'courbe'].includes(rapport.type_rapport)">
											<div class="col-sm-2">Nombres de décimales affichées</div>
											<div class="col-sm-4">
												<input type="number" name="rapport.parametrage_rapport_libre.nombre_decimales_recap" v-model="rapport.parametrage_rapport_libre.nombre_decimales_recap" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</div>
										</div>
										<template v-if="rapport.type_rapport == 'diagramme_circulaire' || rapport.type_rapport == 'graphique_funnel' || 
											(rapport.type_rapport == 'tableau' && rapport.parametrage_rapport_libre.axe_y != null && rapport.parametrage_rapport_libre.axe_x != 'serie')">
											<div class="row" v-if="rapport.parametrage_rapport_libre.serie.index_traduction === undefined">
												<div class="col-sm-2">Nom de la variable</div>
												<div class="col-sm-4">
													<input type="text" v-model="rapport.parametrage_rapport_libre.serie.nom" />
												</div>
											</div>
											<div class="row" v-else>
												<div class="col-sm-12">
													<traduction-table  categorie="10" :filtrage_index="rapport.parametrage_rapport_libre.serie.index_traduction+'.'"></traduction-table>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-2">Type de calcul</div>
												<div class="col-sm-4">
													<select name="serie.type_calcul" v-model="rapport.parametrage_rapport_libre.serie.type_calcul">
														<option value="count">Nombre</option>
														<option value="sum">Somme</option>
														<option value="avg">Moyenne</option>
													</select>
												</div>
											</div>
											<div class="row" v-show="rapport.parametrage_rapport_libre.serie.type_calcul != 'count'" >
												<div class="col-sm-2">Champ pour le calcul</div>
												<div class="col-sm-4">
													<select name="serie.champ_calcul" v-model="rapport.parametrage_rapport_libre.serie.champ_calcul">
														<option :value="champ.nom_sql" v-for="champ in champs_nombre[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
													</select>
												</div>
											</div>
											<div class="row">
												<div class="col-sm-2"></div>
												<div class="col-sm-12 css_form_ligne_titre">Filtres</div>
											</div>
											<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: rapport.type_element, type : 'rapport', id_cible : rapport.id_rapport}" :informations_complementaires="{element_a_filtrer:rapport.parametrage_rapport_libre.serie}"></recherche-avancee>
										</template>

                                        <template v-else-if="rapport.type_rapport == 'carte'">
											<div v-for="type_element in ['adresse'].concat(rapport.parametrage_rapport_libre.types_elements_carte ?? [])" :key="type_element">
												<div class="row">
													<div class="col-sm-12 css_form_ligne_titre">
														Filtres @{{ type_element }}
													</div>
												</div>
												<div class="row">
													<div class="col-sm-12">
														<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: type_element, type : rapport.id_rapport+'.type_element', id_cible : type_element}" :informations_complementaires="{element_a_filtrer:rapport.parametrage_rapport_libre,champ:'filtres_'+type_element}"></recherche-avancee>
													</div>
												</div>
											</div>
                                            <div class="row" v-if="rapport.parametrage_rapport_libre.types_elements_carte && rapport.parametrage_rapport_libre.types_elements_carte.length > 0">
                                                <div class="col-sm-12 css_form_ligne_titre">
													Couleurs marqueurs
													<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouvelle couleur" @click="ajout_couleur">
														<i class="fa fa-fw fa-plus-square" aria-hidden="true"></i>
													</span>
												</div>
                                            </div>
											<div v-for="(couleur, index) in rapport.parametrage_rapport_libre.couleurs" :key="couleur.id">
												<div class="row">
													<div class="col-sm-12 css_form_ligne_titre">
														Couleur @{{index+1}}
														<span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Supprimer couleur" @click="rapport.parametrage_rapport_libre.couleurs.splice(index,1)">
															<i class="fa fa-fw fa-trash" aria-hidden="true"></i>
														</span>
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2">
														Couleur
													</div>
													<div class="col-sm-4">
														<input type="color" v-model="couleur.couleur" name="couleur">
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2">
														Légende
													</div>
													<div class="col-sm-4">
														<input type="text" v-model="couleur.legende" name="legende">
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2">
														Type élément
													</div>
													<div class="col-sm-4">
														<select v-model.lazy="couleur.type_element" name="type_element" @click="changement_type_element_couleur(index)">
															<option v-for="type_element in (rapport.parametrage_rapport_libre.types_elements_carte ?? [])" :value="type_element">
																@{{ type_element }}
															</option>
														</select>
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2">
														Filtres
													</div>
												</div>
												<div class="row">
													<div class="col-sm-12">
														<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :chargement_externe="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: couleur.type_element, type : rapport.id_rapport+'.couleur', id_cible : couleur.id}" :informations_complementaires="{element_a_filtrer:couleur}"></recherche-avancee>
													</div>
												</div>
                                            </div>
                                        </template>
										<template v-else>
											<div v-for="(serie, index_serie) in rapport.parametrage_rapport_libre.series" :key="serie.id">
												<template v-if="serie.index_traduction === undefined">
													<div class="row">
														<div class="col-sm-12 css_form_ligne_titre">
															@{{ serie.nom }}
															<span class="css__lien" @click="supprimer_serie(index_serie)">
																Supprimer cette série
															</span>
														</div>
													</div>
													<div class="row">
														<div class="col-sm-2">Nom de la série</div>
														<div class="col-sm-4"><input type="text" v-model="serie.nom" /></div>
													</div>
												</template>
												<template v-else>
													<div class="row">
														<div class="col-sm-12 css_form_ligne_titre">
															@{{ traduction(serie.index_traduction,'nom') }}
															<span class="css__lien" @click="supprimer_serie(index_serie)">
																Supprimer cette série
															</span>
														</div>
													</div>
													<div class="row">
														<div class="col-sm-12">
															<traduction-table :key="serie.index_traduction"  categorie="10" :filtrage_index="serie.index_traduction+'.'"></traduction-table>
														</div>
													</div>
												</template>
												<div class="row" v-if="!legende_champ_date">
													<div class="col-sm-2">Afficher N-1 sur le filtre de cette série</div>
													<div class="col-sm-4">
														<input :name="'filtre_applique_rapport_n_moins_1_' + index_serie"
															type="checkbox"
															:checked="!(rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1) && rapport.parametrage_rapport_libre.filtre_applique_rapport_n_moins_1 == serie.id" 
															@change="appliquer_n_moins_1_sur_filtre_applique(serie.id)"/>
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2">Type de calcul</div>
													<div class="col-sm-4">
														<select name="serie.type_calcul" v-model="serie.type_calcul" @change="$forceUpdate()">
															<option value="count">Nombre</option>
															<option value="sum">Somme</option>
															<option value="avg">Moyenne</option>
														</select>
													</div>
												</div>
												<div class="row" v-show="serie.type_calcul != 'count'">
													<div class="col-sm-2">Champ pour le calcul</div>
													<div class="col-sm-4">
														<select name="serie.champ_calcul" v-model="serie.champ_calcul">
															<option :value="champ.nom_sql" v-for="champ in champs_nombre[rapport.type_element]">@{{ champ.nom }} (@{{ champ.nom_sql }})</option>
														</select>
													</div>
												</div>
												<div class="row">
													<div class="col-sm-2"></div>
													<div class="col-sm-12 css_form_ligne_titre" v-if="serie.index_traduction === undefined" >@{{ serie.nom }} : filtres</div>
													<div class="col-sm-12 css_form_ligne_titre" v-else>@{{ traduction(serie.index_traduction,'nom') }} : filtres</div>
												</div>
												<recherche-avancee ref="recherche_avancee" :bloc_unitaire="true" :chargement_externe="true" :enregistrement_desactive="true" :parametres_recherche_avancee="{type_element: rapport.type_element, type : rapport.id_rapport+'.serie', id_cible : serie.id}" :informations_complementaires="{element_a_filtrer:serie}"></recherche-avancee>
											</div>
											<span class="btn btn-primary" @click="ajouter_serie()">Ajouter une série</span>
										</template>
									</template>
								</form>
								<a href="{{ route('parametrage.rapport.modifier', ['id_rapport' => $rapport_libre->id_rapport]) }}" type="button" class="btn btn-primary">Retour</a>
								<button type="button" class="btn btn-primary float-right" @click="enregistrer">Enregistrer</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

{{-- <script> --}}

@push('donnees_pour_vuejs_data')
	rapport: {!! $rapport_libre !!},
    types_elements_carte: {!! collect($types_elements_carte) !!},
	champs_dates_et_liste: {!! $champs_dates_et_liste !!},
	champs_dates: {!! $champs_dates !!},
	champs_nombre: {!! $champs_nombre !!},
    champs_libres: {!! $champs_libres !!},
	champs_libres_element : {},
    champs_libres_elements_carte: {!! $champs_libres_elements_carte !!},
    types_elements_fiche: {!! collect($types_elements_fiche) !!},
    type_utilisateur: {{ moi()->type_utilisateur }},
    nouvelle_couleur: {},
	periodicites: ["quotidienne","hebdomadaire","mensuelle","trimestrielle","semestrielle","annuelle"],
	champs_mappage_geolocalisation: {!! $champs_mappage_geolocalisation !!},
@endpush

@push('donnees_pour_vuejs_computed')

	legende_champ_date : function(){

		var vue_instance = this;

		for(const [key, champ] of Object.entries(vue_instance.champs_dates_et_liste[vue_instance.rapport.type_element])){

			if((champ.nom_sql == vue_instance.rapport.parametrage_rapport_libre.axe_x || champ.nom_sql == vue_instance.rapport.parametrage_rapport_libre.axe_y || champ.nom_sql == vue_instance.rapport.parametrage_rapport_libre.variable) && [4,5].some(e => e == champ.type))
				return true;

		}

		return false;

	},

	champs_groupe_par : function(){

		var vue_instance = this;

		var champs_a_retourne = [];

		for(const [key, champ] of Object.entries(vue_instance.champs_dates_et_liste[vue_instance.rapport.type_element])){

			if(champ.nom_sql != vue_instance.rapport.parametrage_rapport_libre.axe_x && champ.nom_sql != vue_instance.rapport.parametrage_rapport_libre.variable)
				champs_a_retourne.push(champ);

		}

		return champs_a_retourne;

	},

    champs_libres_filtre : function() {

        var champs_a_retourner = this.champs_libres;

        if(this.rapport.type_rapport != 'carte')
            return champs_a_retourner;

        champs_a_retourner = [];

        for(const [type_element, champs] of Object.entries(this.champs_libres_elements_carte)){

            if(type_element != 'adresse' && (!this.rapport.parametrage_rapport_libre.types_elements_carte || !this.rapport.parametrage_rapport_libre.types_elements_carte.includes(type_element)))
                continue;

            champs_a_retourner = champs_a_retourner.concat(champs);

        }

        return champs_a_retourner;

    },

    champs_libres_affichage : function() {

        var champs_a_retourner = this.champs_libres.map((x) => {
            return {
                name : `${x.type_element} : ${x.nom} (${x.nom_sql})`,
                id: `#${x.type_element}.${x.nom_sql}#`,
            }
        });

        if(this.rapport.type_rapport != 'carte')
            return champs_a_retourner;

        for(const [type_element, champs] of Object.entries(this.champs_libres_elements_carte)){

            if(!this.rapport.parametrage_rapport_libre.types_elements_carte || !this.rapport.parametrage_rapport_libre.types_elements_carte.includes(type_element))
                continue;

            champs_a_retourner = champs_a_retourner.concat(
                champs.map((x) => {
                    return {
                        name : `${x.type_element} : ${x.nom} (${x.nom_sql})`,
                        id: `#${x.type_element}.${x.nom_sql}#`,
                    }
                })
            );

        }

        return champs_a_retourner;

    },

	filtre_champ_date : function(){
		const resultat = [];

        this.rapport.parametrage_rapport_libre.filtres_rapport.forEach((filtre, index) => {

            if(!filtre) {
                resultat[index] = false;
                return;
            }

            resultat[index] = this.champ_date_pour_filtre(filtre);
        });

        return resultat;
	},

	periodicites_n_moins_1 : function(){
		const periodicite = this.rapport.parametrage_rapport_libre.periodicite || 'mensuelle';
    
    	const index_actuel = this.periodicites.indexOf(periodicite);
    
    	return this.periodicites.filter((p, index) => index >= index_actuel);
	},

@endpush

@push('donnees_pour_vuejs_methods')

	effacer_filtre_serie: function(serie, nom_sql) {

		reset = false

		if(typeof serie['filtre_applique_'+nom_sql] == 'string') {
			serie['filtre_applique_'+nom_sql]='';
			reset = true;
		}

		if(typeof serie['filtre_applique_'+nom_sql] != 'undefined' && typeof serie['filtre_applique_'+nom_sql].variable != 'undefined') {
			serie['filtre_applique_'+nom_sql].variable='';
			reset = true;
		}

		if(typeof serie['filtre_applique_'+nom_sql] != 'undefined' && typeof serie['filtre_applique_'+nom_sql].texte != 'undefined') {
			serie['filtre_applique_'+nom_sql].texte='';
			reset = true;
		}

		if(!reset) {
			serie['filtre_applique_'+nom_sql] = [];
		}
	},

	effacer_filtre: function(nom_sql) {

		reset = false

		if(typeof vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql] == 'string') {
			vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql]='';
			reset = true;
		}

		if(typeof vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql] !== 'undefined' && typeof vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql].variable != 'undefined') {
			vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql].variable='';
			reset = true;
		}

		if(typeof vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql] !== 'undefined' && typeof vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql].texte != 'undefined') {
			vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql].texte='';
			reset = true;
		}

		if(!reset)
			vue_instance.rapport.parametrage_rapport_libre['filtre_applique_'+nom_sql] = [];
	},

	supprimer_filtre_rapport: function(index){
		if(this.rapport.parametrage_rapport_libre.filtres_rapport[index] == this.rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1)
			this.$set(this.rapport.parametrage_rapport_libre,'filtres_rapport_afficher_n_moins_1',null);
		
		this.rapport.parametrage_rapport_libre.filtres_rapport.splice(index, 1);
	},

	appliquer_n_moins_1_sur_filtre: function(index, filtre){
		this.$set(this.rapport.parametrage_rapport_libre,'filtres_rapport_afficher_n_moins_1',(this.rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1 && this.rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1.nom == filtre ? null : { id: index + 1, nom: filtre }));
		this.$set(this.rapport.parametrage_rapport_libre,'filtre_applique_rapport_n_moins_1',null);
	},

	appliquer_n_moins_1_sur_filtre_applique: function(id_serie){
		this.$set(this.rapport.parametrage_rapport_libre,'filtre_applique_rapport_n_moins_1',(this.rapport.parametrage_rapport_libre.filtre_applique_rapport_n_moins_1 && this.rapport.parametrage_rapport_libre.filtre_applique_rapport_n_moins_1 == id_serie ? null : id_serie));
		this.$set(this.rapport.parametrage_rapport_libre,'filtres_rapport_afficher_n_moins_1',null);
	},

	champ_date_pour_filtre : function(filtre){

		const [type_element_filtre, nom_sql_filtre] = filtre.split('.');

        const champs_dates = this.champs_dates[type_element_filtre] ?? [];

        resultat = Object.values(champs_dates).some(champ_date =>
            champ_date.nom_sql === nom_sql_filtre &&
            [4, 5].includes(champ_date.type)
        );

		return resultat;
	},


	ajouter_serie: function() {

		var new_key = Object.keys(this.rapport.parametrage_rapport_libre.series).length;

		temp = {!! $serie_vide !!};

		temp.id = Object.values(this.rapport.parametrage_rapport_libre.series).length == 0 ? 1 : (Math.max(...Object.values(this.rapport.parametrage_rapport_libre.series).map(serie => serie.id)) + 1);

		if(typeof this.rapport.parametrage_rapport_libre.series[new_key] != 'undefined' && typeof this.rapport.parametrage_rapport_libre.series == 'object') {

			this.rapport.parametrage_rapport_libre.series = Object.values(this.rapport.parametrage_rapport_libre.series);
			this.rapport.parametrage_rapport_libre.series.push(temp);

		} else {

			this.rapport.parametrage_rapport_libre.series[new_key] = temp;
		}

		this.$forceUpdate();

		this.$nextTick(() => {
			this.$refs.recherche_avancee[(this.$refs.recherche_avancee.length-1)].chargement_initial({
				champs_libres:this.champs_libres_element
			});
		});
	},

	supprimer_serie: function(index_serie) {
		this.rapport.parametrage_rapport_libre.series.splice(index_serie, 1);
		this.$forceUpdate();
	},

	inverse_valeur_filtre_creation_rapport(modele, id) {

		if(modele.indexOf(id) >= 0) {

			modele.splice(modele.indexOf(id), 1);
		}
		else {

			modele.push(id);
		}

	},

	enregistrer: function() {

		loading(true);
		var vue_contexte = this;

		var rapport = structuredClone(this.rapport);

		// on enregistre les infos du champ libre
		$.post({

			url: 'eden/parametrage/rapport/enregistrer_parametrage',
			dataType: "json",
			data: rapport,
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				loading(false);
				await erreur(donnees.retour);
				return;
			}

			if(vue_contexte.rapport.type_rapport == 'liste_libre' && donnees.hasOwnProperty('id')) {

				window.location.href = "{{URL::to('/eden/parametrage/liste_libre')}}"+'/'+donnees.id;
				return;
			}
			else {

				window.location.href = "{{URL::to('/eden/rapport')}}"+'/'+donnees.id_rapport;
				return;
			}
		});

	},

	changement_axe : function(data){

		var vue_composant = this;

		if(vue_composant.legende_champ_date === true){
			vue_composant.rapport.parametrage_rapport_libre.afficher_n_moins_1 = 0;
			vue_composant.rapport.parametrage_rapport_libre.filtres_rapport_afficher_n_moins_1 = null;
			vue_composant.rapport.parametrage_rapport_libre.filtre_applique_rapport_n_moins_1 = null;
			vue_composant.rapport.parametrage_rapport_libre.periodicite_n_moins_1 = '';
		}
		else{
			vue_composant.rapport.parametrage_rapport_libre.periodicite = 0;
			vue_composant.rapport.parametrage_rapport_libre.resultats_cumules = 0;
			vue_composant.rapport.parametrage_rapport_libre.afficher_n_moins_1 = 0;
			vue_composant.rapport.parametrage_rapport_libre.periodicite_n_moins_1 = '';
		}

		if(this.rapport.parametrage_rapport_libre.axe_x != 'serie' && this.rapport.parametrage_rapport_libre.axe_y != 'serie' && this.rapport.parametrage_rapport_libre.axe_y != null){

			if(!this.rapport.parametrage_rapport_libre.serie)
				this.$set(this.rapport.parametrage_rapport_libre,'serie',{
					nom : '',
					type_calcul : 'count',
					champ_calcul : null,
					filtres : [],
				});

			if(this.rapport.parametrage_rapport_libre.series.length > 0)
				this.$set(this.rapport.parametrage_rapport_libre,'series',[]);
		}
		else if(this.rapport.parametrage_rapport_libre.serie)
			this.$set(this.rapport.parametrage_rapport_libre,'serie',null);

	},

    ajouter_filtre_rapport() {

        if(this.rapport.parametrage_rapport_libre.filtres_rapport){

            if(!Array.isArray(this.rapport.parametrage_rapport_libre.filtres_rapport))
                this.rapport.parametrage_rapport_libre.filtres_rapport = Object.values(this.rapport.parametrage_rapport_libre.filtres_rapport)
    
            this.rapport.parametrage_rapport_libre.filtres_rapport.push('');
    
        } else
            this.rapport.parametrage_rapport_libre.filtres_rapport = [''];

        this.$forceUpdate();
    },

    ajouter_type_element_rapport() {
        if(this.rapport.parametrage_rapport_libre.types_elements_carte)
            this.rapport.parametrage_rapport_libre.types_elements_carte.push('');
        else
            this.rapport.parametrage_rapport_libre.types_elements_carte = [''];

        this.$forceUpdate();
    },

    ajout_couleur() {

        if(!this.rapport.parametrage_rapport_libre.couleurs)
			this.$set(this.rapport.parametrage_rapport_libre,'couleurs',[]);

		this.rapport.parametrage_rapport_libre.couleurs.push({
			id : Object.values(this.rapport.parametrage_rapport_libre.couleurs).length == 0 ? 1 : (Math.max(...Object.values(this.rapport.parametrage_rapport_libre.couleurs).map(couleur => couleur.id)) + 1),
			couleur : null,
			legende : '',
			type_element:this.rapport.parametrage_rapport_libre.types_elements_carte[0]
		});

		this.$forceUpdate();

		this.$nextTick(() => {
			this.$refs.recherche_avancee[(this.$refs.recherche_avancee.length-1)].chargement_initial();
		});
    },

	changement_type_element_couleur : function(index){

		this.$refs.recherche_avancee[index].chargement_initial();
	},

    supprimer_couleur(index) {
        this.rapport.parametrage_rapport_libre.couleurs.splice(index, 1);
        this.$forceUpdate();
    },

	chargement_valeur_filtres : function(type,type_element){

		$.post({
			url : '{{route('base_eden.recherche_avancee.donnees_initialisation')}}',
			data : {
				type_element : type_element,
				type : this.rapport.id_rapport+'.'+type,
			},
			dataType: 'json'
		}).done((donnees) => {

			this.champs_libres_element = donnees.champs_libres;

			if(!this.$refs.recherche_avancee)
				return;

			for(ref_recherche_avancee of this.$refs.recherche_avancee){

				if(ref_recherche_avancee.parametres_recherche_avancee.type != this.rapport.id_rapport+'.'+type
					|| ref_recherche_avancee.parametres_recherche_avancee.type_element != type_element)
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

@endpush

@push('donnees_pour_vuejs_mounted')
    $('.icp-dd').iconpicker();

    $('.icp').on('iconpickerSelected', function (e) {
        vue_instance.rapport.icone_dans_rapport = e.iconpickerValue;
    });

	this.$on('changement_recherche_avancee',(parametres) => {
		if(parametres.informations_complementaires.element_a_filtrer)
			this.$set(parametres.informations_complementaires.element_a_filtrer,parametres.informations_complementaires.champ ?? 'filtres',parametres.recherche_avancee);
	});

	this.$nextTick(() => {

		if(['courbe','histogramme','tableau'].includes(this.rapport.type_rapport))
			this.chargement_valeur_filtres('serie',this.rapport.type_element);
		else if(this.rapport.type_rapport == 'carte' && this.rapport.parametrage_rapport_libre.couleurs){

			var types_elements = this.rapport.parametrage_rapport_libre.couleurs
				.map(couleur => couleur.type_element).filter((valeur, index, tableau) => {
					return tableau.indexOf(valeur) === index;
				});

			for(type_element of types_elements){
				this.chargement_valeur_filtres('couleur',type_element);
			}
		}

	});

@endpush
