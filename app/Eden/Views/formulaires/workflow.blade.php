<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
</div>
<div class="row">
	@champ('workflow','nom', 2,10)
</div>
<div class="row">
	<div class="col-sm-2">{!! management('workflow')->champ('type_element')->nom() !!}</div>
	<div class="col-sm-10">
		<select name="type_element" v-model="workflow.type_element">
			@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
				<option value="{{ $type_element_tmp }}">{{ $type_element_tmp }}</option>
			@endforeach
		</select>
	</div>
</div>
<div class="row">
	@champ('workflow','type_action', 2,10)
</div>
<div class="row">
	@champ('workflow','trigger', 2,10)
</div>

<?php temps_execution('debut formulaire workflow'); ?>

<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.workflow.filtre_element')</div>
</div>
<div class="row">
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.workflow.champ')</div>
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.workflow.valeur_avant')</div>
	<div class="col-sm-4 css_form_ligne_titre">@traduction('formulaire.workflow.valeur_apres')</div>
</div>

@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
	@foreach(App\Eden\Models\Champ_libre::orderBy('nom')->where('type_element', $type_element_tmp)->whereNotIn('type', array(-1,-2, 7, 42, 9, 13))->get() as $champ_libre)
		<div class="row" v-if="workflow.type_element == '{{$type_element_tmp}}'">
			<div class="col-md-4">{{ $champ_libre->nom }}</div>
			<div class="col-md-4">{!! management($type_element_tmp)->champ($champ_libre->nom_sql)->cree_pour_workflow('filtre_avant') !!}</div>
			<div class="col-md-4">{!! management($type_element_tmp)->champ($champ_libre->nom_sql)->cree_pour_workflow('filtre_apres') !!}</div>
		</div>
	@endforeach
@endforeach

<?php temps_execution('filtres'); ?>

<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.workflow.parametrage_action')</div>
</div>

<!-- ENVOYER UN MAIL -->
<template v-if="workflow.type_action == 1">
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.destinataire') 1</div>
		<div class="col-sm-5">
			<select name="parametrage[type_destinataire_1]" v-model="workflow.parametrage.type_destinataire_1">
				<option value="saisie_libre">@traduction('formulaire.workflow.saisie_libre')</option>
				<option value="utilisateur_connecte">@traduction('formulaire.workflow.utilisateur_connecte')</option>
				<option value="mail_associe">@traduction('formulaire.workflow.mail_associe')</option>
			</select>
		</div>
		<div class="col-sm-5" v-show="workflow.parametrage.type_destinataire_1 == 'mail_associe'">
			<input type="text" name="parametrage[destinataire_1]" placeholder="client_id.adresse_email" v-model="workflow.parametrage.destinataire_1">
		</div>
		<div class="col-sm-5" v-show="workflow.parametrage.type_destinataire_1 == 'saisie_libre'">
			<input type="text" name="parametrage[destinataire_1]" placeholder="john.doe@acme.com" v-model="workflow.parametrage.destinataire_1" />
		</div>
	</div>
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.destinataire') 2</div>
		<div class="col-sm-10"><input type="text" name="parametrage[destinataire_2]" placeholder="john.doe@acme.com" v-model="workflow.parametrage.destinataire_3" /></div>
	</div>
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.destinataire') 3</div>
		<div class="col-sm-10"><input type="text" name="parametrage[destinataire_2]" placeholder="john.doe@acme.com" v-model="workflow.parametrage.destinataire_3" /></div>
	</div>
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.modele_de_mail')</div>
		<div class="col-sm-10">
			<select name="parametrage[modele_email]" v-model="workflow.parametrage.modele_email">
				@foreach(modele('modele_email')->get() as $modele_email_tmp)
					<option value="{{ $modele_email_tmp->id }}">{{ $modele_email_tmp->nom }}</option>
				@endforeach
			</select>
		</div>
	</div>
</template>

<!-- MODIFIER UN ELEMENT -->
<template v-if="workflow.type_action == 2">
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.element')</div>
		<div class="col-sm-10">
			<select name="parametrage[type_element]" v-model="workflow.parametrage.type_element">
				<option value="lui_meme">@traduction('formulaire.workflow.element_lui_meme')</option>
				@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
					@foreach(App\Eden\Models\Champ_libre::orderBy('nom')->where('type_element', $type_element_tmp)->where('type', 42)->get() as $champ_libre)
						<option value="{{ $champ_libre->nom_sql }}" v-show="workflow.type_element == '{{$type_element_tmp}}'">{{ $champ_libre->nom }}</option>
					@endforeach
				@endforeach
			</select>
		</div>
	</div>
	
	<div class="row">
		<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.workflow.donnees')</div>
	</div>
	<div class="row">
		<div class="col-sm-12"><div class="alert alert-warning">@traduction('formulaire.workflow.vous_pouvez_utiliser_des_variables')</div></div>
	</div>
	@for($i=1; $i<=3; $i++)
		<div class="row">
			<div class="col-sm-2">@traduction('formulaire.workflow.modification') #{{$i}}</div>
			<div class="col-sm-4"><input type="text" name="parametrage[modification_{{$i}}]" v-model="workflow.parametrage.modification_{{$i}}" /></div>
			<div class="col-sm-4"><input type="text" name="parametrage[valeur_{{$i}}]" v-model="workflow.parametrage.valeur_{{$i}}" /></div>
		</div>
	@endfor
	
