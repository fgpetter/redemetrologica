 <!-- noticias -->
 <div class="container-fluid mt-5 pt-5">
     <div class=" position-relative container-fluid text-center row py-5">
         <div class="col-12 SiteTitulo d-flex align-items-center justify-content-center ">
             <h1 class=" ">NOTÍCIAS</h1>
         </div>
         <div class="position-absolute top-50 start-50 translate-middle col-12 SiteTitulo--sombra ">
             <p class="">NOTÍCIAS</p>
         </div>
     </div>

     <div class="container ">


         <div class="row justify-content-around">
             @foreach ($noticias as $noticia)
                 <div class="col-sm-12 col-md-6 col-lg-3 mb-3">
                     <div class="card hover-shadow" style="width: 18rem;">
                         <a href="{{ route('noticia-show', ['slug' => $noticia->slug]) }}">
                             <img src="{{ asset('post-media/' . $noticia->thumb) }}" class="card-img-top"
                                 style="height: 200px; width: 100%; object-fit: cover;" alt="...">
                         </a>
                         <div class="card-body">
                             <a href="{{ route('noticia-show', ['slug' => $noticia->slug]) }}">
                                 <p class="bold h5">{{ $noticia->titulo }}</p>
                             </a>
                             <p class="opacity-50"><i class="bi bi-calendar-event"></i>
                                 {{ $noticia->data_publicacao }}
                             </p>
                             <p class="opacity-50">{{ $noticia->conteudo }}</p>
                             <a href="{{ route('noticia-show', ['slug' => $noticia->slug]) }}" class="text-black">Ler
                                 Mais <i class="fa-solid fa-circle-chevron-right"></i></a>
                         </div>
                     </div>
                 </div>
             @endforeach

         </div>


     </div>
     <div class="container">
         <div class="row justify-content-end align-items-end">
             <div class="col text-end">
                 <a href="noticias" class="text-secondary small">
                     <i class="fas fa-arrow-right"></i>
                     Ver mais
                 </a>
             </div>
         </div>
     </div>

 </div>
 <!-- noticias -->
