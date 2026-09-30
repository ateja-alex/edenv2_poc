@if(!empty($campagne_de_prospection_en_cours->questionnaire_id))

	<questionnaire
		ref="questionnaire_{{$campagne_de_prospection_en_cours->questionnaire_id}}"
		:questionnaire_id="{{$campagne_de_prospection_en_cours->questionnaire_id}}"
		:parametres="{type_element : 'client',element_id : {{$client->id}},type_element_origine :'campagne_de_prospection',element_origine_id:{{$campagne_de_prospection_en_cours->id}}}">
	</questionnaire>

@endif
