<div class="container">
  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link" id="charge_recurrente" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/charge_recurrente') }}">@traduction('interface.treso_menu.charges_recurrentes')</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="creance_client" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/creance_client') }}">@traduction('interface.treso_menu.creances_clients')</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="revenu_recurrent" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/revenu_recurrent') }}">@traduction('interface.treso_menu.revenus_recurrents')</a>
    </li>
	<li class="nav-item">
      <a class="nav-link" id="rapprochement" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/rapprochement') }}">@traduction('interface.treso_menu.rapprochement_bancaire')</a>
    </li>
	<li class="nav-item">
      <a class="nav-link" id="mouvement_exceptionnel" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/mouvement_exceptionnel') }}">@traduction('interface.treso_menu.mouvements_exceptionnels')</a>
    </li>
	<li class="nav-item">
      <a class="nav-link" id="visualisation_tresorerie" style="color : #212121!important;" href="{{ URL::to('/eden/tresorerie/visualisation_tresorerie') }}">@traduction('interface.treso_menu.tableau_tresorerie')</a>
    </li>
  </ul>
</div>

@push('scripts')

<script type="text/javascript">

var ligne = document.getElementById(vue_instance.type); 

$(ligne).addClass('active');

ligne.style="background-color : #669E24;";

</script>

@endpush


