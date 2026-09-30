<ul  class="nav navbar-nav side-nav nicescroll-bar" id="menus_replier" style="background-color: var(--background_menus);overflow-x:hidden;overflow-y:auto !important;height:94%;flex-flow:column;display: flex;">

    <template v-for="(menu,index_menu) in menus_a_afficher">
        <li v-if="menu.lien != null" class="nav-item" style="padding: 5px 0px;">
            <a class="nav-link" :href="menu.lien" :target="menu.target">
                <i :class="'fa fa-fw '+menu.icone" style="margin-right: 7px;"></i>
                <span class="right-nav-text" v-html="$root.traduction(menu.index_traduction+'.nom')"></span>
            </a>
        </li>
        <li v-else class="nav-item" style="padding: 5px 0px;">

			<a @click="collapse_sous_menu(index_menu)" class="nav-link" style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <i :class="'fa fa-fw '+menu.icone" style="margin-right: 7px;"></i>
                    <span class="right-nav-text" v-html="$root.traduction(menu.index_traduction+'.nom')"></span>
                </div>
                <i :class="'fas fa-chevron-' + (replier_sous_menu == 0 && menu_deployer == index_menu ? 'down' : 'right')" style="font-size: calc(var(--taille_police) * 10px)"></i>
			</a>

            <ul v-show="replier_sous_menu == 0 && menu_deployer == index_menu" style="background-color: var(--background_sous_menus);">
                <li v-for="sous_menu in menu.sous_menus" class="nav-item" style="padding: 6px 0px;">
                    <a class="nav-link" :href="sous_menu.lien" :target="sous_menu.target">
                        <i :class="'fa fa-fw '+sous_menu.icone" style="margin-right: 7px;"></i>
                        <span class="right-nav-text" v-html="$root.traduction(sous_menu.index_traduction+'.nom')"></span>
                    </a>
                </li>
            </ul>
        </li>
    </template>

	<li v-if="superadmin" class="nav-item" style="padding: 5px 0px; @if(isset($infos_menus['couleur_menu'])) background: {{ $infos_menus['couleur_menu'] }} @endif" >
		<a class="nav-link" href="{{ route('parametrage.index', [], false) }}">
			<i class="fa fa-fw fa-cogs" style="margin-right: 7px;"></i>
			<span class="right-nav-text">
				@traduction('menus.lien_standard.parametrage','nom')
			</span>
		</a>
	</li>

</ul>

<ul>
	<li class="nav-item" style="border-top: 1px solid #F8F8F8;cursor : pointer;">
		<a class="nav-link text-center"  @click="$root.modifier_type_menu" style="background: var(--background_menus); padding: 15px;">
			<i class="fa fa-fw fa-angle-right"></i>
		</a>
	</li>
</ul>

