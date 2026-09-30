<?php

    function blade_compile($value, array $args = array()) {
        return \Blade::render(html_entity_decode($value), $args);
    }