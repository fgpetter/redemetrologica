<?php
 
namespace App\View\Components\Painel\Unidades;

use App\Models\Pessoa;
use App\Models\Unidade;
use Illuminate\View\Component;
 
class Modal extends Component
{

    /** @var object $unidade */
    public $unidade;

    /** @var object $pessoa */
    public $pessoa;
 
    /**
     * Create the component instance.
     *
     * @param object $unidade
     * @param object $pessoa
     * @return void
     */
    public function __construct($unidade, $pessoa)
    {
        $this->unidade = $unidade;
        $this->pessoa = $pessoa;
    }
 
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.painel.unidades.modal');
    }
}