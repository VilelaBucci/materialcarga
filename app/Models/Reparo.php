<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reparo extends Model
{
    protected $table = 'reparos';
    protected $fillable = [
        'material_id', 'descricao', 'data_inicio', 'data_conclusao',
        'status', 'setor_responsavel', 'observacoes',
    ];

    protected $casts = [
        'data_inicio'    => 'date',
        'data_conclusao' => 'date',
    ];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'em_andamento' => 'Em Andamento',
            'concluido'    => 'Concluído',
            'cancelado'    => 'Cancelado',
            default        => $this->status,
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'em_andamento' => 'warning',
            'concluido'    => 'success',
            'cancelado'    => 'secondary',
            default        => 'light',
        };
    }
}
