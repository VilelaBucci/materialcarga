<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Selecao extends Model
{
    protected $table = 'selecoes';
    protected $fillable = ['nome', 'setor_id'];

    public function materiais()
    {
        return $this->belongsToMany(Material::class, 'material_selecao');
    }

    public function setor()
    {
        return $this->belongsTo(Setor::class);
    }
}
