<span class="dropdown"  data-toggle="tooltip" :title="traduction('interfaces.modales.dupliquer')">
	<i class="css_action_icon primaire fa fa-copy" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
	<div class="dropdown-menu">
        <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d')]) }}">@traduction('document.actions.dupliquer.dupliquer_avec_date_actuelle')</a>
        <a class="dropdown-item" href="{{ route('document.transformer_date', [$management->_type_element, $management->modele->id, $management->_type_element, formate_date('Y-m-d', $management->modele->date)]) }}">@traduction('document.actions.dupliquer.dupliquer_avec_date_facture')</a>
        <!-- variantes -->
        @if(fonctionnalite('gescom_variantes_devis') && $management->_type_element == 'devis_vente')
            <a class="dropdown-item" href="{{ route('document.transformer_variante', [$management->_type_element, $management->modele->id, $management->_type_element, date('Y-m-d'), $management->modele->id]) }}">@traduction('document.actions.dupliquer.dupliquer_variante')</a>
        @endif
	</div>
</span>





