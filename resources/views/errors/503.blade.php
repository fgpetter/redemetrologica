<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8" />
    <title>Em Manutenção | Rede Metrológica RS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ URL::asset('build/css/bootstrap.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ URL::asset('build/css/icons.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ URL::asset('build/css/app.min.css') }}" type="text/css" />
    <link rel="stylesheet" href="{{ URL::asset('build/css/custom.min.css') }}" type="text/css" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .SiteHeader {
            background-color: #f7f7f7;
        }

        .SiteBanner--titulo {
            font-size: 3rem;
            color: #fff000;
        }

        .SiteBanner--text {
            font-size: 1rem;
        }

        .SiteFooter__rodape {
            background-color: #305c74;
        }

        .maintenance-main {
            flex: 1;
        }

        @media (max-width: 576px) {
            .SiteBanner--titulo {
                font-size: 1.5rem;
            }

            .SiteBanner--text {
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar sticky-top SiteHeader border-bottom">
        <div class="container">
            <span class="navbar-brand py-1 mb-0">
                <img src="{{ asset('build/images/site/LOGO_REDE_COLOR.png') }}" alt="Rede Metrológica RS" height="95">
            </span>
        </div>
    </nav>

    <main class="maintenance-main">
        <div class="card text-bg-dark border-0 rounded-0">
            <img src="{{ asset('build/images/site/BANNER-HOME-TOPO.png') }}" class="card-img" alt="Rede Metrológica RS">

            <div class="card-img-overlay d-flex justify-content-center">
                <div class="align-self-center text-center">
                    <p class="SiteBanner--titulo mb-2"><strong>EM MANUTENÇÃO</strong></p>
                    <p class="SiteBanner--text mb-1">REDE METROLÓGICA RS</p>
                    <p class="SiteBanner--text mb-0">Voltamos em breve</p>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row my-5 d-flex align-items-center justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="text-center px-3">
                        <h1 class="h2 mb-3">Estamos realizando melhorias</h1>
                        <p class="mb-4">
                            O site da Rede Metrológica RS está temporariamente indisponível para manutenção.
                            Em breve você poderá acessar novamente nossos cursos, ensaios de proficiência,
                            laboratórios reconhecidos e demais serviços.
                        </p>
                        <p class="mb-1 text-muted">Precisa falar conosco?</p>
                        <p class="mb-0">
                            <a href="tel:+555122003988" class="text-decoration-none">+55 51 2200-3988</a>
                            <span class="mx-2 text-muted">|</span>
                            <a href="mailto:contato@redemetrologica.com.br" class="text-decoration-none">
                                contato@redemetrologica.com.br
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div class="mt-auto mb-0 text-white"
            style="background-image: url('{{ asset('build/images/site/banner-footer.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="container py-4">
                <div class="row gy-3 justify-content-center">
                    <div class="col-12 col-md-8 text-center text-md-start">
                        <img src="{{ asset('build/images/site/LOGO_REDE_BRANCO.png') }}"
                            class="card-img w-max-350 SiteFooter__imagem mb-3" alt="Rede Metrológica RS"
                            style="max-width: 280px;">
                        <div>
                            Associação Rede de Metrologia e Ensaios do RS<br>
                            Certificada ISO 9001 pela DNV<br>
                            Acreditada ISO/IEC 17043 pela CGCRE
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="SiteFooter__rodape py-2">
            <div class="container">
                <p class="text-white text-center mb-0 small">
                    {{ date('Y') }} Rede Metrológica RS
                </p>
            </div>
        </div>
    </footer>
</body>

</html>
