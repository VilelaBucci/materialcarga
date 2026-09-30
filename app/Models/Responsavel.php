<?php

namespace App\Models;

use App\Models\Concerns\VinculadoASetores;
use Illuminate\Database\Eloquent\Model;

class Responsavel extends Model
{
    use VinculadoASetores;

    protected $table = 'responsaveis';
    protected $fillable = ['nome', 'graduacao', 'especialidade', 'setor'];

    public function materiais()
    {
        return $this->hasMany(Material::class);
    }

    public function setores()
    {
        return $this->belongsToMany(Setor::class, 'responsavel_setor');
    }
}
