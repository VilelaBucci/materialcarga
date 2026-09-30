<?php

namespace App\Models\Concerns;

// Locais e responsáveis podem atender vários setores (tabelas local_setor e responsavel_setor)
trait VinculadoASetores
{
    public function scopeDoSetor($query, $setorId)
    {
        return $query->whereHas('setores', fn($q) => $q->where('setores.id', $setorId));
    }

    // Todos os setores da unidade. Os que ficaram sem setor continuam visíveis para o admin, como antes.
    public function scopeDaUnidade($query, $unidadeId)
    {
        return $query->where(fn($q) => $q
            ->whereHas('setores', fn($s) => $s->where('setores.unidade_id', $unidadeId))
            ->orDoesntHave('setores'));
    }

    // No modo todos os setores, a unidade inteira; senão, só o setor
    public function scopeVisiveisPara($query, $setorId, $unidadeId, bool $verTodos)
    {
        return $verTodos ? $query->daUnidade($unidadeId) : $query->doSetor($setorId);
    }
}
