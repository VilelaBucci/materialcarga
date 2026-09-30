<?php

namespace App\Models;

use App\Models\Concerns\VinculadoASetores;
use Illuminate\Database\Eloquent\Model;

class Local extends Model
{
    use VinculadoASetores;

    protected $table = 'locais';
    protected $fillable = ['nome', 'setor'];

    public function materiais()
    {
        return $this->hasMany(Material::class);
    }

    public function setores()
    {
        return $this->belongsToMany(Setor::class, 'local_setor');
    }
}
