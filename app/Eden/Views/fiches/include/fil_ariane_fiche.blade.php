@include('eden::includes.fil_ariane', ['fil_ariane' => array(
	array('route' => 'base_eden.liste.index', 'nom' => table_libre($type_element)->element_pluriel, 'arguments' => [$type_element]),
	array('nom' => table_libre($type_element)->element . ' - ' . modele($type_element, $id_element)->chaine_affichage),
)])