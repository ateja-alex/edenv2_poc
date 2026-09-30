<?php

return [
		array(

			'route_parametres' => 'ticket_client',
			'id' => "liste_tickets_client",
			'type_2' => "liste",
			'nom' => "Tickets",
			'route' => array('base_eden.liste.index', ['ticket_client']),
			'icone' => 'fas fa-ticket-alt',
			'inactif' => '',
			'index_traduction' => 'menus_extranet.lien.liste_tickets_client',
			'ordre' => '1',
		),
];