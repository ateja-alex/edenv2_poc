<?php
	$timelines_possibles = App\Eden\Models\Champs_liste_formatee::where('id_liste_choix',33)->get();

	if($timelines_possibles->isEmpty()){

		$timelines_possibles = array();

		$timelines_possibles_defaut = management('echange')->champ('type')->valeurs_possibles;

		foreach($timelines_possibles_defaut as $id => $valeur){

			$object = new stdClass();
			$object->desactivee = 0;
			$object->valeur = $valeur;
			$object->id_valeur = $id;
			$object->icone = null;
			$object->couleur = null;

			$timelines_possibles[] =$object;
		}
	}

	$liste_tables =service('echange')->liste_echanges_possibles();
?>
<div style="text-align: center;">
	@foreach($timelines_possibles as $choix_timeline)
		@if($choix_timeline->desactivee == 0 && $choix_timeline->id_valeur!=0)
			<span @click="changer_type_echange({{$choix_timeline->id_valeur}})"
				  class="css_type_echange"
				  :class="{'css_type_echange_selectionne' : echange.type == {{$choix_timeline->id_valeur}} }"
				  title="{{traduction('valeurs_listes_formatees.33.valeur_' . $choix_timeline->id_valeur) }}"
				  data-toggle="tooltip" data-placement="top" >
													<i class=" {{$choix_timeline->icone == null ? 'fas fa-stream' : $choix_timeline->icone }}"
													   style="color: {{$choix_timeline->couleur}}"></i>
												</span>
		@endif
	@endforeach
</div>

<br>


<template v-if="!echange.id">
	<input type="hidden" name="utilisateur_id" :value="echange.utilisateur_id">
</template>

<input type="hidden" name="type" :value="echange.type">
<div class="row" v-if="!echange.id">
	<div class="col-sm-2">@traduction('formulaire.echange.element')</div>
	<div class="col-sm-4">
		<select name="type_element" v-model="echange.type_element" @change="modification_type_element">
			@foreach($liste_tables as $index => $nom)
				<option value="{{ $index }}">{{ ucfirst($nom) }} ({{ $index}})</option>
			@endforeach
		</select>
	</div>
</div>
<div class="row" v-if="echange.type_element != ''">
	<div class="col-sm-2">
		<span v-html="$root.traduction('tables_libres.'+echange.type_element+'.element')"></span>
	</div>
	<div class="col-sm-auto" style="display: flex" v-if="echange.id">
		<champ-selection-element :readonly="true"  :modele="echange" type_element_origine="echange" :type_element="echange.type_element" :nom_sql="'element_id'" name="element_id"></champ-selection-element>
	</div>
	<div class="col-sm-auto" style="display: flex" v-else>
		<champ-selection-element :key="componentCle" :modele="echange" type_element_origine="echange" :type_element="echange.type_element" :nom_sql="'element_id'" name="element_id"></champ-selection-element>
	</div>
</div>

<div class="row" v-if="echange.type_element != 'client'">
	<div class="col-sm-2">
		<span v-html="$root.traduction('tables_libres.client.element')"></span>
	</div>
	<div class="col-sm-auto" style="display: flex" >
		<champ-selection-element :key="componentCle2" :modele="echange" type_element_origine="echange" :type_element="'client'" :nom_sql="'client_id'" name="client_id"></champ-selection-element>
	</div>
</div>

@foreach($timelines_possibles as $id_type_echange => $timeline)
	@if($timeline->desactivee == 0 && $timeline->id_valeur!=0)

		<div v-if="echange.type == {{$timeline->id_valeur}} && echange.type_element != '' && echange.element_id != ''">

			{!! formulaire('formulaire_'.$timeline->id_valeur.'_echange') !!}
		</div>
	@endif
@endforeach

@push('donnees_pour_vuejs_data')

	echange_element : {},
	componentCle: 0,
	componentCle2: 0 ,
	componentCle3: 0 ,

@endpush

@push('donnees_pour_vuejs_methods')

	changer_type_echange(type){

        var vue_instance = this;

		vue_instance.echange.type = type;
		vue_instance.forceRerender();
	},

	modification_type_element(){

        var vue_instance = this;

		vue_instance.echange.element_id = "";
		vue_instance.forceRerender();

	},

	// Permet de recharger les components de champ-selection-element

	<?php
		$utilisateurs = modele('utilisateur')->liste_utilisateurs_visibles();
		$users = [];

		if(!empty($utilisateurs)){

			foreach ($utilisateurs as $index => $utilisateur){

				$users[$index] = [];
				$users[$index]['name'] = $utilisateur['prenom'] . ' ' . $utilisateur['nom'];
				$users[$index]['username'] = retraite_caracteres_speciaux($utilisateur['prenom'] . $utilisateur['nom']);
				$users[$index]['image'] = asset('storage') . '/' . $utilisateur['avatar'];

			}

		}

		$users = collect($users);
	?>
	forceRerender() {

        var vue_instance = this;

		vue_instance.componentCle += 1;
		vue_instance.componentCle2 += 1;
		vue_instance.componentCle3 += 1;
		$("textarea").mention({
			queryBy: ['name', 'username'],
			users: {!! $users !!}
		});
		vue_instance.$forceUpdate();
	},

@endpush

@push('donnees_pour_vuejs_watch')
	echange: {
		handler: function(newVal, oldVal) {

			var vue_instance = this;

			type_element = vue_instance.echange.type_element;
			element_id = vue_instance.echange.element_id;

			//On récupére le modéle de l'élément lié à l'échange
			if(type_element && type_element== 'contact' && element_id && element_id!= ''){
				$.ajax({

					url: "{{ URL::to('/eden/element') }}/"+type_element+'/'+element_id,
					data: {

						type_element: type_element,
						id_element: element_id,
					},
					dataType: "json"
				}).done(function(retour) {

					vue_instance.echange_element = retour;
				});
			}
			else{

				vue_instance.echange_element = {};

			}

			this.$forceUpdate();
			vue_instance.forceRerender();
		},
		deep: true,
	},
@endpush
