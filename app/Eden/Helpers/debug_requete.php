<?php

function debug_requete($requete) {

	dd_eden(vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings()));
}

function debug_requete_dump($requete) {

	dump(vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings()));
}

function debug_requete_dd($requete) {

	dd(vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings()));
}