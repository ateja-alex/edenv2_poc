<div id="menu_app" onClick="$('#menu_app').slideToggle()" style="overflow: auto;background: var(--background_navbar);opacity:1;">
	<div class="container">
		<div class="row">
			<span class="ml-auto float-left" style="font-size: 24px;cursor: pointer;padding-right: 23px;padding-top: 23px;"><i class="fas fa-times"></i></span>
		</div>
		<div class="row css_menu_titre js_menu" style="color: white !important">
			<div class="col-md-12">
				<a href="{{ route('base_eden.accueil.index', [], false) }}" style="color: white"  >
					@{{ maquette_nom_application }}
				</a>
			</div>
		</div>
		<div class="row">
			<template v-for="menu in menus_a_afficher.filter((menu) => {return menu.lien != null})">
				<a style="color: white" class="col-6 col-md-2 css_menu_app_lien" :href="menu.lien" :target="menu.target">
					<span :class="'fa fa-fw '+menu.icone"></span><br/>
					<span v-html="$root.traduction(menu.index_traduction+'.nom')"></span>
				</a>
			</template>

			<a v-if="superadmin" class="col-6 col-md-2 css_menu_app_lien css_menu_app_lien_super_admin" href="{{ route('parametrage.index', [], false) }}" style="color: white !important; filter: none !important">
				<span class="fa fa-fw fa-cogs"></span><br/>
				<span v-html="$root.traduction('menus.lien_standard.parametrage.nom')"></span>
			</a>

		</div>

		<template v-for="categorie in menus_a_afficher.filter((menu) => {return menu.lien == null})">
			<div :class="'row css_menu_titre js_menu js_menu_'+categorie.id" style="color: white !important">
				<div class="col-md-12">
					<span v-html="$root.traduction(categorie.index_traduction+'.nom')"></span>
				</div>
			</div>
			<!-- les sous menus -->
			<div :class="'row js_menu js_menu_'+categorie.id">
				<template v-for="sous_menu in categorie.sous_menus">
					<a style="color: white" class="col-6 col-md-2 css_menu_app_lien" :href="sous_menu.lien" :target="sous_menu.target">
						<span :class="'fa fa-fw '+sous_menu.icone"></span><br/>
						<span v-html="$root.traduction(sous_menu.index_traduction+'.nom')"></span>
					</a>
				</template>
			</div>
		</template>
	</div>
</div>
