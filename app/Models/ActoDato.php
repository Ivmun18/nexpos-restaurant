<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ActoDato extends Model
{
    protected $table = 'acto_datos';
    protected $fillable = ['acto_id', 'campo', 'valor'];
    public function acto() { return $this->belongsTo(ActoNotarial::class, 'acto_id'); }
}
