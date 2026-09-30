<?php

namespace App\Http\Controllers;

use App\Models\ChildProfile;
use App\Models\LearningActivity;
use App\Services\GameService;
use App\Services\LearningProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
    private function available(LearningActivity $activity): void
    {
        abort_unless($activity->is_active && $activity->lesson?->is_active && $activity->lesson->category?->is_active, 404);
    }

    public function show(Request $request, ChildProfile $child, LearningActivity $activity, GameService $games)
    {
        $this->available($activity);
        if ($activity->activity_type === 'tracing') {
            return redirect()->route('child.lesson', [$child, $activity->lesson]);
        }
        $questions = $games->questions($activity, $child->learning_level);
        abort_if(count($questions) === 0, 404, 'Add learning items to this activity first.');
        $token = (string) Str::uuid();
        $request->session()->put('game.'.$token, ['child' => $child->id, 'activity' => $activity->id, 'questions' => $questions, 'at' => time()]);
        $publicQuestions = array_map(fn ($q) => array_diff_key($q, ['answer' => true]), $questions);

        return view('games.play', compact('child', 'activity', 'token', 'publicQuestions'));
    }

    public function check(Request $request, ChildProfile $child, LearningActivity $activity)
    {
        $this->available($activity);
        $data = $request->validate(['token' => 'required|uuid', 'index' => 'required|integer|min:0', 'answer' => 'required|string|max:1000']);
        $game = $request->session()->get('game.'.$data['token']);
        abort_unless($game && $game['child'] === $child->id && $game['activity'] === $activity->id && time() - $game['at'] < 7200, 419);
        $question = $game['questions'][$data['index']] ?? null;
        abort_unless($question, 422);

        return response()->json(['correct' => $data['answer'] === (string) $question['answer']]);
    }

    public function complete(Request $request, ChildProfile $child, LearningActivity $activity, LearningProgressService $progress)
    {
        $this->available($activity);
        if ($activity->activity_type === 'tracing') {
            $data = $request->validate(['item_id' => 'required|integer', 'points' => 'required|array|min:8|max:5000', 'points.*' => 'required|array|size:2', 'points.*.*' => 'required|numeric|between:0,1']);
            abort_unless($activity->lesson->items()->whereKey($data['item_id'])->exists(), 422);
            $distance = 0;
            foreach ($data['points'] as $i => $p) {
                if ($i) {
                    $distance += hypot($p[0] - $data['points'][$i - 1][0], $p[1] - $data['points'][$i - 1][1]);
                }
            }
            abort_if($distance < 0.15, 422, 'Draw a little more before finishing.');
            $attempt = $progress->attempt($child, $activity, 1, 1);

            return response()->json(['score' => $attempt->score, 'message' => 'Your practice star is saved!']);
        }
        $data = $request->validate(['token' => 'required|uuid', 'answers' => 'required|array|max:30', 'answers.*' => 'required|string|max:1000']);
        $game = $request->session()->get('game.'.$data['token']);
        abort_unless($game && $game['child'] === $child->id && $game['activity'] === $activity->id && time() - $game['at'] < 7200, 419);
        abort_unless(count($data['answers']) === count($game['questions']), 422);
        $correct = 0;
        foreach ($game['questions'] as $i => $q) {
            if (($data['answers'][$i] ?? null) === (string) $q['answer']) {
                $correct++;
            }
        }
        $attempt = $progress->attempt($child, $activity, $correct, count($game['questions']));
        $request->session()->forget('game.'.$data['token']);

        return response()->json(['score' => $attempt->score, 'correct' => $correct, 'total' => count($game['questions']), 'message' => 'Well done for practicing!']);
    }
}
