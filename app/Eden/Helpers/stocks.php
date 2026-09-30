<?php

function presence_conditionnement(){

    return cache_eden('conditionnement_present', fn() => modele('conditionnement')->count() > 0);
}