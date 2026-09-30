<?php

namespace Database\Seeders;

use App\Models\LearningActivity;
use App\Models\LearningCategory;
use App\Models\LearningItem;
use App\Models\Lesson;
use App\Models\Reward;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $alphabet = explode('|', 'Apple|Ball|Cat|Dog|Elephant|Fish|Grapes|Hat|Ice cream|Juice|Kite|Lion|Moon|Nest|Orange|Penguin|Queen|Rabbit|Sun|Tree|Umbrella|Van|Watermelon|Xylophone|Yo-yo|Zebra');
        $pictures = explode('|', '🍎|⚽|🐱|🐶|🐘|🐟|🍇|🎩|🍦|🧃|🪁|🦁|🌙|🪺|🍊|🐧|👑|🐰|☀️|🌳|☂️|🚐|🍉|🎵|🪀|🦓');
        $catalog = [
            ['English Alphabet', 'english-alphabet', 'ABC', range('A', 'Z')],
            ['Numbers', 'numbers', '123', range(1, 100)],
            ['Colors', 'colors', '🎨', explode('|', 'Red|Blue|Green|Yellow|Orange|Purple|Pink|Black|White|Brown|Gray')],
            ['Shapes', 'shapes', '🔷', explode('|', 'Circle|Square|Triangle|Rectangle|Oval|Star|Heart|Diamond|Pentagon|Hexagon')],
            ['Days of the Week', 'days-of-the-week', '📅', explode('|', 'Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday')],
            ['Months of the Year', 'months-of-the-year', '🌤️', explode('|', 'January|February|March|April|May|June|July|August|September|October|November|December')],
            ['Animals', 'animals', '🦁', explode('|', 'Cat|Dog|Cow|Goat|Lion|Tiger|Elephant|Monkey|Horse|Sheep|Rabbit|Bird|Fish')],
            ['Fruits', 'fruits', '🍎', explode('|', 'Apple|Banana|Orange|Mango|Grapes|Watermelon|Strawberry|Pineapple|Peach|Pear')],
            ['Vegetables', 'vegetables', '🥕', explode('|', 'Potato|Tomato|Carrot|Onion|Cucumber|Spinach|Cabbage|Peas|Pumpkin|Cauliflower')],
            ['Body Parts', 'body-parts', '🖐️', explode('|', 'Head|Eyes|Ears|Nose|Mouth|Hands|Fingers|Arms|Legs|Feet|Knees|Shoulders')],
            ['Family Members', 'family-members', '🏡', explode('|', 'Father|Mother|Brother|Sister|Grandfather|Grandmother|Baby')],
            ['Basic Words', 'basic-words', '💬', explode('|', 'Hello|Goodbye|Please|Thank you|Yes|No|Happy|Sad|Big|Small|Up|Down')],
        ];
        $emoji = ['Cat' => '🐱', 'Dog' => '🐶', 'Cow' => '🐮', 'Goat' => '🐐', 'Lion' => '🦁', 'Tiger' => '🐯', 'Elephant' => '🐘', 'Monkey' => '🐵', 'Horse' => '🐴', 'Sheep' => '🐑', 'Rabbit' => '🐰', 'Bird' => '🐦', 'Fish' => '🐟', 'Apple' => '🍎', 'Banana' => '🍌', 'Orange' => '🍊', 'Mango' => '🥭', 'Grapes' => '🍇', 'Watermelon' => '🍉', 'Strawberry' => '🍓', 'Pineapple' => '🍍', 'Peach' => '🍑', 'Pear' => '🍐', 'Potato' => '🥔', 'Tomato' => '🍅', 'Carrot' => '🥕', 'Onion' => '🧅', 'Cucumber' => '🥒', 'Spinach' => '🥬', 'Cabbage' => '🥬', 'Peas' => '🫛', 'Pumpkin' => '🎃', 'Cauliflower' => '🥦', 'Father' => '👨', 'Mother' => '👩', 'Brother' => '👦', 'Sister' => '👧', 'Grandfather' => '👴', 'Grandmother' => '👵', 'Baby' => '👶', 'Head' => '🙂', 'Eyes' => '👀', 'Ears' => '👂', 'Nose' => '👃', 'Mouth' => '👄', 'Hands' => '🖐️', 'Fingers' => '☝️', 'Arms' => '💪', 'Legs' => '🦵', 'Feet' => '🦶', 'Knees' => '🦵', 'Shoulders' => '🧍'];
        foreach ($catalog as $order => [$name,$slug,$icon,$values]) {
            $category = LearningCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => 'Listen, explore, and play with '.strtolower($name).'.', 'icon' => $icon, 'sort_order' => $order + 1, 'is_active' => true]);
            if (str_contains($category->icon ?? '', 'ð') || str_contains($category->description ?? '', '1 to 9')) {
                $category->update(['icon' => $icon, 'description' => 'Listen, explore, and play with '.strtolower($name).'.']);
            }
            $lesson = Lesson::firstOrCreate(['slug' => $slug.'-lesson'], ['learning_category_id' => $category->id, 'title' => $name.' Adventure', 'description' => 'Take your time. Every little discovery counts!', 'content_type' => 'learning', 'sort_order' => 1, 'age_min' => 1, 'age_max' => 5, 'is_active' => true]);
            foreach ($values as $i => $value) {
                $value = (string) $value;
                $title = $value;
                $example = $emoji[$value] ?? $value;
                $description = 'Listen and say '.$value.'.';
                if ($slug === 'english-alphabet') {
                    $example = $pictures[$i];
                    $description = $value.' is for '.$alphabet[$i];
                }
                if ($slug === 'numbers') {
                    $description = $this->number((int) $value);
                    $example = $description;
                }
                $examples = ['Circle' => 'a wheel', 'Square' => 'a window', 'Triangle' => 'a slice of pizza', 'Rectangle' => 'a door', 'Oval' => 'an egg', 'Star' => 'a star in the sky', 'Heart' => 'a heart on a card', 'Diamond' => 'a kite', 'Pentagon' => 'a five-sided sign', 'Hexagon' => 'a honeycomb cell', 'Red' => 'an apple', 'Blue' => 'the sky', 'Green' => 'a leaf', 'Yellow' => 'a banana', 'Orange' => 'an orange', 'Purple' => 'grapes', 'Pink' => 'a flower', 'Black' => 'a night sky', 'White' => 'snow', 'Brown' => 'a teddy bear', 'Gray' => 'an elephant'];
                if (in_array($slug, ['colors', 'shapes'])) {
                    $description = $value.', like '.($examples[$value] ?? 'a familiar object').'.';
                }
                LearningItem::updateOrCreate(['lesson_id' => $lesson->id, 'title' => $title], ['display_value' => $value, 'lowercase_value' => strtolower($value), 'description' => $description, 'example_text' => $example, 'sort_order' => $i + 1]);
            }
            foreach (['matching', 'listen'] as $type) {
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'activity_type' => $type], ['title' => $type === 'listen' ? 'Listen and find' : 'Picture match', 'instructions' => 'Tap the matching answer.', 'difficulty' => 'easy', 'is_active' => true]);
            }
            if (in_array($slug, ['english-alphabet', 'numbers', 'shapes'])) {
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'activity_type' => 'tracing'], ['title' => 'Tracing time', 'instructions' => 'Follow the dotted guide with your finger.', 'difficulty' => 'easy', 'is_active' => true]);
            }
            if (in_array($slug, ['english-alphabet', 'numbers', 'days-of-the-week', 'months-of-the-year'])) {
                $ordered = array_map('strval', array_slice($values, 0, 5));
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'activity_type' => 'ordering'], ['title' => 'Put them in order', 'instructions' => 'Tap each card in the right order.', 'difficulty' => 'medium', 'configuration' => ['questions' => [['prompt' => 'What is the correct order?', 'mode' => 'ordering', 'options' => $ordered, 'answer' => implode('|', $ordered)]]], 'is_active' => true]);
            }
            if ($slug === 'numbers') {
                foreach (['counting', 'missing', 'identification'] as $type) {
                    LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'activity_type' => $type], ['title' => ucfirst($type).' numbers', 'instructions' => 'Look carefully and choose a number.', 'difficulty' => 'easy', 'is_active' => true]);
                }
                $questions = [];
                for ($n = 1; $n <= 5; $n++) {
                    $questions[] = ['prompt' => 'Find the digit for '.$this->number($n), 'mode' => 'choice', 'options' => ['1', '2', '3', '4', '5'], 'answer' => (string) $n];
                }
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'title' => 'Digits and names'], ['activity_type' => 'matching', 'instructions' => 'Match the name to its digit.', 'difficulty' => 'easy', 'configuration' => ['questions' => $questions], 'is_active' => true]);
            }
            if ($slug === 'english-alphabet') {
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'activity_type' => 'lowercase'], ['title' => 'Big and little letters', 'instructions' => 'Find the matching little letter.', 'difficulty' => 'medium', 'is_active' => true]);
            }
            if (in_array($slug, ['days-of-the-week', 'months-of-the-year'])) {
                $questions = [];
                foreach (array_slice($values, 0, 5) as $i => $v) {
                    $questions[] = ['prompt' => 'What comes after '.$v.'?', 'mode' => 'choice', 'options' => array_values(array_slice($values, 0, 7)), 'answer' => $values[$i + 1]];
                }
                LearningActivity::firstOrCreate(['lesson_id' => $lesson->id, 'title' => 'What comes next?'], ['activity_type' => 'matching', 'instructions' => 'Think about the calendar.', 'difficulty' => 'medium', 'configuration' => ['questions' => $questions], 'is_active' => true]);
            }
        }
        foreach (['First Lesson' => 1, 'Curious Explorer' => 5, 'Practice Pal' => 10, 'Bright Thinker' => 20, 'Super Learner' => 40] as $name => $stars) {
            Reward::firstOrCreate(['name' => $name], ['description' => 'Celebrate '.$stars.' learning stars!', 'icon' => '⭐', 'star_requirement' => $stars]);
        }
    }

    private function number(int $n): string
    {
        $small = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        return $n < 20 ? $small[$n] : ($n === 100 ? 'One hundred' : $tens[intdiv($n, 10)].($n % 10 ? '-'.strtolower($small[$n % 10]) : ''));
    }
}
