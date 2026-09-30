<?php
$formulaire_generique_nombre_col = 0;
$formulaire_generique_premier_titre = true;
$formulaire_generique_premier_bloc = true;
?>
<div class="row">

@foreach(table_libre($type_element)->champs_libres()->where('afficher_sur_formulaire', 1)->orderBy('ordre')->get() as $champ_libre)

	@if($champ_libre->type == -1)
		
		@if($formulaire_generique_premier_titre !== true)
			</div>
		@endif
		
		</div>	
		<div class="row">
		
			<div class="col-sm-12 css_form_ligne_titre">
				@if($formulaire_generique_premier_titre === true)
					<span class="fa fa-chevron-up js_formulaire_deplie_titre" style="float: right; margin-top: 9px; cursor: pointer;"></span>
				@else
					<span class="fa fa-chevron-down js_formulaire_deplie_titre" style="float: right; margin-top: 9px; cursor: pointer;"></span>
				@endif
				
				{{ $champ_libre->nom }}
			</div>
		
		</div>	
		@if($formulaire_generique_premier_titre === true)
			<div class="js_formulaire_ligne">
		@else
			<div class="js_formulaire_ligne" style="display: none;">
		@endif
		<div class="row">
		
		<?php
		$formulaire_generique_nombre_col = 0;
		$formulaire_generique_premier_titre = false;
		?>
		
		@continue
	@endif
	
	@if(empty($champ_libre->taille_libelle))
		<?php $champ_libre->taille_libelle = 2; ?>
	@endif
	
	@if(empty($champ_libre->taille_champ))
		<?php $champ_libre->taille_champ = 4; ?>
	@endif
	
	@if(!empty($champ_libre->taille_avant))
		<div class="col-sm-{{ $champ_libre->taille_avant }}"></div>
		<?php
		$formulaire_generique_nombre_col += $champ_libre->taille_avant;
		?>
	@endif
	
	@if($formulaire_generique_nombre_col >= 12)
		</div>	
		<div class="row js_formulaire_ligne">
		<?php
		$formulaire_generique_nombre_col = 0;
		?>
	@endif
	
	<div class="col-sm-{{ $champ_libre->taille_libelle }}">{{ $champ_libre->nom() }}</div>
	
	<?php
	$formulaire_generique_nombre_col += $champ_libre->taille_libelle;
	?>
	
	@if($formulaire_generique_nombre_col >= 12)
		</div>	
		<div class="row js_formulaire_ligne">
		<?php
		$formulaire_generique_nombre_col = 0;
		?>
	@endif
	
	<div class="col-sm-{{ $champ_libre->taille_champ }}">{!! champ_libre($champ_libre->type_element, $champ_libre->nom_sql)->champ->cree() !!}</div>
	
	<?php
	$formulaire_generique_nombre_col += $champ_libre->taille_champ;
	?>
	
	@if($formulaire_generique_nombre_col >= 12)
		</div>	
		<div class="row js_formulaire_ligne">
		<?php
		$formulaire_generique_nombre_col = 0;
		?>
	@endif
	
	@if(!empty($champ_libre->taille_apres))
		<div class="col-sm-{{ $champ_libre->taille_apres }}"></div>
		<?php
		$formulaire_generique_nombre_col += $champ_libre->taille_apres;
		?>
	@endif
	
	@if($formulaire_generique_nombre_col >= 12)
		</div>	
		<div class="row js_formulaire_ligne">
		<?php
		$formulaire_generique_nombre_col = 0;
		?>
	@endif
	
	
@endforeach

@if($formulaire_generique_nombre_col != 0)
	</div>
@endif

</div>
