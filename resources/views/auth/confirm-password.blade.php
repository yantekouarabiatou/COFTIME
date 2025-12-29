<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmer le mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { color: #070920; overflow-x: hidden; height: 100%; background-color: #B0BEC5; }
        .card0 { box-shadow: 0px 4px 8px 0px #757575; }
        .logo { max-width: 280px; image-rendering: -webkit-optimize-contrast; }
        .image { width: 360px; height: 280px; }
        .btn-blue { background-color: #1A237E; width: 100%; color: #fff; padding: 12px; border-radius: 2px; }
        .btn-blue:hover { background-color: #9c9797; }
        .bg-blue { background-color: #1A237E; color: #fff; }
        input { padding: 10px 12px; border: 1px solid lightgrey; border-radius: 2px; margin-bottom: 15px; width: 100%; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid px-1 px-md-5 py-5 mx-auto">
    <div class="card card0 border-0">
        <div class="row d-flex">
            <div class="col-lg-6">
                <div class="card1 pb-5">
                    <div class="row justify-content-center">
                        <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" class="logo img-fluid">
                    </div>
                    <div class="row px-3 justify-content-center mt-4 mb-5">
                        <img src="{{ asset('assets/img/uNGdWHi.png') }}" class="image">
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card2 card border-0 px-4 py-5">
                    <div class="row mt-4 text-center">
                        <h2 style="color: #1A237E; font-size: 2rem;"><strong>Confirmation requise</strong></h2>
                    </div>

                    <div class="row px-3 mt-4">
                        <p class="text-sm text-center text-muted">
                            Cette zone est sécurisée. Veuillez confirmer votre mot de passe pour continuer.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="row px-3 mt-4">
                            <label class="mb-1"><h6 class="mb-0 text-sm">Mot de passe actuel</h6></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row px-3 mt-4">
                            <button type="submit" class="btn btn-blue">Confirmer</button>
                        </div>

                        <div class="row px-3 mt-3 text-center">
                            <a href="{{ route('dashboard') }}" class="text-sm text-decoration-underline">Annuler</a>
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
