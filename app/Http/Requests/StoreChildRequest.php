<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_active ?? false;
    }

    public function rules(): array
    {
        return ['name' => 'required|string|max:100', 'date_of_birth' => 'nullable|date|before_or_equal:today', 'learning_level' => 'required|in:level-1,level-2,level-3', 'avatar' => 'nullable|in:fox,bear,rabbit,cat', 'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'];
    }
}
