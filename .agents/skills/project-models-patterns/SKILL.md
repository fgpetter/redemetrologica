---
name: project-models-patterns
description: Padrões para Models Eloquent (Traits, Accessors, PHP 8.3+) no projeto sistema-rede.
---

# Models — Padrões Eloquent

## 1. Regras Gerais

- Usar `protected $guarded = []` em vez de `$fillable` (padrão estabelecido no projeto).
- Usar o trait `SetDefaultUid` para gerar UIDs automáticos via `uniqid()` no evento `creating`.
- Usar o trait `LogsActivity` (Spatie) com `getActivitylogOptions()` implementado.
- Sempre declarar `protected $table = 'nome_tabela'` explicitamente.
- Castear datas com `'date'` (Carbon) no `$casts`. Valores monetários **não** são castados — são formatados via helper `formataMoeda()` no Controller ou componente.
- **`SoftDeletes`:** usar em entidades principais (clientes, pessoas, usuários). Ao referenciar modelos com `SoftDeletes` em relacionamentos `BelongsTo`, adicionar `->withTrashed()` na relação para evitar perda de dados em logs/visualizações.
- **Accessors/Mutators:** obrigatório usar `Attribute::make(get:, set:)` (Laravel 9+). Proibido `getXxxAttribute()` em código novo.
- **Model Scopes estáticos:** para filtros complexos reutilizáveis, criar método `static` retornando `Builder` (ex: `getLancamentosAReceber(array $validated): Builder`).
- Relacionamentos sempre documentados com `@return` no PHPDoc e com tipagem de retorno (`BelongsTo`, `HasMany`, `BelongsToMany`, etc.).
- Métodos de negócio que manipulam relacionamentos (ex: `updateParametros()`) devem ficar no Model, não no Controller.
- Para validar se um objeto existe, use `->exists()` em vez de `->count()` isso gera uma query muito mais eficiente no banco.

## 2. Exemplo de Referência

```php
<?php

namespace App\Models;

use App\Traits\SetDefaultUid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NomeModel extends Model
{
    use LogsActivity, SetDefaultUid, SoftDeletes; // SoftDeletes apenas em entidades principais

    protected $table = 'nome_tabela';

    protected $guarded = [];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim'    => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName(get_class($this));
    }

    // ─── Relacionamentos ─────────────────────────────────────────────────────

    /**
     * Retorna o relacionamento com ModelPai.
     *
     * @return BelongsTo
     */
    public function modelPai(): BelongsTo
    {
        return $this->belongsTo(ModelPai::class);
    }

    /**
     * Retorna os itens filhos.
     *
     * @return HasMany
     */
    public function itens(): HasMany
    {
        return $this->hasMany(ItemModel::class);
    }

    // ─── Accessors / Mutators (Laravel 9+ style) ─────────────────────────────

    /**
     * Formata o CPF/CNPJ na leitura e remove máscara na gravação.
     */
    protected function cpfCnpj(): Attribute
    {
        return Attribute::make(
            get: fn (string|null $value) => $this->formataDoc($value),
            set: fn (string|null $value) => preg_replace("/[^\d]/", '', $value),
        );
    }

    // ─── Model Scopes estáticos (para filtros complexos reutilizáveis) ────────

    /**
     * Retorna query base filtrada para listagem.
     *
     * @param array $validated
     * @return Builder
     */
    public static function getItensComFiltros(array $validated): Builder
    {
        return self::query()
            ->when($validated['status'] ?? null, fn (Builder $q, $s) => $q->where('status', $s))
            ->when($validated['search'] ?? null, fn (Builder $q, $s) => $q->where('nome', 'like', "%{$s}%"));
    }

    public function userExists()
    {
        $this->where('email', $email)->exists()
    }
}
```

## 3. Anti-Patterns

| ❌ Anti-pattern                                       | ✅ Correto                                              |
| :-----------------------------------------------      | :------------------------------------------------------- |
| `getXxxAttribute()` para novos Accessors              | Usar `Attribute::make(get:, set:)`                       |
| `$fillable = [...]` explícito                         | Usar `protected $guarded = []`                           |
| Omitir `protected $table`                             | Sempre declarar a tabela explicitamente                  |
| Relacionamento `BelongsTo` sem `->withTrashed()`      | Adicionar `->withTrashed()` em entidades com SoftDeletes |
| Usar count `$this->where('email', $email)->count()`   | Usar Exists `$this->where('email', $email)->exists()`    |

## 4. Checklist

- [ ] Trait `SetDefaultUid` incluído
- [ ] Trait `LogsActivity` incluído com `getActivitylogOptions()`
- [ ] `protected $guarded = []` (não usar `$fillable`)
- [ ] `protected $table` declarado explicitamente
- [ ] Datas castadas no `$casts` com `'date'`
- [ ] `SoftDeletes` aplicado em entidades principais
- [ ] Accessors usando `Attribute::make`
- [ ] PHPDoc `@return` em todos os relacionamentos
