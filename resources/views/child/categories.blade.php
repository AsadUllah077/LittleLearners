<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Categories</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-page">
        <main class="content-panel full-width">
            <header class="panel-header">
                <div>
                    <p class="eyebrow">Learning categories</p>
                    <h1>{{ $child->name }} can explore</h1>
                </div>
            </header>

            <section class="category-grid">
                @foreach($categories as $category)
                    <div class="category-card">
                        <div class="category-icon">{{ $category->icon ?? '✨' }}</div>
                        <h3>{{ $category->name }}</h3>
                        <a href="/learn/{{ $child->id }}/categories/{{ $category->id }}" class="button primary">Open</a>
                    </div>
                @endforeach
            </section>
        </main>
    </body>
</html>
