<?php

function est_un_mobile() {
    if(!isset($_SERVER["HTTP_USER_AGENT"]))
        return false;
    
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}