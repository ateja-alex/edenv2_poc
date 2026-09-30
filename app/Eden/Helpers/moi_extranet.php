<?php

function moi_extranet() {

    if((!isset($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] != fonctionnalite('url_extranet')) &&
        (!defined('type_export_en_cours') || type_export_en_cours != 'extranet'))
        return null;

	return session()->get('utilisateur_eden_extranet');
}