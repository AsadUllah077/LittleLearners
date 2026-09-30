<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Child</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page">
        <div class="auth-card wider">
            <h1>Add a Child Profile</h1>
            <form method="POST" action="/parent/children">
                @csrf
                <div class="field">
                    <label for="name">Child name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="date_of_birth">Date of birth</label>
                    <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}">
                    @error('date_of_birth') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="learning_level">Learning level</label>
                    <select id="learning_level" name="learning_level">
                        <option value="level-1">Level 1</option>
                        <option value="level-2">Level 2</option>
                        <option value="level-3">Level 3</option>
                    </select>
                    @error('learning_level') <span class="error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="button primary wide">Save Child</button>
            </form>
        </div>
    </body>
</html>
