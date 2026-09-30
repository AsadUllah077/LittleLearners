@props(['title' => 'Little Learners', 'child' => null])
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="speech-language" content="{{ config('learning.speech_language') }}"><title>{{ $title }} · Little Learners</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body><div class="app-shell"><aside class="app-sidebar"><a class="logo" href="/">✦ <span>little<span class="purple">learners</span><small>BIG DISCOVERIES START SMALL</small></span></a><p class="nav-label">LET'S EXPLORE</p><nav>
@if($child)<a href="{{ route('child.dashboard',$child) }}"><i data-lucide="house"></i> My learning</a><a href="{{ route('child.games',$child) }}"><i data-lucide="gamepad-2"></i> Play & discover</a><a href="{{ route('parent.children.progress',$child) }}"><i data-lucide="star"></i> My achievements</a>
@endif
@auth<a href="{{ route('parent.dashboard') }}"><i data-lucide="users"></i> Parent corner</a>
@if(auth()->user()->role==='admin')<a href="{{ route('admin.dashboard') }}"><i data-lucide="settings"></i> Admin studio</a>
@endif
@else<a href="/register"><i data-lucide="sparkles"></i> Get started</a><a href="/login"><i data-lucide="log-in"></i> Parent login</a>
@endauth</nav><div class="sidebar-note"><span>🌱</span><strong>A little learning,<br>every day.</strong><p>Small steps. Wonderful discoveries.</p></div>
@auth<form method="post" action="/logout">
@csrf<button class="text-button" type="submit">Sign out</button></form>
@endauth</aside>
<div class="app-main"><header class="topbar"><span>Made for little curious minds <span aria-hidden="true">☀️</span></span><div class="topbar-profile">
@if($child)<span class="star-pill">★ {{ $child->stars_sum_stars ?? $child->stars()->sum('stars') }}</span><span class="avatar-mini">{{ ['fox'=>'🦊','bear'=>'🐻','rabbit'=>'🐰','cat'=>'🐱'][$child->avatar] ?? '🌟' }}</span><strong>{{ $child->name }}</strong>
@else<span class="pill">Ages 1–5</span>
@auth<strong>{{ auth()->user()->name }}</strong>
@endauth
@endif</div></header>
<main class="page-content">
@if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>
@endif @if($errors->any())<div class="error-box" role="alert"><strong>Please check these details:</strong><ul>
@foreach($errors->all() as $error)<li>{{ $error }}</li>
@endforeach</ul></div>
@endif{{ $slot }}</main><footer class="app-footer">Made for discovery. A little joy in every lesson. <span>Little Learners © {{ date('Y') }}</span></footer></div></div><div id="toast" class="toast" role="status" hidden></div></body></html>
