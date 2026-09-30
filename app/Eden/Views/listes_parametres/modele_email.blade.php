<table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
	<thead>
		<tr>
			<th scope="col">#</th>
			<th scope="col">@traduction('interface.modele_email.nom')</th>
			<th scope="col">@traduction('interface.modele_email.sujet')</th>
		</tr>
	</thead>
	<tbody>
		<tr v-for="parametre in parametres">
			<td><span class="css__lien" @click="modifier" :id_parametre="parametre.id">@{{ parametre.id }}</span></td>
			<td v-html="parametre.nom"></td>
			<td v-html="parametre.sujet_modele"></td>
		</tr>
		<tr v-show="!parametres.length">
			<td colspan="2" class="css_aucune_donnee_dans_liste">@traduction('interface.parametres_liste_libre.aucune_donnee')</td>
		</tr>
	</tbody>
</table>