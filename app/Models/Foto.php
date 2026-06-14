<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'fotos';
    protected $fillable = ['material_id', 'tipo', 'caminho', 'descricao'];

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
