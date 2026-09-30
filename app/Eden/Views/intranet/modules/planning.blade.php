<section v-show="bloc_affiche == '{{$id}}'">
	<planning ref="planning" :lecture_seule="true"></planning>
</section>

@section('styles')
	<link rel="stylesheet" href="{{ asset('eden/css/planning.css') }}?<?=time()?>" />
@endsection