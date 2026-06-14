<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model
{
    protected $table = 'responsaveis';
    protected $fillable = ['nome', 'graduacao', 'especialidade', 'setor'];

    public function materiais()
    {
        return $this->hasMany(Material::class);
    }
}
