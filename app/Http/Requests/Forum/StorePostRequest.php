<?php

namespace App\Http\Requests\Forum;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }
    
    public function rules()
    {
        return [
            'thread_id' => ['required', 'exists:forum_threads,id'],
            'body' => ['required', 'string', 'min:2'],
        ];
    }
    
    public function messages()
    {
        return [
            'thread_id.required' => 'ID-ul thread-ului este obligatoriu.',
            'thread_id.exists' => 'Thread-ul nu există.',
            'body.required' => 'Conținutul este obligatoriu.',
            'body.min' => 'Conținutul trebuie să aibă cel puțin 2 caractere.',
        ];
    }
}
