<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VentasExport implements FromCollection, WithHeadings, WithStyles, WithTitle, ShouldAutoSize
{
    protected string $desde;
    protected string $hasta;
    protected string $tipo;

    public function __construct(string $desde, string $hasta, string $tipo = '')
    {
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->tipo  = $tipo;
    }

    public function collection()
    {
        $query = Venta::query()
            ->whereDate('fecha_emision', '>=', $this->desde)
            ->whereDate('fecha_emision', '<=', $this->hasta)
            ->where('estado', '!=', 'anulado');

        if ($this->tipo) {
            $query->where('tipo_comprobante', $this->tipo);
        }

        return $query->orderBy('fecha_emision')->get()->map(function ($v) {
            return [
                'fecha'           => $v->fecha_emision instanceof \Carbon\Carbon
                    ? $v->fecha_emision->format('d/m/Y')
                    : $v->fecha_emision,
                'numero'          => $v->numero_completo,
                'tipo'            => $v->tipo_comprobante === '01' ? 'Factura' : 'Boleta',
                'tipo_doc'        => $v->cliente_tipo_doc === '6' ? 'RUC' : 'DNI',
                'num_doc'         => $v->cliente_num_doc ?? '',
                'cliente'         => $v->cliente_razon_social ?? 'CLIENTES VARIOS',
                'gravado'         => $v->total_gravado,
                'exonerado'       => $v->total_exonerado,
                'igv'             => $v->total_igv,
                'total'           => $v->total,
                'forma_pago'      => $v->forma_pago,
                'estado'          => $v->estado,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Número',
            'Tipo',
            'Tipo Doc.',
            'Nro. Documento',
            'Cliente',
            'Op. Gravadas',
            'Op. Exoneradas',
            'IGV',
            'Total',
            'Forma Pago',
            'Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    public function title(): string
    {
        return 'Ventas ' . $this->desde . ' al ' . $this->hasta;
    }
}