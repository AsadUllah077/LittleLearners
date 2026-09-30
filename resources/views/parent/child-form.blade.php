<x-layout title="Child profile"><div class="form-panel panel"><span class="eyebrow">YOUR LITTLE LEARNER</span><h1>{{ $child->exists?'Edit profile':'Meet your little learner' }}</h1><form method="post" enctype="multipart/form-data" action="{{ $child->exists?route('parent.children.update',$child):route('parent.children.store') }}">
@csrf @if($child->exists)
@method('put')
@endif<label>Name<input name="name" required maxlength="100" value="{{ old('name',$child->name) }}"></label><label>Date of birth<input type="date" name="date_of_birth" max="{{ today()->toDateString() }}" value="{{ old('date_of_birth',$child->date_of_birth?->toDateString()) }}"></label><label>Learning level<select name="learning_level">
@foreach(['level-1'=>'First discoveries · Ages 1–2','level-2'=>'Curious explorers · Ages 3–4','level-3'=>'Growing thinkers · Ages 4–5'] as $value=>$label)<option value="{{ $value }}" @selected(old('learning_level',$child->learning_level)===$value)>{{ $label }}</option>
@endforeach</select></label><p class="subtle">Levels suggest a starting point. Every category is always available.</p><label>Choose a friend<select name="avatar"><option value="">Keep current avatar</option>
@foreach(['fox'=>'🦊 Fox','bear'=>'🐻 Bear','rabbit'=>'🐰 Rabbit','cat'=>'🐱 Cat'] as $value=>$label)<option value="{{ $value }}" @selected(old('avatar',$child->avatar)===$value)>{{ $label }}</option>
@endforeach</select></label><label>Or upload a profile photo<input type="file" name="photo" accept="image/png,image/jpeg,image/webp"></label><button class="button primary">Save profile</button><a class="button secondary" href="{{ route('parent.dashboard') }}">Back</a></form>
@if($child->exists)<details class="danger-zone"><summary>Delete this profile</summary><form method="post" action="{{ route('parent.children.destroy',$child) }}" data-confirm="Delete this child's profile and all progress?">
@csrf @method('delete')<label>Confirm your password<input type="password" name="password" required autocomplete="current-password"></label><button class="button danger">Delete profile permanently</button></form></details>
@endif</div></x-layout>
