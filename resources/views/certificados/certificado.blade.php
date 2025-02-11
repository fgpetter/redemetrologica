<html lang="en">

<head>
  <title>Certificado</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>

  <div class="max-w-[800px] mx-auto pt-52 bg-right-top bg-contain bg-no-repeat" 
    style="background-image: url('{{ asset('build/images/certificados/marcadagua.png') }}')" >
    <!-- Certificate Title -->
    <div class="text-center mb-16">
      <h1 class="text-3xl font-semibold mb-8">Certificado</h1>
      <p class="text-lg mb-8">
        A Associação Rede de Metrologia e Ensaios do Rio Grande do Sul certifica que
      </p>
    </div>

    <!-- Certificate Content -->
    <div class="space-y-8 mb-16">
      <div class="text-center">
        <p class="text-xl font-semibold">"XXXXXXXX"</p>
      </div>

      <div class="text-center">
        <p class="mb-4">Participou do</p>
        <p class="text-xl font-semibold mb-8">NOME DO CURSO</p>
      </div>

      <div class="text-center">
        <p class="mb-2">Realizado no (s) dia (s):</p>
        <p class="font-semibold">Data do curso</p>
      </div>
    </div>

    <!-- Location and Date -->
    <div class="text-center mb-16">
      <p>Porto Alegre, 09 de janeiro de 2025</p>
    </div>

    <!-- Signature -->
    <div class="text-center mb-16 mx-auto">
      <img src="{{ asset('build/images/certificados/assinatura.jpg') }}" class="mx-auto">
    </div>

    <!-- Footer -->
    <div class="text-center text-sm text-gray-600 space-y-2">
      <p>Associação Rede de Metrologia e Ensaios do Rio Grande do Sul</p>
      <p>CNPJ 97.130.207/0001-12 | Certificada ISO 9001</p>
      <p class="text-xs">
        Santa Catarina, nº 40 - Salas 801/802 - Santa Maria Goretti - Porto Alegre - RS - Brasil | Cep 91030-330
      </p>
      <p class="text-xs">
        +55 51 2200-3988 | contato@redemetrologica.com.br | www.redemetrologica.com.br
      </p>
    </div>

    @pageBreak
        <!-- Certificate Content -->
        <div class="space-y-8 mb-16">
          <div class="text-center">
            <p class="text-xl font-semibold">"XXXXXXXX"</p>
          </div>
    
          <div class="text-center">
            <p class="mb-4">Participou do</p>
            <p class="text-xl font-semibold mb-8">NOME DO CURSO</p>
          </div>
    
          <div class="text-center">
            <p class="mb-2">Realizado no (s) dia (s):</p>
            <p class="font-semibold">Data do curso</p>
          </div>
        </div>
    
        <!-- Location and Date -->
        <div class="text-center mb-16">
          <p>Porto Alegre, 09 de janeiro de 2025</p>
        </div>
    
  </div>
</body>

</html>
