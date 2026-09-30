<div class="card mb-3">
	<div class="card-header js_fermeture_bloc">
		@if(isset($afficher_par_defaut))
			@if($afficher_par_defaut === true)
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-up"></span>
				</span>

			@else
				<span class="" style="float: right; margin-top: 3px; cursor: pointer;">
					<span class="fa fa-chevron-down"></span>
				</span>
			@endif
		@endif
		<h4>
			@traduction('module_sur_fiche.projet.planning_projet.titre')
		</h4>
	</div>
	<div class="card-body" @if(isset($afficher_par_defaut) && $afficher_par_defaut === false) style="display: none;" @endif>
		
		
		
	</div>
</div>
