<div class="collapse navbar-collapse" id="navbarResponsive" >
	<ul class="navbar-nav navbar-sidenav" id="menus" style="background-color: var(--background_menus); border: solid var(--background_menus) 1px">

	<template v-for="(menu,index_menu) in menus_a_afficher">
		<li v-if="menu.lien != null" class="nav-item" :class="url_actuel == menu.lien ? 'active' : ''"
			data-toggle="tooltip" data-placement="right" :title="menu.nom" :style="'padding: 6px 0px;'+(menu.couleur_menu ? 'background:'+menu.couleur_menu : '')">
			<a class="nav-link" :href="menu.lien" :target="menu.target">
				<i :class="'fa fa-fw '+menu.icone" style="margin-right: 7px;"></i>
				<span class="nav-link-building">
					<span v-html="$root.traduction(menu.index_traduction+'.nom')"></span>
				</span>
			</a>
		</li>
		<li v-else class="nav-item" data-toggle="tooltip" data-placement="right" :title="menu.nom" style="padding: 6px 0px;">

			<a @click="collapse_sous_menu(index_menu)" class="nav-link" style="display:flex;align-items:center;justify-content:space-between">
				<div>
                    <i :class="'fa fa-fw '+menu.icone" style="margin-right: 7px;"></i>
                    <span class="right-nav-text" v-html="$root.traduction(menu.index_traduction+'.nom')"></span>
                </div>
				<i :class="'fas fa-chevron-' + (replier_sous_menu == 0 && menu_deployer == index_menu ? 'down' : 'right')" style="font-size: calc(var(--taille_police) * 10px)"></i>
			</a>

            <ul class="sidenav-second-level" v-show="menu_deployer == index_menu" style="background-color: var(--background_sous_menus);"">
                <li v-for="sous_menu in menu.sous_menus" class="nav-item" :class="url_actuel == sous_menu.lien ? 'active' : ''"
                    data-toggle="tooltip" data-placement="right" :title="sous_menu.nom" style="padding: 6px 0px;">
                    <a class="nav-link" :href="sous_menu.lien" :target="sous_menu.target">
                        <i :class="'fa fa-fw '+sous_menu.icone" style="margin-right: 7px;"></i>
                        <span class="nav-link-building" v-html="$root.traduction(sous_menu.index_traduction+'.nom')"></span>
                    </a>
                </li>
			</ul>
		</li>
	</template>

	<li v-if="superadmin" class="nav-item" data-toggle="tooltip" data-placement="right" style="padding: 6px 0px">
		<a class="nav-link" href="{{ route('parametrage.index', [], false) }}">
			<i class="fa fa-fw fa-cogs" style="margin-right: 7px;"></i>
			<span class="nav-link-building">
				@traduction('menus.lien_standard.parametrage','nom')
			</span>
		</a>
	</li>

	</ul>
	<ul class="navbar-nav sidenav-toggler" style="border-top: 1px solid #F8F8F8;border-right: 1px solid #F8F8F8;">
		<li class="nav-item">
			<a class="nav-link text-center"  @click="$root.modifier_type_menu" style="background: var(--background_menus);">
				<i class="fa fa-fw fa-angle-left"></i>
			</a>
		</li>
	</ul>
</div>
