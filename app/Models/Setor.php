<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setor extends Model
{
    protected $table = 'setores';
    protected $fillable = ['nome', 'sigla', 'senha', 'senha_adm', 'unidade_id'];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }

    public function locais()
    {
        return $this->hasMany(Local::class, 'setor', 'nome');
    }

    public function responsaveis()
    {
        return $this->hasMany(Responsavel::class, 'setor', 'nome');
    }
}