</template>

<?php temps_execution('parametrage'); ?>

<!-- CREER UN ELEMENT -->
<template v-if="workflow.type_action == 3">
	<div class="row">
		<div class="col-sm-2">@traduction('formulaire.workflow.element')</div>
		<div class="col-sm-10">
			<select name="parametrage[type_element]" v-model="workflow.parametrage.type_element">
				@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
					<option value="{{ $type_element_tmp }}">{{ $type_element_tmp }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.workflow.donnees')</div>
	</div>
	@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
		@foreach(App\Eden\Models\Champ_libre::orderBy('nom')->where('type_element', $type_element_tmp)->whereNotIn('type', array(-1,-2))->get()->pluck('nom', 'nom_sql')->toArray() as $nom_sql => $nom_champ)
			
			@if(in_array($nom_sql, array('modifie_le', 'modifie_par', 'cree_le', 'cree_par')))
				@continue
			@endif
			
			<div class="row" v-if="workflow.parametrage.type_element == '{{$type_element_tmp}}'">
				<div class="col-md-4">{{ $nom_champ }}</div>
				<div class="col-md-4">{!! management($type_element_tmp)->champ($nom_sql)->sans_vmodel()->cree_pour_workflow() !!}</div>
				<div class="col-md-4"><input type="text" name="parametrage[perso_{{$nom_sql}}]" v-model="workflow.parametrage.perso_{{$nom_sql}}" /></div>
			</div>
		@endforeach
	@endforeach
</template>

<?php temps_execution('creer un element'); ?>

<!-- NOTIFIER -->
<template v-if="workflow.type_action == 4">
	
	<div class="row">
		<div class="col-md-6">@traduction('formulaire.workflow.type_de_notification')</div>
		<div class="col-md-6">
			<select name="parametrage[zone]" v-model="workflow.parametrage.zone">
				<option value="modal_notifications">@traduction('formulaire.workflow.modale_de_notification')Modale de notification</option>
				<option value="navbar_notifications">@traduction('formulaire.workflow.dans_la_navnar')Dans la navbar</option>
			</select>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">@traduction('formulaire.workflow.type_destinataire')</div>
		<div class="col-md-6">
			<select name="parametrage[type_destinataire]" v-model="workflow.parametrage.type_destinataire">
				<option value="champ_utilisateur">@traduction('formulaire.workflow.champ_utilisateur')</option>
				<option value="utilisateur">@traduction('formulaire.workflow.utilisateur')</option>
				<option value="flux">@traduction('formulaire.workflow.flux_de_notification')</option>
			</select>
		</div>
	</div>
	<div class="row" v-show="workflow.parametrage.type_destinataire == 'utilisateur'">
		<div class="col-md-6">@traduction('formulaire.workflow.destinataire')</div>
		<div class="col-md-6">
			<select name="parametrage[destinataire]" v-model="workflow.parametrage.destinataire">
				@foreach(modele('utilisateur')->liste_utilisateurs_visibles() as $utilisateur)
					<option value="{{ $utilisateur->id }}">{{ $utilisateur->prenom }} {{ $utilisateur->nom }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row" v-show="workflow.parametrage.type_destinataire == 'flux'">
		<div class="col-md-6">@traduction('formulaire.workflow.flux')</div>
		<div class="col-md-6">
			<select name="parametrage[flux]" v-model="workflow.parametrage.flux">
				@foreach(modele('notification_flux')->orderBy('nom')->get() as $notification_flux)
					<option value="{{ $notification_flux->index }}">{{ $notification_flux->nom }}</option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="row" v-show="workflow.parametrage.type_destinataire == 'champ_utilisateur'">
		<div class="col-md-6">@traduction('formulaire.workflow.champ_utilisateur')</div>
		<div class="col-md-6">
			<select name="parametrage[champ_utilisateur]" v-model="workflow.parametrage.champ_utilisateur">
				@foreach(App\Eden\Variables::tables_libres_pour_workflow() as $type_element_tmp)
					@foreach(App\Eden\Models\Champ_libre::orderBy('nom')->where('type_element', $type_element_tmp)->where('type', 20)->where('liste_choix', 1)->get()->pluck('nom', 'nom_sql')->toArray() as $nom_sql => $nom_champ)
						<option value="{{ $nom_sql }}">{{ $nom_champ }}</option>
					@endforeach
				@endforeach
			</select>
		</div>
	</div>
	<div class="row" v-show="workflow.parametrage.type_destinataire == 'utilisateur'">
		<div class="col-md-12">@traduction('formulaire.workflow.notification')</div>
		<div class="col-md-12"><textarea name="parametrage[notification_html]" v-model="workflow.parametrage.notification_html"></textarea></div>
	</div>
</template>


<?php temps_execution('notifier'); ?>
