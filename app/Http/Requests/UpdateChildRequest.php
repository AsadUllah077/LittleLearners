<?php

namespace App\Http\Requests;

class UpdateChildRequest extends StoreChildRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('child'));
    }
}
