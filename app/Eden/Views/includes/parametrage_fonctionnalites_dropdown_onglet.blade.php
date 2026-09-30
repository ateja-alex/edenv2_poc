<div class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton" style="width: 230px !important;">
	@foreach($modules as $module)
		<a class="dropdown-item" href="{{ route('parametrage.fonctionnalites.module', $module['nom_module_lien']) }}">
			{!! $module['type_module'] !!}
		</a>
	@endforeach
</div>
