<x-layout-site>

    <x-slot name="content">
        <div class="container">
            <div class="row justify-content-center  text-center">
                <div class="col">
                    <i class="bi bi-buildings fs-1"></i>
                    <h2>ENDEREÇO</h2>
                    <p>Rua Santa Catarina, 40 – Salas 801/802</p>
                    <p>Bairro Santa Maria Goretti</p>
                    <p>Porto Alegre/RS – Cep 91030-330</p>
                </div>
                <div class="col">
                    <i class="bi bi-phone fs-1"></i>
                    <h2>TELEFONE</h2>
                    <p>(51) 2200 3988</p>
                    <h2>WHATSAPP</h2>
                    <i class="bi bi-whatsapp fs-1"></i>
                    <p>(51) 99179-5131</p>
                </div>
                <div class="col">
                    <i class="bi bi-share-fill fs-1"></i>
                    <h2>REDE SOCIAL</h2>
                    <div class="">
                        <a href="https://www.facebook.com/Rede-Metrol%C3%B3gica-RS-788822964529119/"><i
                                class="bi bi-facebook fs-2  px-3 "></i></a>
                        <a href="https://www.instagram.com/redemetrologicars01/"><i
                                class="bi bi-instagram fs-2 px-3 "></i></a>
                        <a href="https://br.linkedin.com/company/redemetrologicars"><i
                                class="bi bi-linkedin fs-2 px-3 "></i></a>
                    </div>
                </div>
            </div>
        </div>


        {{-- mapa --}}
        <div class="container-fluid SiteMapbox">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d6909.986166011413!2d-51.177472!3d-30.008355!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9519776178b124d7%3A0xa2ee25f04980c5b9!2sR.%20Santa%20Catarina%2C%2040%20-%20Santa%20Maria%20Goretti%2C%20Porto%20Alegre%20-%20RS%2C%2091030-330!5e0!3m2!1spt-BR!2sbr!4v1696857669341!5m2!1spt-BR!2sbr"
                style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        {{-- mapa --}}

    </x-slot>

</x-layout-site>
