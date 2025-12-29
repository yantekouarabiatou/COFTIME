<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription</title>

    <!-- Liens CORRECTS pour Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            color: #070920;
            overflow-x: hidden;
            height: 100%;
            background-color: #B0BEC5;
            background-repeat: no-repeat;
        }

        .card0 {
            box-shadow: 0px 4px 8px 0px #757575;
            border-radius: 0px;
        }

        .logo {
            margin: 5px;
            height: auto;
            max-width: 280px;
            width: auto;
            image-rendering: -webkit-optimize-contrast;
        }

        .image {
            width: 360px;
            height: 280px;
        }

        .text-sm {
            font-size: 14px !important;
        }

        .btn-blue {
            background-color: #1A237E;
            width: 100%;
            color: #fff;
            border-radius: 2px;
            padding: 12px;
        }

        .btn-blue:hover {
            background-color: #9c9797;
        }

        .bg-blue {
            color: #fff;
            background-color: #1A237E;
        }

        input,
        select {
            padding: 10px 12px;
            border: 1px solid lightgrey;
            border-radius: 2px;
            margin-bottom: 15px;
            width: 100%;
            box-sizing: border-box;
            color: #2C3E50;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border: 1px solid #304FFE;
            outline: none;
        }

        @media (max-width: 991px) {
            .logo {
                margin-left: 0;
            }

            .image {
                width: 300px;
                height: 220px;
            }
        }
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
                            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="Logo Cofima"
                                class="logo img-fluid">
                        </div>
                        <div class="row px-3 justify-content-center mt-4 mb-5">
                            <img src="{{ asset('assets/img/uNGdWHi.png') }}" class="image">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card2 card border-0 px-4 py-5">

                        <div class="row mt-4 px-3 text-center">
                            <h2 style="color: #1A237E; font-size: 2rem;"><strong>Inscription</strong></h2>
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

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="row px-3">
                                <div class="col-md-6">
                                    <label class="mb-1">
                                        <h6 class="mb-0 text-sm">Nom</h6>
                                    </label>
                                    <input type="text" name="nom" value="{{ old('nom') }}"
                                        class="form-control @error('nom') is-invalid @enderror" required autofocus>
                                    @error('nom') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="mb-1">
                                        <h6 class="mb-0 text-sm">Prénom</h6>
                                    </label>
                                    <input type="text" name="prenom" value="{{ old('prenom') }}"
                                        class="form-control @error('prenom') is-invalid @enderror" required>
                                    @error('prenom') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Nom d'utilisateur</h6>
                                </label>
                                <input type="text" name="username" value="{{ old('username') }}"
                                    class="form-control @error('username') is-invalid @enderror" required>
                                @error('username') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Email</h6>
                                </label>
                                <input type="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" required
                                    autocomplete="username">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Poste</h6>
                                </label>
                                <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
                                    <option value="">-- Choisir un poste --</option>
                                    @foreach(\App\Models\Poste::all() as $poste)
                                        <option value="{{ $poste->id }}" {{ old('poste_id') == $poste->id ? 'selected' : '' }}>
                                            {{ $poste->libelle ?? $poste->intitule }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('poste_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Mot de passe</h6>
                                </label>
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required
                                    autocomplete="new-password">
                                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Confirmer le mot de passe</h6>
                                </label>
                                <input type="password" name="password_confirmation" class="form-control" required
                                    autocomplete="new-password">
                            </div>

                            <div class="row px-3 mt-4">
                                <button type="submit" class="btn btn-blue">Créer mon compte</button>
                            </div>

                            <div class="row px-3 mt-3 text-center">
                                <small class="text-sm">
                                    Déjà un compte ? <a href="{{ route('login') }}" class="text-decoration-underline">Se
                                        connecter</a>
                                </small>
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

    <!-- Scripts JavaScript à la fin du body -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
