<?php

namespace App\Mail;

use App\Models\Venta;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ComprobanteEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Venta $venta,
        public string $pdfPath
    ) {}

    public function envelope(): Envelope
    {
        $tipo = $this->venta->tipo_comprobante === '01' ? 'Factura' : 'Boleta';
        return new Envelope(
            subject: $tipo . ' ' . $this->venta->numero_completo . ' - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.comprobante',
            with: [
                'venta'          => $this->venta,
                'tipo'           => $this->venta->tipo_comprobante === '01' ? 'Factura' : 'Boleta',
                'numero'         => $this->venta->numero_completo,
                'fecha'          => $this->venta->fecha_emision,
                'total'          => $this->venta->total,
                'cliente'        => $this->venta->cliente_razon_social ?? 'Cliente',
                'empresa'        => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as($this->venta->numero_completo . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}