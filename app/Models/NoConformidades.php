<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoConformidades extends Model
{
   protected $table = 'no_conformidades';
   protected $primaryKey = 'idNoConformidades';

   public function cortesNoConformidades()
{
   return $this->hasMany(Cortes_de_tesis_has_NoConformidades::class, 'no_conformidad_id');
}
}
