<?php

namespace App\Eden\Requests\Ecommerce;

use Illuminate\Foundation\Http\FormRequest;

class InscriptionRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {

        return [

            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'adresse_email' => 'required|email|max:100',
            'motdepasse' => 'confirmed|required|min:6'
        ];
    }
}
