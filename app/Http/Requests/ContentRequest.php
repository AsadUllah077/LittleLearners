<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin' && $this->user()->is_active;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->configuration) && $this->configuration !== '') {
            $decoded = json_decode($this->configuration, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['configuration' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $resource = $this->route('resource');
        $id = $this->route('id');
        $common = ['image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096', 'audio' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240', 'is_active' => 'required|boolean', 'description' => 'nullable|string|max:5000', 'sort_order' => 'required|integer|min:0|max:10000'];

        return match ($resource) {
            'categories' => array_merge(array_diff_key($common, ['audio' => true]), ['name' => 'required|string|max:100', 'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('learning_categories')->ignore($id)], 'icon' => 'nullable|string|max:30']),
            'lessons' => array_merge($common, ['title' => 'required|string|max:150', 'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('lessons')->ignore($id)], 'learning_category_id' => 'required|exists:learning_categories,id', 'content_type' => 'required|in:learning,lesson', 'age_min' => 'required|integer|between:1,5', 'age_max' => 'required|integer|between:1,5|gte:age_min']),
            'learning-items' => ['lesson_id' => 'required|exists:lessons,id', 'title' => 'required|string|max:100', 'display_value' => 'required|string|max:100', 'lowercase_value' => 'nullable|string|max:100', 'description' => 'nullable|string|max:5000', 'example_text' => 'nullable|string|max:255', 'sort_order' => $common['sort_order'], 'image' => $common['image'], 'audio' => $common['audio']],
            'activities','games' => ['lesson_id' => 'required|exists:lessons,id', 'title' => 'required|string|max:150', 'activity_type' => 'required|in:matching,listen,counting,ordering,missing,lowercase,tracing,identification', 'instructions' => 'required|string|max:2000', 'difficulty' => 'required|in:easy,medium,advanced', 'is_active' => 'required|boolean', 'configuration' => 'nullable|array', 'configuration.questions' => 'sometimes|required|array|min:1|max:30', 'configuration.questions.*.prompt' => 'required|string|max:500', 'configuration.questions.*.speak' => 'nullable|string|max:150', 'configuration.questions.*.visual' => 'nullable|string|max:150', 'configuration.questions.*.image' => 'nullable|string|max:255', 'configuration.questions.*.mode' => 'required|in:choice,listen,ordering', 'configuration.questions.*.options' => 'required|array|min:2|max:12', 'configuration.questions.*.options.*' => 'required|string|max:100', 'configuration.questions.*.answer' => 'required|string|max:1000', 'question_image' => $common['image']],
            'users' => ['name' => 'required|string|max:100', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)], 'is_active' => 'required|boolean'],
            'children' => ['name' => 'required|string|max:100', 'date_of_birth' => 'nullable|date|before_or_equal:today', 'learning_level' => 'required|in:level-1,level-2,level-3'],
            default => [],
        };
    }

    public function after(): array
    {
        return [function ($validator) {
            if ($this->hasFile('question_image') && empty($this->configuration['questions'] ?? [])) {
                $validator->errors()->add('configuration', 'Add a question before uploading its image.');
            }
            if (! is_array($this->configuration) || ! is_array($this->configuration['questions'] ?? [])) {
                return;
            }
            foreach ($this->configuration['questions'] ?? [] as $q) {
                if (! is_array($q) || ! isset($q['options'],$q['answer']) || ! is_array($q['options']) || ! is_string($q['answer'])) {
                    continue;
                }
                $expected = $q['options'];
                $ordered = explode('|', $q['answer']);
                sort($expected);
                sort($ordered);
                $valid = ($q['mode'] ?? '') === 'ordering' ? $expected === $ordered : in_array($q['answer'], $q['options'], true);
                if (! $valid) {
                    $validator->errors()->add('configuration', 'Each answer must match the supplied options. Ordered answers use | between every option.');
                }
            }
        }];
    }
}
