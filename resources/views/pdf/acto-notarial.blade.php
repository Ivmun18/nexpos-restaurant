<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $acto->numero_expediente }}</title>
    <style>
        @page :right {
            margin: 3.5cm 2.5cm 2cm 2.5cm;
        }
        @page :left {
            margin: 4cm 2.5cm 2cm 2.5cm;
        }
        body {
            font-family: Verdana, sans-serif;
            font-size: 8pt;
            line-height: 1.25;
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
            @case('compra_venta') Compra Venta @break
            @case('compra_venta_bien_futuro') Compra Venta de Bien Futuro @break
            @case('compra_venta_hipoteca') Compra Venta con Hipoteca @break
            @case('compra_venta_alicuotas') Compra Venta de Alícuotas @break
            @case('aclaracion_compra_venta') Aclaración de Compra Venta @break
            @case('ratificacion_compra_venta') Ratificación de Compra Venta @break
            @case('contrato_preparatorio') Contrato Preparatorio @break
            @case('adjudicacion') Adjudicación @break
            @case('rectificacion_area') Rectificación de Área @break
            @case('particion') Partición @break
            @case('prescripcion_dominio') Prescripción de Dominio @break
            @case('donacion_inmueble') Donación de Inmueble @break
            @case('donacion_alicuotas') Donación de Alícuotas @break
            @case('donacion_vehiculo') Donación de Vehículo @break
            @case('transferencia_vehicular') Transferencia Vehicular @break
            @case('hipoteca') Hipoteca @break
            @case('mutuo_hipoteca') Mutuo con Hipoteca @break
            @case('poder') Poder @break
            @case('ampliacion_poder') Ampliación de Poder @break
            @case('revocatoria_poder') Revocatoria de Poder @break
            @case('constitucion_sac') Constitución de S.A.C. @break
            @case('constitucion_srl') Constitución de S.R.L. @break
            @case('constitucion_asociacion') Constitución de Asociación @break
            @case('aumento_capital') Aumento de Capital @break
            @case('transformacion_empresa') Transformación de Empresa @break
            @case('sustitucion_regimen') Sustitución de Régimen @break
            @case('cese_regimen') Cese de Régimen @break
            @case('testamento') Testamento @break
            @case('reconocimiento_paternidad') Reconocimiento de Paternidad @break
            @case('autorizacion_viaje') Autorización de Viaje @break
            @case('autorizacion_viaje_ext') Autorización de Viaje al Extranjero @break
            @case('divorcio') Divorcio @break
            @case('sucesion_intestada') Sucesión Intestada @break
            @case('certificacion_notarial') Certificación Notarial @break
            @case('legalizacion') Legalización @break
            @case('escritura_publica') Escritura Pública @break
            @case('notificacion') Notificación @break
            @case('certificado_domiciliario') Certificado Domiciliario @break
            @case('acta_no_contenciosa') Acta No Contenciosa @break
            @case('arrendamiento') Arrendamiento @break
            @case('carta_notarial') Carta Notarial @break
            @case('otro') Otro @break
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
