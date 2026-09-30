<?php

/*
*
* Helper pour aller chercher un management pour un élément
*
* @param $type_element string
*
*/
function queue($nom) {

    $tests = array(

        "\\App\\Queues\\".ucfirst($nom)."_queue",
        "\\App\\Eden\\Queues\\".ucfirst($nom)."_queue",
    );

    return classe_existante($tests);
}