<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vérification OTP</title>

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            color: #070920;
            overflow-x: hidden;
            height: 100%;
            background-color: #B0BEC5;
            background-repeat: no-repeat;
        }
        .card0 { box-shadow: 0px 4px 8px 0px #757575; border-radius: 0px; }
        .card2 { margin: 0px 40px; }
        .logo { margin: 5px; height: auto; max-width: 280px; width: auto; image-rendering: -webkit-optimize-contrast; image-rendering: crisp-edges; }
        .image { width: 360px; height: 280px; image-rendering: auto; }
        .border-line { border-right: 1px solid #EEEEEE; }
        .line { height: 1px; width: 45%; background-color: #E0E0E0; margin-top: 10px; }
        .or { width: 10%; font-weight: bold; }
        .text-sm { font-size: 14px !important; }
        ::placeholder { color: #BDBDBD; opacity: 1; font-weight: 300 }
        input, textarea { padding: 10px 12px; border: 1px solid lightgrey; border-radius: 2px; margin-bottom: 5px; margin-top: 2px; width: 100%; box-sizing: border-box; color: #2C3E50; font-size: 14px; letter-spacing: 1px; }
        input:focus, textarea:focus { box-shadow: none !important; border: 1px solid #304FFE; outline-width: 0; }
        button:focus { box-shadow: none !important; outline-width: 0; }
        a { color: inherit; cursor: pointer; }
        .btn-blue { background-color: #1A237E; width: 150px; color: #fff; border-radius: 2px; }
        .btn-blue:hover { background-color: #9c9797; cursor: pointer; }
        .bg-blue { color: #fff; background-color: #1A237E; }
        @media screen and (max-width: 991px) {
            .logo { margin-left: 0px; }
            .image { width: 300px; height: 220px; }
            .border-line { border-right: none; }
            .card2 { border-top: 1px solid #EEEEEE !important; margin: 0px 15px; }
        }
    </style>
</head>

<body>
    <div class="container-fluid px-1 px-md-5 px-lg-1 px-xl-5 py-5 mx-auto">
        <div class="card card0 border-0">
            <div class="row d-flex">
                <!-- Partie gauche avec images -->
                <div class="col-lg-6">
                    <div class="card1 pb-5">
                        <div class="row justify-content-center justify-content-lg-start">
                            <img src="{{ asset('assets/img/logo_cofima_bon.jpg') }}" alt="Logo Cofima"
                                class="logo img-fluid" width="auto" height="auto" loading="eager">
                        </div>
                        <div class="row px-3 justify-content-center mt-4 mb-5 border-line">
                            <img src="{{ asset('assets/img/uNGdWHi.png') }}" class="image">
                        </div>
                    </div>
                </div>

                <!-- Formulaire OTP -->
                <div class="col-lg-6">
                    <div class="card2 card border-0 px-4 py-5">

                        <!-- Session Status -->
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
                                    <strong>Vérification OTP</strong>
                                </h2>
                                <p class="text-muted mt-2">Entrez le code reçu par email pour continuer</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf

                        <div class="row px-3 mt-4 justify-content-center text-center">
                            <h5 class="text-muted">Un E-mail contenant un code de vérification à 6 chiffres vous a été envoyé à l'adresse - {{ $maskedEmail }}</h5>
                            <p class="mt-2">
                                <strong id="expire-label">Expire dans :</strong>
                                <strong><span id="timer" class="text-danger fw-bold"></span></strong>
                            </p>
                        </div>

                        <!-- Champs OTP -->
                        <div class="d-flex justify-content-center gap-3 my-4 otp-inputs">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" name="otp_code[]"
                                    maxlength="1"
                                    class="otp-field form-control text-center"
                                    style="width:55px; height:55px; font-size:1.8rem; border-radius:10px; border:2px solid #1A237E;"
                                    required>
                            @endfor
                        </div>

                        <div class="row mb-3 px-3">
                            <button type="submit" class="btn btn-blue text-center w-100">
                                Vérifier le code
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            <small class="text-muted">Vous n'avez rien reçu ?</small><br>
                            <a href="#" class="text-primary fw-bold" id="resend-btn">Renvoyer le code</a>
                            <div id="resend-message" class="text-success mt-2" style="display:none;">
                                Code renvoyé ✔
                            </div>
                        </div>

                    </form>

                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-blue py-4">
                <div class="row px-3">
                    <small class="ml-4 ml-sm-5 mb-2">Copyright COFIMA &copy; {{ date('Y') }}. Tous droits réservés.</small>
                </div>
            </div>
        </div>
    </div>

<script>
const inputs = document.querySelectorAll('.otp-field');
inputs[0].focus();

inputs.forEach((input, index) => {
    input.addEventListener('input', (e) => {
        const value = input.value;
        if (value.length > 1) {
            const chars = value.split('');
            chars.forEach((char, i) => {
                if (inputs[index + i]) inputs[index + i].value = char;
            });
            const last = index + value.length - 1;
            if (inputs[last]) inputs[last].focus();
            return;
        }
        if (value && index < inputs.length - 1) inputs[index + 1].focus();
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === "Backspace" && input.value === "" && index > 0) {
            inputs[index - 1].focus();
        }
    });
});

// Coller un OTP complet
inputs.forEach((input, index) => {
    input.addEventListener('paste', (e) => {
        e.preventDefault();
        const pasteData = (e.clipboardData || window.clipboardData).getData('text');
        const digits = pasteData.replace(/\D/g, '').split(''); // ne garder que les chiffres
        digits.forEach((digit, i) => {
            if (inputs[index + i]) inputs[index + i].value = digit;
        });
        const lastIndex = index + digits.length - 1;
        if (inputs[lastIndex]) inputs[lastIndex].focus();
    });
});


let time = {{ $remainingSeconds }};
const timerElement = document.getElementById("timer");
const expireLabel = document.getElementById("expire-label");
const resendBtn = document.getElementById("resend-btn");
const verifyBtn = document.querySelector("button[type='submit']");
const resendMsg = document.getElementById("resend-message");

function disableResendBtn() {
    resendBtn.classList.add("disabled");
    resendBtn.style.pointerEvents = "none";
    resendBtn.style.opacity = "0.5";
}
function enableResendBtn() {
    resendBtn.classList.remove("disabled");
    resendBtn.style.pointerEvents = "auto";
    resendBtn.style.opacity = "1";
}

function updateTimer() {
    if (time > 0) {
        const minutes = String(Math.floor(time / 60)).padStart(2, '0');
        const seconds = String(time % 60).padStart(2, '0');
        timerElement.textContent = `${minutes}:${seconds}`;
        time--;
        return;
    }

    // Quand le code est expiré
    timerElement.textContent = "Code expiré";
    expireLabel.style.display = "none";

    timerElement.classList.add("text-danger");

    verifyBtn.disabled = true;
    verifyBtn.innerText = "Code expiré";
    verifyBtn.style.opacity = "0.6";
    verifyBtn.style.cursor = "not-allowed";

    enableResendBtn();
    clearInterval(interval);
}

disableResendBtn();
const interval = setInterval(updateTimer, 1000);

resendBtn.addEventListener('click', function(e) {
    e.preventDefault();
    if (resendBtn.classList.contains("disabled")) return;

    resendBtn.textContent = "Renvoi...";
    resendBtn.style.pointerEvents = "none";

    fetch("{{ route('otp.resend') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(response => response.json())
    .then(data => {
        time = data.remainingSeconds;
        timerElement.classList.remove("text-danger");
        verifyBtn.disabled = false;
        verifyBtn.innerText = "Vérifier le code";
        verifyBtn.style.opacity = "1";
        verifyBtn.style.cursor = "pointer";
        expireLabel.style.display = "inline"; // remettre le label
        resendBtn.textContent = "Renvoyer le code";

        clearInterval(interval);
        setInterval(updateTimer, 1000);

        disableResendBtn();
        resendMsg.style.display = "block";
        setTimeout(() => resendMsg.style.display = "none", 2500);
    });
});
</script>

</body>
</html>
