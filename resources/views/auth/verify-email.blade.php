<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification email</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { color: #070920; overflow-x: hidden; background-color: #B0BEC5; }
        .card0 { box-shadow: 0px 4px 8px 0px #757575; }
        .logo { max-width: 280px; image-rendering: -webkit-optimize-contrast; }
        .image { width: 360px; height: 280px; }
        .btn-blue { background-color: #1A237E; color: #fff; padding: 12px; border-radius: 2px; }
        .bg-blue { background-color: #1A237E; color: #fff; }
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
                        <h2 style="color: #1A237E; font-size: 2rem;"><strong>Vérifiez votre email</strong></h2>
                    </div>

                    <div class="row px-3 mt-4 text-center">
                        <p class="text-sm text-muted">
                            Merci pour votre inscription ! Un lien de vérification a été envoyé à votre adresse email.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-success mt-3">
                                Un nouveau lien de vérification a été envoyé.
                            </div>
                        @endif
                        </div>

                        <form method="POST" action="{{ route('verification.send') }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-blue w-100">Renvoyer l'email de vérification</button>
                        </form>

                        <div class="row px-3 mt-3 text-center">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="text-sm text-decoration-underline bg-transparent border-0">
                                    Se déconnecter
                                </button>
                            </form>
                        </div>
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
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
