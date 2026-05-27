<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $acto->numero_expediente }}</title>
    <style>
        @page {
            margin: 2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .header .empresa {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header .info {
            font-size: 10pt;
            margin: 3px 0;
        }
        .expediente {
            text-align: right;
            font-size: 11pt;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .tipo-acto {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            text-decoration: underline;
        }
        .seccion {
            margin: 25px 0;
        }
        .seccion-titulo {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
        }
        .contenido {
            text-align: justify;
            margin: 15px 0;
            line-height: 1.8;
        }
        .dato-item {
            margin: 8px 0;
            padding-left: 20px;
        }
        .dato-item strong {
            display: inline-block;
            width: 150px;
            text-transform: uppercase;
            font-size: 10pt;
        }
        .sello-notario {
            margin-top: 60px;
            text-align: center;
            border: 2px dashed #999;
            padding: 40px 20px;
            background: #fafafa;
        }
        .firmas {
            margin-top: 80px;
            page-break-inside: avoid;
        }
        .firma-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            margin: 40px 2% 0;
            vertical-align: top;
        }
        .firma-linea {
            border-top: 1px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
        .parte {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #333;
        }
        .parte .rol {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11pt;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="empresa">{{ $acto->empresa->nombre }}</div>
        @if($acto->empresa->ruc)
            <div class="info">R.U.C. {{ $acto->empresa->ruc }}</div>
        @endif
        @if($acto->empresa->direccion)
            <div class="info">{{ $acto->empresa->direccion }}</div>
        @endif
    </div>

    <!-- EXPEDIENTE -->
    <div class="expediente">
        EXPEDIENTE N° {{ $acto->numero_expediente }}
    </div>

    <!-- TIPO DE ACTO -->
    <div class="tipo-acto">
        @switch($acto->tipo_acto)
            @case('escritura_publica') ESCRITURA PÚBLICA @break
            @case('poder') PODER NOTARIAL @break
            @case('testamento') TESTAMENTO @break
            @case('legalizacion') LEGALIZACIÓN @break
            @case('carta_notarial') CARTA NOTARIAL @break
            @case('protesto') PROTESTO @break
            @case('acta_notarial') ACTA NOTARIAL @break
            @default {{ strtoupper($acto->tipo_acto) }}
        @endswitch
    </div>

    <!-- ASUNTO -->
    @if($acto->asunto)
    <div class="seccion">
        <div class="seccion-titulo">Asunto</div>
        <div class="contenido">{{ $acto->asunto }}</div>
    </div>
    @endif

    <!-- DATOS ESPECÍFICOS DEL ACTO -->
    @if(count($datos) > 0)
    <div class="seccion">
        <div class="seccion-titulo">Datos del Acto</div>
        @foreach($datos as $campo => $valor)
        <div class="dato-item">
            <strong>{{ ucfirst(str_replace('_', ' ', $campo)) }}:</strong> {{ $valor }}
        </div>
        @endforeach
    </div>
    @endif

    <!-- PARTES INTERVINIENTES (desde tabla estructurada) -->
    @if($acto->partes && $acto->partes->count() > 0)
    <div class="seccion">
        <div class="seccion-titulo">Partes Intervinientes</div>
        @foreach($acto->partes as $parte)
        <div class="parte">
            <div class="rol">{{ $parte->orden }}. {{ strtoupper($parte->rol) }}</div>
            <div style="margin:5px 0;">
                <strong>Nombre:</strong> 
                {{ $parte->tipo_persona === 'juridica' ? $parte->razon_social : $parte->nombre_completo }}
            </div>
            <div style="margin:5px 0;">
                <strong>{{ $parte->tipo_documento_label }}:</strong> {{ $parte->numero_documento }}
            </div>
            @if($parte->estado_civil)
            <div style="margin:5px 0;">
                <strong>Estado Civil:</strong> {{ ucfirst($parte->estado_civil) }}
            </div>
            @endif
            @if($parte->domicilio)
            <div style="margin:5px 0;">
                <strong>Domicilio:</strong> {{ $parte->domicilio }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @elseif($acto->partes_intervinientes)
    <!-- Si no hay partes en tabla, mostrar campo texto legacy -->
    <div class="seccion">
        <div class="seccion-titulo">Partes Intervinientes</div>
        <div class="contenido">{{ $acto->partes_intervinientes }}</div>
    </div>
    @endif

    <!-- OBSERVACIONES -->
    @if($acto->observaciones)
    <div class="seccion">
        <div class="seccion-titulo">Observaciones</div>
        <div class="contenido">{{ $acto->observaciones }}</div>
    </div>
    @endif

    <!-- FECHA -->
    <div class="seccion">
        <div class="contenido">
            <strong>Fecha de ingreso:</strong> {{ \Carbon\Carbon::parse($acto->fecha_ingreso)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </div>
    </div>

    <!-- SELLO DEL NOTARIO -->
    <div class="sello-notario">
        <strong>SELLO Y FIRMA DEL NOTARIO</strong>
        <br><br>
        <span style="font-size:10pt; color:#999;">(Espacio reservado para sello y firma del notario público)</span>
    </div>

    <!-- FIRMAS DE LAS PARTES -->
    <div class="firmas">
        <div class="seccion-titulo">Firmas de las Partes</div>
        
        @if($acto->partes && $acto->partes->count() > 0)
            @foreach($acto->partes as $parte)
            <div class="firma-box">
                <div class="firma-linea">
                    <strong>{{ strtoupper($parte->rol) }}</strong><br>
                    {{ $parte->nombre_completo }}<br>
                    {{ $parte->tipo_documento_label }}: {{ $parte->numero_documento }}
                </div>
            </div>
            @endforeach
        @else
            <!-- Firmas genéricas si no hay partes -->
            @if(isset($datos['poderdante']))
            <div class="firma-box">
                <div class="firma-linea">
                    <strong>PODERDANTE</strong><br>
                    {{ $datos['poderdante'] }}
                </div>
            </div>
            @endif
            
            @if(isset($datos['apoderado']))
            <div class="firma-box">
                <div class="firma-linea">
                    <strong>APODERADO</strong><br>
                    {{ $datos['apoderado'] }}
                </div>
            </div>
            @endif
            
            @if(!isset($datos['poderdante']) && !isset($datos['apoderado']))
            <div class="firma-box">
                <div class="firma-linea">
                    <strong>FIRMA</strong>
                </div>
            </div>
            <div class="firma-box">
                <div class="firma-linea">
                    <strong>FIRMA</strong>
                </div>
            </div>
            @endif
        @endif
    </div>

</body>
</html>
