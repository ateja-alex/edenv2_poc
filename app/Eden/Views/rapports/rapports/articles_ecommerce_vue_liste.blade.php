<div class="table-responsive">
	<div class="css_actualisation_liste" @click="actualisation_filtres()">Cliquez ici pour actualiser la liste</div>
	<table class="table table-bordered table-hover" id="liste_elements_{{$id_liste}}" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th>Photos</th>
				<th>Catégorie</th>
				<th>Article</th>
				<th>Tarif</th>
				<th>En ligne</th>
			</tr>
		</thead>
		<tbody>
			<tr v-show="liste.lignes.length == 0">
				<td colspan="2">Aucun article dans cette liste !</td>
			</tr>
			<tr v-for="(ligne, index) in liste.lignes" class="js_liste_ligne_selectionnable js_ligne_element" :element_id="ligne['id']">
				<td><img :src="ligne.image_principale" style="max-width: 50px; max-height: 50px;" /></td>
				<td>@{{ ligne.famille_id }}</td>
				<td><a :href="'/eden/fiche/article/'+ligne.id">@{{ ligne.designation }}</a></td>
				<td>@{{ ligne.tarif | montant }} {!! maquette('devise_application_symbole') !!}</td>
				<td><input type="checkbox" :checked="ligne.en_ligne == 'Oui'" :index="index" nom_sql="en_ligne"  data-toggle="toggle" data-size="mini" data-on="En ligne" data-off="Hors ligne" data-width="110"></td>
			</tr>
		</tbody>
	</table>
</div>

