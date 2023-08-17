@extends('layouts.master-without-nav')

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container text-center">
        <div class="d-flex justify-content-between align-items-center">
            <a class="navbar-brand" href="#">
                <img src="https://redemetrologica.com.br/wp-content/uploads/2020/06/LOGO_REDE_COLOR.png"
                    alt="Rede Metrológica RS" width="238" height="115">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Notícias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Associe-se</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Cursos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Interlaboratoriais</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Laboratórios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Avaliação de Laboratórios</a></li>
                            <li><a class="dropdown-item" href="#">Laboratórios Reconhecidos</a></li>
                            <li><a class="dropdown-item" href="#">Bônus Metrologia</a></li>
                            <li><a class="dropdown-item" href="#">Downloads</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Fale Conosco</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>


<hr style="padding-top: 100px;">
-----------corpo----------
<hr>



<footer id="colophon" class="site-footer">
    <div id="footer-top" class="bg-light">
        <div class="container">
            <div class="row">

                <div class="col-lg-4 col-md-4 col-sm-12">
                    <aside id="custom_html-2" class="widget_text widget widget_custom_html">
                        <div class="textwidget custom-html-widget">
                            <img src="https://redemetrologica.com.br/wp-content/uploads/2020/06/LOGO_REDE_BRANCO.png"
                                title="" alt="Associação Rede de Metrologia e Ensaios do RS"
                                class="img-fluid mb-3"
                                width="193" height="78">
                            <p>Associação Rede de Metrologia e Ensaios do RS<br>
                                CNPJ 97.130.207/0001-12<br>
                                Certificada ISO 9001 pela DNV<br>
                                Acreditada ISO/IEC 17043 pela CGCRE</p>
                            <p>Soluções em Metrologia para Qualidade e Sustentabilidade</p>
                            <a href="https://redemetrologica.com.br/politica-de-privacidade/">Política de
                                Privacidade</a>
                            <br>
                            <a href="https://redemetrologica.com.br/politica-de-cookies/">Política de Cookies</a>
                        </div>
                    </aside>
                </div>

                <div class="col-lg-2 col-md-2 col-sm-12">
                    <aside id="custom_html-3" class="widget_text widget widget_custom_html">
                        <h5 class="wg-title">Contato</h5>
                        <div class="textwidget custom-html-widget">
                            <ul class="list-unstyled">
                                <li><i class="zmdi zmdi-phone"></i><a title="Telefone" href="tel:+55 51 2200-3988 ">+55
                                        51 2200-3988 </a></li>
                                <li><i class="zmdi zmdi-email"></i><a title="E-mail"
                                        href="mailto:contato@redemetrologica.com.br" target="_blank" rel="noopener">
                                        contato@redemetrologica.com.br</a></li>
                            </ul>
                            <address>
                                <i class="zmdi zmdi-pin zmdi-hc-fw"></i>Santa Catarina, nº 40 - Salas 801/802 <br>
                                Porto Alegre - RS <br>
                                Bairro Santa Maria Goretti <br>
                                CEP 91030-330
                            </address>
                        </div>
                    </aside>
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12">
                    {{-- espaco --}}
                </div>

                <div class="col-lg-3 col-md-3 col-sm-12">
                    <aside id="nav_menu-3" class="widget widget_nav_menu">
                        <h5 class="wg-title">Acesso Rápido</h5>
                        <div class="menu-1-primary-menu-container">
                            <ul id="menu-1-primary-menu-1" class="menu list-unstyled">
                                <div class="menu-1-primary-menu-container">
                                    <ul id="menu-1-primary-menu-1" class="menu">
                                        <li
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2045">
                                            <a href="https://redemetrologica.com.br/noticias-2/">Notícias</a></li>
                                        <li
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2452">
                                            <a href="https://redemetrologica.com.br/associe-se-2/">Associe-se</a></li>
                                        <li
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-3115">
                                            <a href="https://redemetrologica.com.br/cursos/">Cursos</a></li>
                                        <li
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-3114">
                                            <a
                                                href="https://redemetrologica.com.br/interlaboratoriais/">Interlaboratoriais</a>
                                        </li>
                                        <li
                                            class="menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-2321">
                                            <a href="#">Laboratórios</a>
                                            <ul class="sub-menu">
                                                <li
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2067">
                                                    <a href="https://redemetrologica.com.br/laboratorios-avaliacao/">Avaliação
                                                        de Laboratórios</a></li>
                                                <li
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-3113">
                                                    <a
                                                        href="https://redemetrologica.com.br/laboratorios-reconhecidos/">Laboratórios
                                                        Reconhecidos</a></li>
                                                <li
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-3170">
                                                    <a href="https://redemetrologica.com.br/bonus-metrologia/">Bônus
                                                        Metrologia</a></li>
                                                <li
                                                    class="menu-item menu-item-type-post_type menu-item-object-page menu-item-3596">
                                                    <a href="https://redemetrologica.com.br/downloads/">Downloads</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-2061">
                                            <a href="https://redemetrologica.com.br/fale-conosco/">Fale Conosco</a>
                                        </li>
                                    </ul>
                                </div>
                            </ul>
                        </div>
                    </aside>
                </div>

            </div>
        </div>
    </div><!-- #footer-top -->

    <div id="footer-bottom" class="bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    Rede Metrológica RS ©
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <ul class="list-inline cms-footer-social">
                        <li class="list-inline-item"><a
                                href="https://www.facebook.com/Rede-Metrol%C3%B3gica-RS-788822964529119/"><span
                                    class="zmdi zmdi-facebook-box"></span></a></li>
                        <li class="list-inline-item"><a href="https://www.instagram.com/redemetrologicars01/"><span
                                    class="zmdi zmdi-instagram"></span></a></li>
                        <li class="list-inline-item"><a
                                href="https://www.linkedin.com/company/redemetrologicars/"><span
                                    class="zmdi zmdi-linkedin"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div><!-- #footer-bottom -->
</footer>
