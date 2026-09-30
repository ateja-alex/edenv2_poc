<?php

function select($requete) {
	
	return \DB::select(vsprintf(str_replace(['?'], ['\'%s\''], $requete->toSql()), $requete->getBindings()));
}