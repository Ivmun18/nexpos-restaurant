<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaLog extends Model
{
    protected $table = 'auditoria_log';

    protected $fillable = [
        'empresa_id',
        'usuario_id',
        'usuario_nombre',
        'categoria',
        'accion',
        'entidad',
        'entidad_id',
        'entidad_descripcion',
        'datos_antes',
        'datos_despues',
        'descripcion',
        'ip',
        'user_agent',
        'severidad',
    ];

    protected $casts = [
        'datos_antes'   => 'array',
        'datos_despues' => 'array',
    ];

    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    // ============================================================
    // HELPER ESTÁTICO PARA REGISTRAR EVENTOS FÁCILMENTE
    // Uso: AuditoriaLog::registrar('venta', 'creada', ...)
    // ============================================================
    public static function registrar(
        string $categoria,
        string $accion,
        ?string $entidad = null,
        $entidadId = null,
        ?string $entidadDescripcion = null,
        ?array $datosAntes = null,
        ?array $datosDespues = null,
        ?string $descripcion = null,
        string $severidad = 'info'
    ): self {
        $user = auth()->user();
        $request = request();

        return self::create([
            'empresa_id'          => $user?->empresa_id,
            'usuario_id'          => $user?->id,
            'usuario_nombre'      => $user?->name,
            'categoria'           => $categoria,
            'accion'              => $accion,
            'entidad'             => $entidad,
            'entidad_id'          => $entidadId,
            'entidad_descripcion' => $entidadDescripcion,
            'datos_antes'         => $datosAntes,
            'datos_despues'       => $datosDespues,
            'descripcion'         => $descripcion,
            'ip'                  => $request?->ip(),
            'user_agent'          => substr($request?->userAgent() ?? '', 0, 255),
            'severidad'           => $severidad,
        ]);
    }
}
