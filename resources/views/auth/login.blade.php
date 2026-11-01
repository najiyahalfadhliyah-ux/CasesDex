<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Arsip Kasus</title>
</head>
<body>
    <main>
        <h1>Login</h1>

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div>
                <label for="login">Email atau Username</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus>
                @error('login')
                    <p style="color:red">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password">Kata Sandi</label>
                <input id="password" name="password" type="password" required>
            </div>

            <button type="submit">Masuk</button>
        </form>
    </main>
</body>
</html>