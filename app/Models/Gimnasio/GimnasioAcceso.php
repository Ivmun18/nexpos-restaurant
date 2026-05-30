<?php
namespace App\Models\Gimnasio;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class GimnasioAcceso extends Model {
    use Auditable;

    protected $auditModulo = 'Gimnasio';
    protected $table = 'gimnasio_accesos';
    protected $fillable = ['empresa_id','miembro_id','entrada','salida','tipo_acceso','observacion'];
    protected $casts = ['entrada'=>'datetime','salida'=>'datetime'];
    public function miembro() { return $this->belongsTo(GimnasioMiembro::class, 'miembro_id'); }
}
