<x-layout title="Edit content"><a class="back-link" href="{{ route('admin.index',$resource) }}">← {{ ucfirst($resource) }}</a><div class="panel editor-panel"><h1>{{ $record->exists?'Edit':'Create' }} {{ str_replace('-',' ',$resource) }}</h1><form method="post" enctype="multipart/form-data" action="{{ $record->exists?route('admin.update',[$resource,$record->id]):route('admin.store',$resource) }}">
@csrf @if($record->exists)
@method('put')
@endif
@php
$fields=match($resource){'categories'=>['name','slug','description','icon','sort_order'],'lessons'=>['title','slug','description','sort_order','age_min','age_max'],'learning-items'=>['title','display_value','lowercase_value','description','example_text','sort_order'],'activities','games'=>['title','instructions'],'users'=>['name','email'],'children'=>['name','date_of_birth'],default=>[]};
@endphp
@foreach($fields as $field)<label>{{ ucfirst(str_replace('_',' ',$field)) }}
@if(in_array($field,['description','instructions']))<textarea name="{{ $field }}" rows="3">{{ old($field,$record->$field) }}</textarea>
@else<input name="{{ $field }}" type="{{ in_array($field,['sort_order','age_min','age_max'])?'number':($field==='date_of_birth'?'date':($field==='email'?'email':'text')) }}" value="{{ old($field,$field==='date_of_birth'?$record->date_of_birth?->toDateString():($record->$field??(in_array($field,['sort_order','age_min','age_max'])?1:''))) }}">
@endif</label>
@endforeach
@if($resource==='lessons')<input type="hidden" name="content_type" value="learning"><label>Category<select name="learning_category_id">
@foreach($categories as $id=>$name)<option value="{{ $id }}" @selected(old('learning_category_id',$record->learning_category_id)==$id)>{{ $name }}</option>
@endforeach</select></label>
@endif
@if(in_array($resource,['learning-items','activities','games']))<label>Lesson<select name="lesson_id">
@foreach($lessons as $id=>$name)<option value="{{ $id }}" @selected(old('lesson_id',$record->lesson_id)==$id)>{{ $name }}</option>
@endforeach</select></label>
@endif
@if(in_array($resource,['activities','games']))<label>Game type<select name="activity_type">
@foreach(['matching','listen','counting','ordering','missing','lowercase','tracing','identification'] as $type)<option @selected(old('activity_type',$record->activity_type)===$type)>{{ $type }}</option>
@endforeach</select></label><label>Difficulty<select name="difficulty">
@foreach(['easy','medium','advanced'] as $value)<option @selected(old('difficulty',$record->difficulty)===$value)>{{ $value }}</option>
@endforeach</select></label><fieldset id="questionEditor"><legend>Questions</legend><p>Leave empty to generate questions from the lesson items.</p><div id="questionRows"></div><button type="button" id="addQuestion" class="button secondary">+ Add question</button><input type="hidden" name="configuration" id="questionConfiguration" value="{{ is_array(old('configuration'))?json_encode(old('configuration')):old('configuration',json_encode($record->configuration??[])) }}"><label>Optional first question image<input type="file" name="question_image" accept="image/png,image/jpeg,image/webp"></label></fieldset>
@endif
@if($resource==='children')<label>Learning level<select name="learning_level">
@foreach(['level-1','level-2','level-3'] as $value)<option @selected(old('learning_level',$record->learning_level)===$value)>{{ $value }}</option>
@endforeach</select></label><h3>Progress</h3>
@foreach($record->progresses()->with('lesson')->get() as $progress)<p>{{ $progress->lesson?->title }} · {{ $progress->completion_percentage }}%</p>
@endforeach @endif
@if(in_array($resource,['categories','lessons','learning-items']))<label>Image (PNG, JPEG, WebP; max 4 MB)<input type="file" name="image" accept="image/png,image/jpeg,image/webp"></label>
@if($record->image)<img class="media-preview" src="{{ Storage::url($record->image) }}" alt="Current image">
@endif @if($resource!=='categories')<label>Pronunciation audio (max 10 MB)<input type="file" name="audio" accept="audio/*"></label>
@if($record->audio)<audio controls src="{{ Storage::url($record->audio) }}"></audio>
@endif @endif @endif
@unless(in_array($resource,['learning-items','children']))<label>Status<select name="is_active"><option value="1" @selected(old('is_active',$record->is_active??1)==1)>Active</option><option value="0" @selected(old('is_active',$record->is_active??1)==0)>Inactive</option></select></label>
@endunless<button class="button primary">Save changes</button></form></div></x-layout>
