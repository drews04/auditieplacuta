<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }
    
    public function rules()
    {
        return [
            'category_id' => ['required', 'exists:forum_categories,id'],
            'title' => ['required', 'string', 'min:4', 'max:140'],
            'body' => ['required', 'string', 'min:10'],
        ];
    }
    
    public function messages()
    {
        return [
            'category_id.required' => 'Vă rugăm să selectați o categorie.',
            'category_id.exists' => 'Categoria selectată nu există.',
            'title.required' => 'Titlul este obligatoriu.',
            'title.min' => 'Titlul trebuie să aibă cel puțin 4 caractere.',
            'title.max' => 'Titlul nu poate avea mai mult de 140 de caractere.',
            'body.required' => 'Conținutul este obligatoriu.',
            'body.min' => 'Conținutul trebuie să aibă cel puțin 10 caractere.',
        ];
    }
}
