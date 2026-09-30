<x-layout title="Admin studio"><span class="eyebrow">CONTENT & COMMUNITY</span><h1>Learning studio</h1><div class="admin-nav">
@foreach(['categories','lessons','learning-items','activities','games','users','children'] as $resource)<a class="button secondary" href="{{ route('admin.index',$resource) }}">{{ ucfirst(str_replace('-',' ',$resource)) }}</a>
@endforeach</div><div class="stats-grid">
@foreach($stats as $label=>$count)<div class="stat-box"><span>{{ $label }}</span><strong>{{ $count }}</strong></div>
@endforeach</div><div class="profile-grid"><section class="panel"><h2>Recent registrations</h2>
@foreach($recent as $user)<p>{{ $user->name }} · {{ $user->created_at->format('M j, Y') }}</p>
@endforeach</section><section class="panel"><h2>Popular learning categories</h2>
@foreach($popular as $category)<p>{{ $category->name }} · {{ $category->completed_progress_count }} completions · {{ $category->lessons_count }} lessons</p>
@endforeach</section></div></x-layout>
