<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { color: #070920; overflow-x: hidden; height: 100%; background-color: #B0BEC5; background-repeat: no-repeat; }
        .card0 { box-shadow: 0px 4px 8px 0px #757575; border-radius: 0px; }
        .logo { margin: 5px; height: auto; max-width: 280px; width: auto; image-rendering: -webkit-optimize-contrast; }
        .image { width: 360px; height: 280px; }
        .text-sm { font-size: 14px !important; }
        .btn-blue { background-color: #1A237E; width: 100%; color: #fff; border-radius: 2px; padding: 12px; }
        .btn-blue:hover { background-color: #9c9797; }
        .bg-blue { color: #fff; background-color: #1A237E; }
        input { padding: 10px 12px; border: 1px solid lightgrey; border-radius: 2px; margin-bottom: 15px; width: 100%; box-sizing: border-box; color: #2C3E50; font-size: 14px; }
        input:focus { border: 1px solid #304FFE; outline: none; }
        @media (max-width: 991px) { .logo { margin-left: 0; } .image { width: 300px; height: 220px; } }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid px-1 px-md-5 py-5 mx-auto">
    <div class="card card0 border-0">
        <div class="row d-flex">
            <div class="col-lg-6">
                <div class="card1 pb-5">
                    <div class="row justify-content-center justify-content-lg-start">
                        <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="Logo Cofima" class="logo img-fluid">
                    </div>
                    <div class="row px-3 justify-content-center mt-4 mb-5">
                        <img src="{{ asset('assets/img/uNGdWHi.png') }}" class="image">
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card2 card border-0 px-4 py-5">
                    <div class="row mt-4 px-3 text-center">
                        <h2 style="color: #1A237E; font-size: 2rem;"><strong>Mot de passe oublié</strong></h2>
                    </div>

                    <x-auth-session-status class="mt-4 text-center text-success" :status="session('status')" />

                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row px-3 mt-4">
                        <p class="text-sm text-muted text-center">
                            Pas de problème. Indiquez votre adresse email et nous vous enverrons un lien de réinitialisation.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="row px-3 mt-4">
                            <label class="mb-1"><h6 class="mb-0 text-sm">Adresse Email</h6></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Entrez votre email" required autofocus>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row px-3 mt-4">
                            <button type="submit" class="btn btn-blue">Envoyer le lien de réinitialisation</button>
                        </div>

                        <div class="row px-3 mt-3 text-center">
                            <a href="{{ route('login') }}" class="text-sm text-decoration-underline">← Retour à la connexion</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-blue py-4">
            <div class="row px-3">
                <small class="ml-4 ml-sm-5 mb-2">Copyright COFIMA © {{ date('Y') }}. Tous droits réservés.</small>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
