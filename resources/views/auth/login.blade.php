<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />

    <!-- Bootstrap + Font Awesome (pour les icônes) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Ton CSS personnalisé (le même que dans ton exemple) -->

    <!-- Ton CSS personnalisé (le même que dans ton exemple) -->
    <style>
        /* Copie-colle exactement le CSS de ton exemple ici */
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

        .card2 {
            margin: 0px 40px;
        }

        .logo {
            margin: 5px;
            height: auto;
            max-width: 280px;
            /* ajuste selon ton design */
            width: auto;
            image-rendering: -webkit-optimize-contrast;
            /* Safari */
            image-rendering: crisp-edges;
            /* Pour certains cas */
        }

        .image {
            width: 360px;
            height: 280px;
            image-rendering: auto;
        }

        .border-line {
            border-right: 1px solid #EEEEEE;
        }


        .line {
            height: 1px;
            width: 45%;
            background-color: #E0E0E0;
            margin-top: 10px;
        }

        .or {
            width: 10%;
            font-weight: bold;
        }

        .text-sm {
            font-size: 14px !important;
        }

        ::placeholder {
            color: #BDBDBD;
            opacity: 1;
            font-weight: 300
        }

        :-ms-input-placeholder {
            color: #BDBDBD;
            font-weight: 300
        }

        ::-ms-input-placeholder {
            color: #BDBDBD;
            font-weight: 300
        }

        input,
        textarea {
            padding: 10px 12px 10px 12px;
            border: 1px solid lightgrey;
            border-radius: 2px;
            margin-bottom: 5px;
            margin-top: 2px;
            width: 100%;
            box-sizing: border-box;
            color: #2C3E50;
            font-size: 14px;
            letter-spacing: 1px;
        }

        input:focus,
        textarea:focus {
            -moz-box-shadow: none !important;
            -webkit-box-shadow: none !important;
            box-shadow: none !important;
            border: 1px solid #304FFE;
            outline-width: 0;
        }

        button:focus {
            -moz-box-shadow: none !important;
            -webkit-box-shadow: none !important;
            box-shadow: none !important;
            outline-width: 0;
        }

        a {
            color: inherit;
            cursor: pointer;
        }

        .btn-blue {
            background-color: #1A237E;
            width: 150px;
            color: #fff;
            border-radius: 2px;
        }

        .btn-blue:hover {
            background-color: #9c9797;
            cursor: pointer;
        }

        .bg-blue {
            color: #fff;
            background-color: #1A237E;
        }

        @media screen and (max-width: 991px) {
            .logo {
                margin-left: 0px;
            }

            .image {
                width: 300px;
                height: 220px;
            }

            .border-line {
                border-right: none;
            }

            .card2 {
                border-top: 1px solid #EEEEEE !important;
                margin: 0px 15px;
            }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    <div class="container-fluid px-1 px-md-5 px-lg-1 px-xl-5 py-5 mx-auto">
        <div class="card card0 border-0">
            <div class="row d-flex">
                <!-- Partie gauche avec les images -->
                <div class="col-lg-6">
                    <div class="card1 pb-5">
                        <div class="row justify-content-center justify-content-lg-start">
                            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="Logo Cofima"
                                class="logo img-fluid" width="auto" height="auto" loading="eager" srcset="">
                        </div>
                        <div class="row px-3 justify-content-center mt-4 mb-5 border-line">
                            <img src="{{ asset('assets/img/uNGdWHi.png') }}" class="image">
                        </div>
                    </div>
                </div>

                <!-- Formulaire de connexion -->
                <div class="col-lg-6">
                    <div class="card2 card border-0 px-4 py-5">

                        <!-- Session Status (message succès/erreur) -->
                        <x-auth-session-status class="mb-4 text-center text-success" :status="session('status')" />

                        <!-- Erreurs globales -->
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif


                            <div class="row mt-5 px-3">
                                <div class="col-12 text-center">
                                    <h2 class="mb-0" style="color: #1A237E; font-size: 2rem;">
                                        <strong>Connexion</strong>
                                    </h2>
                                </div>
                            </div>
                        <div class="row px-3 mb-4">
                            <div class="line"></div> <small class="or text-center"></small>
                            <div class="line"></div>
                        </div>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email -->
                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Addresse Email</h6>
                                </label>
                                <input class="mb-4 form-control @error('email') is-invalid @enderror" type="email"
                                    name="email" value="{{ old('email') }}" placeholder="Entrez votre addresse mail"
                                    required autofocus autocomplete="username">
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="row px-3">
                                <label class="mb-1">
                                    <h6 class="mb-0 text-sm">Mot de Passe</h6>
                                </label>
                                <input class="form-control @error('password') is-invalid @enderror" type="password"
                                    name="password" placeholder="Entrez le mot de passe" required
                                    autocomplete="current-password">
                                @error('password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Remember me + Mot de passe oublié (sur la même ligne) -->
                            <div class="row px-3 mb-4 mt-4 align-items-center">
                                <div class="col-12 col-md-6 d-flex align-items-center">
                                    <div class="form-check mb-0">
                                        <input id="remember_me" type="checkbox" class="form-check-input"
                                            name="remember">
                                        <label for="remember_me" class="form-check-label text-sm ms-2">Se souvenir de
                                            moi</label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
                                    @if (Route::has('password.request'))
                                        <a class="text-sm text-decoration-underline text-muted hover:text-primary"
                                            href="{{ route('password.request') }}">
                                            Mot de passe oublié ?
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3 px-3">
                                <button type="submit" class="btn btn-blue text-center w-100">Se connecter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-blue py-4">
                <div class="row px-3">
                    <small class="ml-4 ml-sm-5 mb-2">Copyright  COFIMA &copy; {{ date('Y') }}. Tous droits reservés.</small>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
