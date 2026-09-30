<div class="col-sm-4">
	<select name="langue">
		@foreach(modele('traduction_langue')->get() as $langue)
			<option value="{{$langue->code}}" {{moi()->langue == $langue->code || (empty(moi()->langue) && $langue->code == 'fr')? 'selected' : ''}}>
				{{$langue->nom}}
			</option>
		@endforeach
	</select>
</div>