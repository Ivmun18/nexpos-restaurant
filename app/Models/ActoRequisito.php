<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActoRequisito extends Model
{
    protected $table = 'acto_requisitos';
    protected $fillable = ['acto_id', 'documento', 'entregado', 'observacion', 'user_id'];
    protected $casts = ['entregado' => 'boolean'];
    public function acto() { return $this->belongsTo(ActoNotarial::class, 'acto_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
