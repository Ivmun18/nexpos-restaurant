<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;

class GimnasioReserva extends Model {
    protected $table = 'gimnasio_reservas';
    protected $fillable = ['empresa_id','miembro_id','horario_id','fecha','estado'];
    protected $casts = ['fecha'=>'date'];
    public function miembro() { return $this->belongsTo(GimnasioMiembro::class, 'miembro_id'); }
    public function horario() { return $this->belongsTo(GimnasioHorario::class, 'horario_id'); }
}
