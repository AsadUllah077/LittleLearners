<?php

namespace App\Services;

use App\Models\LearningActivity;

class GameService
{
    public function questions(LearningActivity $activity, string $level = 'level-2'): array
    {
        $configured = $activity->configuration['questions'] ?? [];
        if ($configured) {
            return $configured;
        }
        $items = $activity->lesson->items()->orderBy('sort_order')->get();
        $category = $activity->lesson->category->slug;
        if ($category === 'numbers') {
            $items = $items->filter(fn ($item) => (int) $item->display_value <= match ($level) {
                'level-1' => 5,'level-2' => 20,default => 100
            });
        }
        if ($activity->activity_type === 'counting') {
            $items = $items->filter(fn ($item) => (int) $item->display_value <= 20);
        }
        if ($items->isEmpty()) {
            return [];
        }

        return $items->shuffle()->take($level === 'level-1' ? 3 : 5)->map(function ($item) use ($items, $activity, $category, $level) {
            $options = $items->where('id', '!=', $item->id)->shuffle()->take($level === 'level-1' ? 1 : 3)->pluck('title')->push($item->title)->shuffle()->values()->all();
            $type = $activity->activity_type;
            $prompt = match ($type) {
                'counting' => 'How many dots can you count?',
                'missing' => 'What comes next? '.max(0, (int) $item->display_value - 1).', ...',
                'lowercase' => 'Find the lowercase letter for '.$item->display_value,
                'listen' => 'Listen and choose.',
                default => match ($category) {
                    'english-alphabet' => 'Which letter starts '.(explode(' is for ', $item->description ?? '')[1] ?? $item->title).'?', 'colors' => 'What color is this?', 'shapes' => 'What shape is this?', 'numbers' => 'Find the name for '.$item->display_value, default => 'What do you see?'
                },
            };
            if ($type === 'lowercase') {
                $options = array_map('strtolower', $options);
            }
            $visual = $type === 'counting' ? str_repeat('● ', min(20, (int) $item->display_value)) : ($item->example_text ?? $item->display_value);
            if ($type === 'missing') {
                $visual = '🔎';
            }

            return ['prompt' => $prompt, 'speak' => $category === 'numbers' ? $item->description : $item->title, 'visual' => $visual, 'image' => $item->image, 'visual_type' => in_array($category, ['colors', 'shapes']) ? $category : 'text', 'visual_value' => $item->display_value, 'options' => $options, 'answer' => $type === 'lowercase' ? strtolower($item->title) : $item->title, 'mode' => $type === 'listen' ? 'listen' : 'choice'];
        })->values()->all();
    }
}
