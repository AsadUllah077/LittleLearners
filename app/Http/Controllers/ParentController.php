<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChildRequest;
use App\Http\Requests\UpdateChildRequest;
use App\Models\ChildProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ParentController extends Controller
{
    public function dashboard()
    {
        $children = Auth::user()->children()->withSum('stars', 'stars')->withCount(['progresses' => fn ($q) => $q->where('status', 'completed')])->with(['streak', 'progresses' => fn ($q) => $q->with('lesson')->latest('updated_at')->limit(5)])->latest()->get();

        return view('parent.dashboard', compact('children'));
    }

    public function createChild()
    {
        return view('parent.child-form', ['child' => new ChildProfile]);
    }

    public function storeChild(StoreChildRequest $request)
    {
        $data = $request->safe()->except('photo');
        if (empty($data['avatar'])) {
            unset($data['avatar']);
        }
        if ($request->hasFile('photo')) {
            $data['avatar'] = $request->file('photo')->store('avatars', 'public');
        }
        $request->user()->children()->create($data);

        return redirect()->route('parent.dashboard')->with('success', 'Your child is ready to explore!');
    }

    public function edit(ChildProfile $child)
    {
        Gate::authorize('update', $child);

        return view('parent.child-form', compact('child'));
    }

    public function update(UpdateChildRequest $request, ChildProfile $child)
    {
        $data = $request->safe()->except('photo');
        if (empty($data['avatar'])) {
            unset($data['avatar']);
        }
        if ($request->hasFile('photo')) {
            $data['avatar'] = $request->file('photo')->store('avatars', 'public');
        }
        $child->update($data);

        return redirect()->route('parent.dashboard')->with('success', 'Profile updated.');
    }

    public function progress(ChildProfile $child)
    {
        Gate::authorize('view', $child);
        $child->load(['streak', 'earnedRewards.reward'])->loadSum('stars', 'stars');
        $progresses = $child->progresses()->with('lesson')->latest('updated_at')->paginate(15);
        $attempts = $child->attempts()->with('activity')->latest()->limit(10)->get();

        return view('parent.progress', compact('child', 'progresses', 'attempts'));
    }

    public function reset(Request $request, ChildProfile $child)
    {
        Gate::authorize('update', $child);
        $request->validate(['password' => 'required|current_password']);
        DB::transaction(function () use ($child) {
            ChildProfile::lockForUpdate()->findOrFail($child->id);
            foreach (['progresses', 'stars', 'attempts', 'earnedRewards', 'streak'] as $relation) {
                $child->$relation()->delete();
            }
        });

        return back()->with('success', 'Learning progress reset.');
    }

    public function destroy(Request $request, ChildProfile $child)
    {
        Gate::authorize('delete', $child);
        $request->validate(['password' => 'required|current_password']);
        $child->delete();

        return redirect()->route('parent.dashboard')->with('success', 'Child profile deleted.');
    }
}
