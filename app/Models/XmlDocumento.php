<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XmlDocumento extends Model
{
    protected $table = 'xml_documentos';

    protected $fillable = [
        'empresa_id',
        'tipo_comprobante',
        'numero_completo',
        'referencia_id',
        'referencia_tabla',
        'xml_sin_firma',
        'xml_firmado',
        'hash_documento',
        'nombre_archivo',
        'zip_nombre',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'referencia_id');
    }
}
