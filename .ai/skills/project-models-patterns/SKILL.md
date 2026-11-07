---
name: project-models-patterns
description: Padrões de models Eloquent do sistema-rede para Laravel 13, traits, casts, relacionamentos e scopes.
---

# Models — Padrões Eloquent

## Atribuição em massa

- Use `protected $guarded = []` como padrão do projeto.
- A validação e a definição explícita dos dados que serão persistidos pertencem ao controller ou à Action. Em Livewire, o Form Object define as regras e o componente ou a Action executa a validação e seleciona os dados. O model não é responsável por validar a entrada nem por decidir quais campos recebidos serão persistidos.
- Passe a `create()`, `update()` e métodos semelhantes apenas os campos autorizados para a operação. Consulte [Controllers & Actions](../project-controllers-patterns/SKILL.md) e [Livewire Patterns](../project-livewire-patterns/SKILL.md).

## Traits e configuração

- Inclua `SetDefaultUid` e `LogsModelActivity` em todos os models, exceto quando houver uma razão explícita para a entidade não precisar de uma dessas traits. `LogsModelActivity` incorpora `LogsActivity` e acrescenta o contexto de impersonação. Documente a exceção e seu motivo. A trait `SetDefaultUid` preenche um UID vazio no evento `creating`.
- Ao usar `LogsActivity`, implemente `getActivitylogOptions()` seguindo os models relacionados e as necessidades de auditoria da entidade. O padrão existente usa `logOnly(['*'])->logOnlyDirty()->useLogName(get_class($this))`.
- Declare `protected $table` explicitamente, seguindo a convenção local.
- Aplique `SoftDeletes` quando a entidade exigir exclusão lógica e a tabela suportar essa funcionalidade. Em relações com models excluíveis, use `withTrashed()` quando a regra de negócio exigir acesso ao registro excluído, como em uma visualização histórica.

## Casts, atributos e relacionamentos

- Declare casts com `$casts` ou `casts()`, seguindo os models relacionados. Use `date` para datas sem horário e `datetime` quando o horário fizer parte do dado. Escolha casts monetários de acordo com a precisão e o comportamento necessários e formate valores para exibição na camada de apresentação.
- Em novos accessors e mutators, use métodos protegidos que retornem `Attribute::make()`. Preserve accessors legados até existir motivo para alterá-los.
- Declare o tipo concreto de retorno de cada relacionamento e acrescente PHPDoc com tipos genéricos quando necessário para análise estática. Confirme as chaves estrangeiras e a cardinalidade da relação.
- Mantenha comportamentos próprios da entidade no model. Coordenação de operações e regras de persistência pertencem às Actions.
- Use `exists()` em consultas quando precisar apenas saber se existe um registro. Use `count()` quando precisar da quantidade.

## Scopes

Para restrições reutilizáveis e encadeáveis, use um local scope com `#[Scope]`, seguindo o Laravel 13 e as convenções do contexto.

```php
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

#[Scope]
protected function ativo(Builder $query): void
{
    $query->where('ativo', true);
}
```

Métodos estáticos que iniciam e retornam uma consulta, como `getLancamentosAReceber()`, são métodos de consulta do projeto. Preserve suas assinaturas e comportamento para os chamadores existentes.

## Checklist

- [ ] `$guarded = []` e dados de persistência definidos na camada responsável
- [ ] `SetDefaultUid` e `LogsModelActivity` incluídos ou exceção justificada explicitamente
- [ ] Opções de auditoria adequadas à entidade e tabela declarada
- [ ] Casts, accessors e relacionamentos correspondem ao domínio e ao esquema
- [ ] `SoftDeletes` e `withTrashed()` seguem a regra de negócio

Consulte a [documentação Eloquent do Laravel 13](https://laravel.com/docs/13.x/eloquent) para APIs e comportamento da versão.
