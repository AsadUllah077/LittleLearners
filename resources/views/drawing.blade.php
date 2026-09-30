<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sketch Board</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell">
            <header class="toolbar">
                <div>
                    <p class="eyebrow">English Learning Board</p>
                    <h1>Sketch Board</h1>
                </div>

                <div class="controls">
                    <label>
                        Color
                        <input id="colorPicker" type="color" value="#1d4ed8" aria-label="Brush color">
                    </label>

                    <label>
                        Brush
                        <input id="brushSize" type="range" min="2" max="40" value="6" aria-label="Brush size">
                    </label>

                    <button id="clearCanvas" type="button">Clear</button>
                </div>
            </header>

            <main class="canvas-panel">
                <canvas id="drawingCanvas" width="960" height="560" aria-label="Drawing area"></canvas>
            </main>
        </div>
    </body>
</html>
