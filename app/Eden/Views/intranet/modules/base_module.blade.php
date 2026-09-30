<section v-show="bloc_affiche == '{{$id}}'">
	<div class="big-box scroll" style="background-color:{{$couleur}};" >
		<div class="box-text box-intranet">
			<div class="card-header css_flex_header_liste" style="background-color: unset;border-bottom: 1px solid rgb(255 255 255);">
				<p class="title">
					@traduction('intranet.modules.module_{{$id}}','nom')
				</p>
				<div class="ml-auto">
					@yield('options_nom_module_'.$id)
				</div>
			</div>
			<div class="card-body scrollbar_intranet corps_intranet">
				<div class="alert alert-danger" id="alerte_erreur_{{$id}}" style="display: none" ></div>
				<div class="alert alert-success" id="alerte_succes_{{$id}}" style="display: none" ></div>
				@yield('contenu_'.$id)
			</div>
			<div class="card-footer" style="background-color: unset;border-top:unset;">
				@yield('footer_'.$id)
			</div>
		</div>
	</div>
	@yield('footer_boutons_'.$id)
</section>