<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActoSeguimiento extends Model
{
    protected $table = 'acto_seguimientos';
    protected $fillable = ['acto_id', 'usuario_id', 'estado_nuevo', 'comentario'];
    public function acto() { return $this->belongsTo(ActoNotarial::class, 'acto_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
