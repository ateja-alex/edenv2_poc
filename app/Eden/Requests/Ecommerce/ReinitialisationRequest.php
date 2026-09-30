<?php

namespace App\Eden\Requests\Ecommerce;

use Illuminate\Foundation\Http\FormRequest;

class ReinitialisationRequest extends FormRequest {

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

            'mdp_token' => 'required',
            'adresse_email' => 'required|email|max:100',
            'motdepasse' => 'confirmed|required|min:6'
        ];
    }
}
