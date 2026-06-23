@props(['lancamento'])
<button
  type="button"
  class="dropdown-item js-abrir-modal-duplicar"
  data-lancamento="{{ json_encode([
      'uid' => $lancamento->uid,
      'data_emissao' => $lancamento->data_emissao ? \Carbon\Carbon::parse($lancamento->data_emissao)->format('Y-m-d') : '',
      'nota_fiscal' => $lancamento->nota_fiscal ?? '',
      'consiliacao' => $lancamento->consiliacao ?? '',
      'documento' => $lancamento->documento ?? '',
      'pessoa_id' => $lancamento->pessoa_id,
      'centro_custo_id' => $lancamento->centro_custo_id,
      'plano_conta_id' => $lancamento->plano_conta_id,
      'historico' => $lancamento->historico ?? '',
      'tipo_lancamento' => $lancamento->tipo_lancamento,
      'valor' => number_format((float) $lancamento->valor, 2, ',', '.'),
      'data_vencimento' => $lancamento->data_vencimento ? \Carbon\Carbon::parse($lancamento->data_vencimento)->format('Y-m-d') : '',
      'data_pagamento' => $lancamento->data_pagamento ? \Carbon\Carbon::parse($lancamento->data_pagamento)->format('Y-m-d') : '',
      'modalidade_pagamento_id' => $lancamento->modalidade_pagamento_id ?? '',
  ], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) }}"
>Duplicar</button>
