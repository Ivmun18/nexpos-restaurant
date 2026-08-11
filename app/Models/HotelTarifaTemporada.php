<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelTarifaTemporada extends Model
{
    protected $table = 'hotel_tarifas_temporada';
    protected $fillable = ['empresa_id','tipo_id','nombre','fecha_inicio','fecha_fin','precio_noche','color','activo','descripcion'];
    protected $casts = ['fecha_inicio' => 'date', 'fecha_fin' => 'date', 'activo' => 'boolean'];

    public function tipo() { return $this->belongsTo(HotelTipoHabitacion::class, 'tipo_id'); }
}
