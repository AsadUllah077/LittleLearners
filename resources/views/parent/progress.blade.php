<x-layout title="Learning progress" :child="$child"><div class="section-heading"><div><span class="eyebrow">EVERY STEP COUNTS</span><h1>{{ $child->name }}'s discoveries</h1></div><a href="{{ route('parent.dashboard') }}" class="button secondary">Parent corner</a></div><div class="stats-grid"><div class="stat-box"><span>Learning stars</span><strong>★ {{ $child->stars_sum_stars??0 }}</strong></div><div class="stat-box"><span>Current streak</span><strong>{{ $child->current_streak }} days</strong></div><div class="stat-box"><span>Longest streak</span><strong>{{ $child->streak?->longest_streak??0 }} days</strong></div></div><h2>Little achievements</h2><div class="badge-row">
@forelse($child->earnedRewards as $earned)<div class="badge-card">⭐<strong>{{ $earned->reward?->name }}</strong><small>{{ $earned->earned_at?->format('M j, Y') }}</small></div>
@empty<p>Your first star is waiting. Try a lesson together!</p>
@endforelse</div><h2>Lessons</h2><div class="panel">
@forelse($progresses as $progress)<div class="progress-row"><a href="{{ route('child.lesson',[$child,$progress->lesson]) }}">{{ $progress->lesson->title }}</a><progress max="100" value="{{ $progress->completion_percentage }}"></progress><span>{{ $progress->completion_percentage }}%</span></div>
@empty<p>No lessons completed yet.</p>
@endforelse{{ $progresses->links() }}</div><h2>Recent practice</h2><div class="panel">
@forelse($attempts as $attempt)<p>{{ $attempt->activity?->title }} · {{ $attempt->correct_answers }}/{{ $attempt->total_questions }} · {{ $attempt->completed_at?->diffForHumans() }}</p>
@empty<p>Games and tracing activities will appear here.</p>
@endforelse</div><details class="danger-zone"><summary>Reset learning progress</summary><p>This clears lessons, stars, badges, and practice history.</p><form method="post" action="{{ route('parent.children.reset',$child) }}" data-confirm="Reset all learning progress for this child?">
@csrf @method('delete')<label>Your password<input type="password" name="password" required autocomplete="current-password"></label><button class="button danger">Reset progress</button></form></details></x-layout>
