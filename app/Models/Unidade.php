<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = 'unidades';
    protected $fillable = ['nome'];

    public function setores()
    {
        return $this->hasMany(Setor::class)->orderBy('nome');
    }
}
