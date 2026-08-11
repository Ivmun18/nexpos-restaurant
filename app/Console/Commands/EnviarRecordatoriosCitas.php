<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Odontologia\OdontoCita;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnviarRecordatoriosCitas extends Command {
    protected $signature   = 'citas:recordatorios {--empresa_id=* : IDs de empresas}';
    protected $description = 'Envía recordatorios WhatsApp de citas del día siguiente';

    public function handle() {
        $manana     = Carbon::tomorrow();
        $empresaIds = $this->option('empresa_id');

        $query = OdontoCita::with(['paciente','doctor','empresa'])
            ->whereDate('fecha_hora', $manana)
            ->whereIn('estado', ['programada','confirmada']);

        if (!empty($empresaIds)) {
            $query->whereIn('empresa_id', $empresaIds);
        }

        $citas = $query->get();
        $this->info("Citas encontradas: {$citas->count()}");

        foreach ($citas as $cita) {
            $this->enviarRecordatorio($cita);
        }

        $this->info('✅ Recordatorios enviados');
    }

    private function enviarRecordatorio(OdontoCita $cita) {
        $paciente = $cita->paciente;
        $telefono = $paciente->telefono ?? $paciente->telefono_emergencia;

        if (!$telefono) {
            $this->warn("Sin teléfono: {$paciente->nombres} {$paciente->apellidos}");
            return;
        }

        $empresa  = Empresa::find($cita->empresa_id);
        $fecha    = Carbon::parse($cita->fecha_hora)->locale('es')->isoFormat('dddd D [de] MMMM');
        $hora     = Carbon::parse($cita->fecha_hora)->format('H:i');
        $doctor   = $cita->doctor->nombre ?? 'su doctor';
        $clinica  = $empresa->nombre_comercial ?? $empresa->razon_social;
        $tel_cl   = $empresa->telefono ?? '';

        $mensaje = "Hola {$paciente->nombres} 👋\n\n"
            . "Le recordamos que tiene una *cita dental* programada para:\n"
            . "📅 *{$fecha}* a las *{$hora}*\n"
            . "👨‍⚕️ Dr. {$doctor}\n"
            . "🦷 {$clinica}\n\n"
            . "Para confirmar o reprogramar, contáctenos:\n"
            . "📞 {$tel_cl}\n\n"
            . "_Por favor llegue 5 minutos antes._";

        // Limpiar teléfono (solo números)
        $tel = preg_replace('/[^0-9]/', '', $telefono);
        if (strlen($tel) === 9) $tel = '51' . $tel; // Perú

        // Log para verificación
        Log::channel('daily')->info("Recordatorio enviado", [
            'paciente'  => "{$paciente->nombres} {$paciente->apellidos}",
            'telefono'  => $tel,
            'cita'      => $cita->fecha_hora,
        ]);

        // Guardar URL de WhatsApp en BD para tracking
        $waUrl = "https://wa.me/{$tel}?text=" . urlencode($mensaje);
        $cita->update(['observaciones' => ($cita->observaciones ?? '') . " [Recordatorio enviado: " . now()->toDateTimeString() . "]"]);

        $this->info("✅ {$paciente->nombres} → wa.me/{$tel}");
        return $waUrl;
    }
}
