<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentRequest;
use App\Models\ChildProfile;
use App\Models\ChildProgress;
use App\Models\LearningActivity;
use App\Models\LearningCategory;
use App\Models\LearningItem;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function model(string $resource): string
    {
        return match ($resource) {
            'categories' => LearningCategory::class,'lessons' => Lesson::class,'learning-items' => LearningItem::class,'activities','games' => LearningActivity::class,'users' => User::class,'children' => ChildProfile::class,default => abort(404)
        };
    }

    public function dashboard()
    {
        $stats = ['Parents' => User::where('role', 'parent')->count(), 'Children' => ChildProfile::count(), 'Lessons' => Lesson::count(), 'Completed lessons' => ChildProgress::where('status', 'completed')->count()];
        $recent = User::where('role', 'parent')->latest()->limit(8)->get();
        $popular = LearningCategory::withCount(['lessons', 'completedProgress'])->orderByDesc('completed_progress_count')->get();

        return view('admin.dashboard', compact('stats', 'recent', 'popular'));
    }

    public function index(Request $request, string $resource)
    {
        $class = $this->model($resource);
        $column = in_array($resource, ['categories', 'users', 'children']) ? 'name' : 'title';
        $query = $class::query();
        if ($resource === 'games') {
            $query->where('activity_type', '!=', 'tracing');
        }
        if ($request->filled('q')) {
            $query->where($column, 'like', '%'.mb_substr($request->string('q'), 0, 100).'%');
        }
        $records = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.index', compact('resource', 'records', 'column'));
    }

    private function form(string $resource, $record)
    {
        $categories = LearningCategory::orderBy('name')->pluck('name', 'id');
        $lessons = Lesson::orderBy('title')->pluck('title', 'id');

        return view('admin.form', compact('resource', 'record', 'categories', 'lessons'));
    }

    public function create(string $resource)
    {
        abort_if(in_array($resource, ['users', 'children']), 404);
        $class = $this->model($resource);

        return $this->form($resource, new $class);
    }

    public function edit(string $resource, int $id)
    {
        $class = $this->model($resource);

        return $this->form($resource, $class::findOrFail($id));
    }

    private function data(ContentRequest $request): array
    {
        $data = $request->validated();
        foreach (['image', 'audio', 'question_image'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('learning', 'public');
                if ($field === 'question_image') {
                    $data['configuration']['questions'][0]['image'] = $path;
                    unset($data['question_image']);
                } else {
                    $data[$field] = $path;
                }
            } else {
                unset($data[$field]);
            }
        }

        return $data;
    }

    public function store(ContentRequest $request, string $resource)
    {
        abort_if(in_array($resource, ['users', 'children']), 403);
        $class = $this->model($resource);
        $class::create($this->data($request));

        return redirect()->route('admin.index', $resource)->with('success', 'Content created.');
    }

    public function update(ContentRequest $request, string $resource, int $id)
    {
        $class = $this->model($resource);
        $record = $class::findOrFail($id);
        $data = $this->data($request);
        if ($resource === 'users') {
            abort_if($record->role === 'admin', 403, 'Administrator accounts are managed from the command line.');
            $record->is_active = (bool) $data['is_active'];
            unset($data['is_active']);
        }
        $record->fill($data)->save();

        return redirect()->route('admin.index', $resource)->with('success', 'Changes saved.');
    }

    public function destroy(string $resource, int $id)
    {
        abort_if(in_array($resource, ['users', 'children']), 403, 'Use account deactivation or parent profile management.');
        $class = $this->model($resource);
        $class::findOrFail($id)->delete();

        return back()->with('success', 'Content deleted.');
    }
}
