<?php

namespace Pterodactyl\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;

class LocaleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // The multiload backend joins several locales and namespaces with "+", which
            // arrives here as a space.
            'locale' => ['required', 'string', 'max:32', 'regex:/^[a-z]{2}( [a-z]{2}){0,4}$/'],
            'namespace' => ['required', 'string', 'max:1024', 'regex:/^[a-z]{1,64}( [a-z]{1,64}){0,39}$/'],
        ];
    }
}
