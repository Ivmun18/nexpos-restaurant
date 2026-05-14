<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActoDocumento extends Model
{
    protected $table = 'acto_documentos';
    protected $fillable = ['acto_id', 'nombre', 'archivo', 'tipo', 'usuario_id'];
    public function acto() { return $this->belongsTo(ActoNotarial::class, 'acto_id'); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
