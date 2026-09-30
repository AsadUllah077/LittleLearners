<x-layout :title="$category->name" :child="$child"><a href="{{ route('child.dashboard',$child) }}" class="back-link">← My learning</a><div class="section-heading"><div><span class="eyebrow">LET'S DISCOVER</span><h1>{{ $category->icon }} {{ $category->name }}</h1><p>{{ $category->description }}</p></div></div><div class="profile-grid">
@forelse($lessons as $lesson)<article class="panel"><span class="pill">{{ $lesson->items_count }} little discoveries</span><h2>{{ $lesson->title }}</h2><p>{{ $lesson->description }}</p><a class="button primary" href="{{ route('child.lesson',[$child,$lesson]) }}">Start learning →</a><div class="activity-links">
@foreach($lesson->activities as $activity)<a href="{{ $activity->activity_type==='tracing'?route('child.lesson',[$child,$lesson]).'#tracing':route('child.activity',[$child,$activity]) }}">{{ $activity->activity_type==='tracing'?'✏️':'🎲' }} {{ $activity->title }} →</a>
@endforeach</div></article>
@empty<p>New lessons are coming soon.</p>
@endforelse</div></x-layout>
