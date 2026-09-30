<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
</div>
<div class="row">
	@champ('coupon_reduction', 'type_coupon', 3,3)
	@champ('coupon_reduction', 'entite_id', 2,4)
</div>
<div class="row">
	@champ('coupon_reduction', 'debut_validite', 2,4)
	@champ('coupon_reduction', 'fin_validite', 2,4)
</div>
<div class="row" v-if="coupon_reduction.type_coupon == 3">
	@champ('coupon_reduction', 'code', 2,4)
	@champ('coupon_reduction', 'minimum_de_commande', 4,2)
</div>
<div class="row">
	@champ('coupon_reduction', 'valeur', 2,4)
	<div class="col-sm-4" v-if="coupon_reduction.type_coupon == 3">
		{!! management('coupon_reduction')->champ('type_de_reduction')->nom() !!}
	</div>
	<div class="col-sm-2" v-if="coupon_reduction.type_coupon == 3">{!! management('coupon_reduction')->champ('type_de_reduction')->cree() !!}</div>
</div>
<div class="row" v-if="coupon_reduction.type_coupon == 3">
	@champ('coupon_reduction', 'nouveau_client_seulement', 4,2)
	@champ('coupon_reduction', 'nominatif', 4,2)
</div>
<div class="row" v-show="coupon_reduction.nominatif == 1">
	@champ('coupon_reduction', 'client_id', 2,10)
</div>
<div class="row" v-if="coupon_reduction.type_coupon == 3">
	<div class="col-sm-12 css_form_ligne_titre">
		{!! management('coupon_reduction')->champ('liste_familles_coupon')->nom() !!}
	</div>
</div>
<template v-if="coupon_reduction.type_coupon == 3">
@foreach(modele('famille')->where(function($r) { $r->where('parent_id', 0)->orWhereNull('parent_id'); })->get() as $famille)
	<div class="row">
		<div class="col-sm-12">
			<input type="checkbox" v-model="coupon_reduction.liste_familles_coupon[{{ $famille->id }}]" name="liste_familles_coupon[{{ $famille->id }}]" value="{{ $famille->id }}" /> {{ $famille->nom }}
		</div>
	</div>
	@foreach(modele('famille')->where('parent_id', $famille->id)->get() as $famille_1)
		<div class="row">
			<div class="col-sm-1"></div>
			<div class="col-sm-11">
				<input type="checkbox" v-model="coupon_reduction.liste_familles_coupon[{{ $famille_1->id }}]" name="liste_familles_coupon[{{ $famille_1->id }}]" value="{{ $famille_1->id }}" /> {{ $famille_1->nom }}
			</div>
		</div>
		@foreach(modele('famille')->where('parent_id', $famille_1->id)->get() as $famille_2)
			<div class="row">
				<div class="col-sm-2"></div>
				<div class="col-sm-10">
					<input type="checkbox" v-model="coupon_reduction.liste_familles_coupon[{{ $famille_2->id }}]" name="liste_familles_coupon[{{ $famille_2->id }}]" value="{{ $famille_2->id }}" /> {{ $famille_2->nom }}
				</div>
			</div>
			@foreach(modele('famille')->where('parent_id', $famille_2->id)->get() as $famille_3)
				<div class="row">
					<div class="col-sm-3"></div>
					<div class="col-sm-9">
						<input type="checkbox" v-model="coupon_reduction.liste_familles_coupon[{{ $famille_3->id }}]" name="liste_familles_coupon[{{ $famille_3->id }}]" value="{{ $famille_3->id }}" /> {{ $famille_3->nom }}
					</div>
				</div>
				@foreach(modele('famille')->where('parent_id', $famille_3->id)->get() as $famille_4)
					<div class="row">
						<div class="col-sm-4"></div>
						<div class="col-sm-8">
							<input type="checkbox" v-model="coupon_reduction.liste_familles_coupon[{{ $famille_4->id }}]" name="liste_familles_coupon[{{ $famille_4->id }}]" value="{{ $famille_4->id }}" /> {{ $famille_4->nom }}
						</div>
					</div>
				@endforeach
			@endforeach
		@endforeach
	@endforeach
@endforeach
</template>