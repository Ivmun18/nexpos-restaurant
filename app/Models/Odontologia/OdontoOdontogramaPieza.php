<?php
namespace App\Models\Odontologia;
use Illuminate\Database\Eloquent\Model;

class OdontoOdontogramaPieza extends Model {
    protected $table = 'odonto_odontograma_piezas';
    protected $fillable = ['odontograma_id','numero_pieza','estado','cara_mesial','cara_distal','cara_oclusal','cara_vestibular','cara_palatino','color','notas'];
    public function odontograma() { return $this->belongsTo(OdontoOdontograma::class, 'odontograma_id'); }
}
