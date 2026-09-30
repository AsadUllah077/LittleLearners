@props(['category','child'=>null,'percent'=>0])
<article class="learning-card tone-{{ $category->sort_order % 6 }}"><div class="category-art">
@if($category->image)<img src="{{ Storage::url($category->image) }}" alt="">
@else<span>{{ $category->icon }}</span>
@endif<span class="art-spark">✦</span></div><div class="card-body"><h3>{{ $category->name }}</h3><p>{{ $category->description }}</p>
@if($child)<div class="progress-caption"><span>Little steps, big discoveries</span><span>{{ $percent }}%</span></div><progress value="{{ $percent }}" max="100">{{ $percent }}%</progress>
@endif<a class="card-link" href="{{ $child ? route('child.category',[$child,$category]) : route('register') }}">Start learning <span aria-hidden="true">→</span></a></div></article>
