<table class="table table-bordered table-hover css_form css_table_parametrage">
	<thead class="css_thead_parametrage">
		<th>@traduction('interface.parametrage_fonctionnalites.nom_parametre')</th>
		<th>@traduction('interface.parametrage_fonctionnalites.valeur')</th>
	</thead>
	<tbody>
		<template v-for="(fonctionnalites_de_la_categorie, categorie) in fonctionnalites">
			<tr>
				<td colspan="2" class="css_form_ligne_titre">
					@{{ categorie }}
				</td>
			</tr>
			<template v-for="fonctionnalite in fonctionnalites_de_la_categorie">
				<tr v-show="recherche_fonctionnalite == '' || fonctionnalite.nom.indexOf(recherche_fonctionnalite) >= 0">
					<td>
						<div class="d-flex align-items-center">
							<span>@{{ fonctionnalite.nom }}</span>
							<span class="css_toggle_aide_parametrage js_toggle_infos_module"><i class="far fa-question-circle" v-if="fonctionnalite.description != null && fonctionnalite.description != ''"></i></span>
						</div>
						<div class="css_infos_module_parametrage js_block_infos_module">
							@{{ fonctionnalite.description }}
						</div>
					</td>
					<!-- toggle -->
					<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'toggle'">
						<div class="css_toggle_parametrage slide">
							<input :id="fonctionnalite.fonctionnalite" type="checkbox" class="css_checkbox_toggle_slide_parametrage" v-model="parametres_fonctionnalites[fonctionnalite.fonctionnalite]"/>
							<label :for="fonctionnalite.fonctionnalite" class="css_label_toggle_slide_parametrage">
								<div class="toggle_rectangle slide"></div>
							</label>
						</div>
					</td>
					<!-- select -->
					<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'select'">
						<select v-model="parametres_fonctionnalites[fonctionnalite.fonctionnalite]" class="css_form">
							<option value="" selected disabled>@traduction('interface.parametrage_fonctionnalites.selectionner_option')</option>
							<option :value="cle" v-for="(valeur, cle) in fonctionnalite.valeurs_select">@{{valeur}}</option>
						</select>
					</td>
					<!-- input -->
					<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'input'">
						<input type="text" :placeholder="fonctionnalite.placeholder" v-model="parametres_fonctionnalites[fonctionnalite.fonctionnalite]">
					</td>
					<!-- textarea -->
					<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'textarea'">
						<textarea :placeholder="fonctionnalite.placeholder" v-model="parametres_fonctionnalites[fonctionnalite.fonctionnalite]"></textarea>
					</td>
				</tr>
			</template>
		</template>




		<?php
		/*
		<tr>
			<td>
				<div class="d-flex align-items-center">
					<span>Mode multi entités</span>
					<span class="css_toggle_aide_parametrage js_toggle_infos_module"><i class="far fa-question-circle"></i></span>
				</div>
				<div class="css_infos_module_parametrage js_block_infos_module">
					Lorem ipsum dolor, sit, amet consectetur adipisicing elit. Harum eius ducimus perferendis vero nemo pariatur doloribus voluptatem amet, dolor, tenetur quaerat et temporibus odio hic, itaque illum ratione sed praesentium.
				</div>
			</td>
			<td class="css_td_valeur_parametrage">
				<input type="text" class="js_datepicker" placeholder="JJ/MM/AAAA">
			</td>
		</tr>
		*/
		?>
	</tbody>
</table>