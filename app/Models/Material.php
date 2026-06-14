<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $table = 'materiais';

    protected $fillable = [
        'dependencia', 'conta', 'classe', 'num_bmp', 'nomenclatura',
        'num_serie', 'fcg', 'num_pn', 'num_sispat', 'etiqueta_metalica',
        'quantidade', 'valor_atualizado', 'valor_depreciacao', 'valor_liquido',
        'situacao', 'em_uso', 'funcionando', 'mais_informacoes',
        'responsavel_id', 'local_id',
    ];

    protected $casts = [
        'valor_atualizado'  => 'decimal:2',
        'valor_depreciacao' => 'decimal:2',
        'valor_liquido'     => 'decimal:2',
    ];

    public function responsavel()
    {
        return $this->belongsTo(Responsavel::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }

    public function reparos()
    {
        return $this->hasMany(Reparo::class)->orderByDesc('created_at');
    }

    public function getSituacaoLabelAttribute(): string
    {
        return $this->situacao ?? '—';
    }

    public function getSituacaoBadgeAttribute(): string
    {
        return match($this->situacao) {
            'A' => 'success',
            'D' => 'secondary',
            'P' => 'warning',
            'R' => 'danger',
            default => 'light',
        };
    }
}
