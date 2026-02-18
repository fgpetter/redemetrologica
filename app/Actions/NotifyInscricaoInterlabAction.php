<?php

namespace App\Actions;

use App\Models\Endereco;
use App\Models\InterlabAnalista;
use App\Models\InterlabInscrito;
use App\Models\InterlabLaboratorio;
use App\Models\AgendaInterlab;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmacaoInscricaoAnalistaNotification;
use App\Mail\SenhaAnalistaInterlabNotification;

class NotifyInscricaoInterlabAction
{
    public function execute(InterlabInscrito $inscrito, AgendaInterlab $interlab)
    {
      return true;
    }

}
