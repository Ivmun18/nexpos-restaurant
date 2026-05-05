@component('mail::message')
# {{ $empresa }}

Estimado(a) **{{ $cliente }}**,

Adjunto encontrara su **{{ $tipo }} N° {{ $numero }}** con fecha {{ $fecha }}.

@component('mail::panel')
**Total a pagar: S/ {{ number_format($total, 2) }}**
@endcomponent

Si tiene alguna consulta, no dude en contactarnos.

Gracias por su preferencia.

@component('mail::button', ['url' => config('app.url')])
Ver comprobante en linea
@endcomponent

{{ $empresa }}
@endcomponent