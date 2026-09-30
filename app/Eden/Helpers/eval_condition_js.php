<?php

function eval_condition_js($condition, $type_element, $element) {

    try{

        $condition_variable = preg_replace("/(['\"])(?:\\\\.|(?!\\1).)*\\1/", "", $condition);
        preg_match_all("/(?<![A-Za-z0-9_])[\$A-Za-z_][A-Za-z0-9_]*(?:\\.[A-Za-z_][A-Za-z0-9_]*)+/", $condition_variable, $bloc_conditions);

        $correspondances_variables = [  
            'moi' => 'moi()'
        ];

        foreach($bloc_conditions[0] as $bloc_condition) {

            $parties = explode('.', $bloc_condition);

            $remplacement = '';

            $type_element_condition = array_shift($parties);

            if($type_element_condition == '$root'){

                $variable_condition = array_shift($parties);

                if(isset($correspondances_variables[$variable_condition]))
                    $remplacement = $correspondances_variables[$variable_condition];
            }
            else if($type_element_condition == $type_element) {
                $remplacement = '$element';
            }
            else
                continue;

            $element_condition = array_shift($parties);

            $remplacement .= "->$element_condition";

            $condition = str_replace($bloc_condition, $remplacement, $condition);
        }

        return eval('return '.$condition.';');
    }
    catch(\Exception | \Throwable $e) {
        return false;
    }
}