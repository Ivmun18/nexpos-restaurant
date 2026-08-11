<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        size: A4;
        margin-top: 4cm;
        margin-right: 3cm;
        margin-bottom: 2.5cm;
        margin-left: 3cm;
    }
    body {
        font-family: Verdana, Geneva, sans-serif;
        font-size: 8pt;
        line-height: 1.25;
        color: #000;
        margin: 0;
        padding: 0;
    }
    .container { padding: 0; }
    .parrafo { text-align: justify; margin-bottom: 6pt; }
</style>
</head>
<body>
<div class="container">

<p class="parrafo"><strong>PARTE NOTARIAL</strong></p>

<p class="parrafo"><strong>INSTRUMENTO :</strong> Nº {{ strtoupper($d['num_instrumento'] ?? '______') }}.- = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>MINUTA :</strong> Nº {{ $d['num_minuta'] ?? '______' }}.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>COMPRA VENTA</strong></p>

<p class="parrafo"><strong>VENDEDOR :</strong> {{ strtoupper($d['vendedor_nombre'] ?? '') }}. = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>COMPRADOR :</strong> {{ strtoupper($d['comprador_nombre'] ?? '') }}{{ isset($d['comprador2_nombre']) && $d['comprador2_nombre'] ? ' Y ' . strtoupper($d['comprador2_nombre']) : '' }}. = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *</p>

<p class="parrafo"><strong>INTRODUCCIÓN:</strong> = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">EN EL DISTRITO, PROVINCIA Y DEPARTAMENTO DE {{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, A LOS <strong>{{ strtoupper($d['fecha_letras'] ?? '______') }}</strong>; ANTE MÍ, <strong>{{ strtoupper($empresa->minuta_notario_nombre ?? '') }}</strong>, ABOGADO-NOTARIO DE {{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, CON DOCUMENTO NACIONAL DE IDENTIDAD Nº {{ $empresa->minuta_notario_dni ?? '________' }}, SUFRAGANTE, NOMBRADO CON RESOLUCIÓN MINISTERIAL NRO. {{ $empresa->minuta_resolucion_ministerial ?? '' }} DE FECHA {{ $empresa->minuta_fecha_resolucion ?? '' }}, CON REGISTRO Nº {{ $empresa->minuta_registro_notario ?? '' }} DEL COLEGIO DE NOTARIOS DE {{ mb_strtoupper($empresa->minuta_colegio_notarios ?? 'HUÁNUCO Y PASCO', 'UTF-8') }}, <strong>COMPARECEN</strong>: </p>

<p class="parrafo">- DON/DOÑA <strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI Nº {{ $d['vendedor_dni'] ?? '' }}, {{ strtoupper($d['vendedor_profesion'] ?? '') }}, {{ strtoupper($d['vendedor_estado_civil'] ?? '') }}, CON DOMICILIO EN {{ strtoupper($d['vendedor_domicilio'] ?? '') }}, QUIEN PROCEDE POR SU PROPIO DERECHO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">- DON/DOÑA <strong>{{ strtoupper($d['comprador_nombre'] ?? '') }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI Nº {{ $d['comprador_dni'] ?? '' }}, {{ strtoupper($d['comprador_profesion'] ?? '') }}, {{ strtoupper($d['comprador_estado_civil'] ?? '') }}, CON DOMICILIO EN {{ strtoupper($d['comprador_domicilio'] ?? '') }}, QUIEN PROCEDE POR SU PROPIO DERECHO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

@if(!empty($d['comprador2_nombre']))
<p class="parrafo">- DON/DOÑA <strong>{{ strtoupper($d['comprador2_nombre']) }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI Nº {{ $d['comprador2_dni'] ?? '' }}, {{ strtoupper($d['comprador2_profesion'] ?? '') }}, {{ strtoupper($d['comprador2_estado_civil'] ?? '') }}, CON DOMICILIO EN {{ strtoupper($d['comprador2_domicilio'] ?? '') }}, QUIEN PROCEDE POR SU PROPIO DERECHO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>
@endif

<p class="parrafo">LOS COMPARECIENTES SON MAYORES DE EDAD, SUFRAGANTES, HÁBILES PARA CONTRATAR E INTELIGENTES EN EL IDIOMA CASTELLANO, QUIENES SE OBLIGAN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO SUFICIENTE, DE LO QUE DOY FE.- ASÍ COMO DE HABER CONSTATADO QUE PROCEDEN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO CON QUE SE OBLIGA CONFORME A LA LEY DEL NOTARIADO, ASIMISMO SE ADVIRTIÓ SOBRE LOS EFECTOS LEGALES DEL PRESENTE INSTRUMENTO PÚBLICO NOTARIAL, DE CONFORMIDAD AL ARTÍCULO VEINTISIETE DEL DECRETO LEGISLATIVO NÚMERO MIL CUARENTA Y NUEVE Y ME ENTREGAN UNA MINUTA DE COMPRA VENTA, PARA QUE SU TENOR SE ELEVE A ESCRITURA PÚBLICA LA CUAL ARCHIVO EN SU LEGAJO RESPECTIVO, BAJO EL NÚMERO RESPECTIVO, SIENDO SU CONTENIDO LITERAL COMO SIGUE: = =</p>

<p class="parrafo"><strong>SEÑOR NOTARIO:</strong> = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">SÍRVASE UD. EXTENDER EN SU REGISTRO DE ESCRITURAS PÚBLICAS UNA DE COMPRA VENTA DE PREDIO QUE CELEBRAN, DE UNA PARTE, <strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, IDENTIFICADO(A) CON DOCUMENTO NACIONAL DE IDENTIDAD N° {{ $d['vendedor_dni'] ?? '' }}, PERUANO(A), DE ESTADO CIVIL {{ strtoupper($d['vendedor_estado_civil'] ?? '') }}, DE OCUPACIÓN {{ strtoupper($d['vendedor_profesion'] ?? '') }}, DOMICILIADO(A) EN {{ strtoupper($d['vendedor_domicilio'] ?? '') }}, A QUIEN EN ADELANTE SE LE DENOMINARÁ <strong>EL VENDEDOR</strong> Y, DE OTRA PARTE, <strong>{{ strtoupper($d['comprador_nombre'] ?? '') }}</strong>, IDENTIFICADO(A) CON DOCUMENTO NACIONAL DE IDENTIDAD N° {{ $d['comprador_dni'] ?? '' }}, PERUANO(A), DE ESTADO CIVIL {{ strtoupper($d['comprador_estado_civil'] ?? '') }}, DE OCUPACIÓN {{ strtoupper($d['comprador_profesion'] ?? '') }}{{ !empty($d['comprador2_nombre']) ? ', Y ' . strtoupper($d['comprador2_nombre']) . ', IDENTIFICADO(A) CON DOCUMENTO NACIONAL DE IDENTIDAD N° ' . ($d['comprador2_dni'] ?? '') . ', PERUANO(A), DE ESTADO CIVIL ' . strtoupper($d['comprador2_estado_civil'] ?? '') . ', DE OCUPACIÓN ' . strtoupper($d['comprador2_profesion'] ?? '') . ', AMBOS DOMICILIADOS EN ' . strtoupper($d['comprador_domicilio'] ?? '') . ', A QUIENES EN ADELANTE SE LES DENOMINARÁ LOS COMPRADORES' : ', DOMICILIADO(A) EN ' . strtoupper($d['comprador_domicilio'] ?? '') . ', A QUIEN EN ADELANTE SE LE DENOMINARÁ EL COMPRADOR' }}; EN LOS TÉRMINOS Y CONDICIONES DE LAS CLÁUSULAS SIGUIENTES: = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>PRIMERA:</strong> EL VENDEDOR ES PROPIETARIO, {{ strtoupper($d['predio_descripcion'] ?? '') }}, INSCRITO EN LA PARTIDA REGISTRAL N° {{ $d['predio_partida'] ?? '' }} DEL REGISTRO DE PREDIOS DE LA OFICINA REGISTRAL DE {{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, EN LA QUE CONSTA SUS DEMÁS CARACTERÍSTICAS FÍSICAS. = = = = = = = = = = = = = = = = = = = = = =</p>

@if(!empty($d['antecedente_registral']))
<p class="parrafo">{{ strtoupper($d['antecedente_registral']) }} = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>
@endif

<p class="parrafo"><strong>SEGUNDA:</strong> POR EL MÉRITO DE ESTE CONTRATO EL VENDEDOR DA EN VENTA REAL Y ENAJENACIÓN PERPETUA A FAVOR DE {{ !empty($d['comprador2_nombre']) ? 'LOS COMPRADORES EN PARTES IGUALES' : 'EL COMPRADOR' }} EL PREDIO DESCRITO EN LA CLÁUSULA PRIMERA. DICHA VENTA ES, AD CORPUS, POR LO QUE, COMPRENDE TODO LO INHERENTE Y ACCESORIO, ESTO ES LOS AIRES, ENTRADAS, SALIDAS, USOS, COSTUMBRES, SERVIDUMBRES Y TODO CUANTO DE HECHO O POR DERECHO LE CORRESPONDA A DICHO INMUEBLE, SIN RESERVA NI LIMITACIÓN ALGUNA. = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>TERCERA:</strong> EL PRECIO TOTAL DE VENTA POR EL PREDIO DESCRITO EN LA CLÁUSULA PRIMERA DE ESTE DOCUMENTO, PACTADO DE COMÚN ACUERDO, ES DE S/ {{ number_format(floatval($d['precio_total'] ?? 0), 2) }} ({{ strtoupper($d['precio_total_letras'] ?? '') }}), {{ strtoupper($d['forma_pago_detalle'] ?? '') }} POR LO QUE ÉSTE DECLARA TOTALMENTE CANCELADO EL PRECIO DE VENTA. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>CUARTA:</strong> AMBAS PARTES DECLARAN QUE EXISTE LA MÁS JUSTA Y PERFECTA EQUIVALENCIA ENTRE EL PRECIO PACTADO Y EL VALOR REAL DEL INMUEBLE, MATERIA DE VENTA, Y QUE, SI ALGUNA DIFERENCIA HUBIERE DE MÁS O DE MENOS, QUE AL MOMENTO NO SE ADVIERTE, SE HACEN DE ELLA MUTUA GRACIA Y RECÍPROCA DONACIÓN, RENUNCIANDO DESDE AHORA EN FORMA EXPRESA A TODA ACCIÓN O EXCEPCIÓN QUE, POR ERROR, DOLO, U OTRA CAUSA CUALQUIERA TIENDA A INVALIDAR ESTE CONTRATO, ASÍ COMO A LOS PLAZOS PARA INTERPONERLAS. = = = = = = = = </p>

<p class="parrafo"><strong>QUINTA:</strong> EL VENDEDOR DECLARA QUE SOBRE EL PREDIO QUE ENAJENA NO PESA CARGA, EMBARGO, MEDIDA JUDICIAL O EXTRAJUDICIAL, NI GRAVAMEN ALGUNO QUE EN CUALQUIER FORMA AFECTE O LIMITE EL DERECHO DE SU LIBRE USO Y DISPOSICIÓN. EN TODO CASO EL VENDEDOR, SE OBLIGA AL SANEAMIENTO DE LEY. = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>SEXTA:</strong> EL VENDEDOR DECLARA QUE RESPECTO DEL PREDIO OBJETO DE ESTA VENTA SE ENCUENTRAN AL DÍA EN EL PAGO DEL IMPUESTO AL PATRIMONIO PREDIAL Y DE CUALQUIER OTRO TRIBUTO QUE PUDIEREN AFECTAR AL INMUEBLE TRANSFERIDO; SIENDO DE CARGO DE {{ !empty($d['comprador2_nombre']) ? 'LOS COMPRADORES' : 'EL COMPRADOR' }} EL PAGO DE LOS MISMOS A PARTIR DE LA FECHA DE ESTA MINUTA. = = = = = = </p>

<p class="parrafo"><strong>SÉPTIMA:</strong> EN TODO LO NO PREVISTO EN ESTA MINUTA SE APLICARÁN LAS NORMAS PERTINENTES DEL CÓDIGO CIVIL. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">SÍRVASE UD. SEÑOR NOTARIO, AGREGAR LO QUE FUERA DE LEY. = = = = = = = = = = = = = = = </p>

<p class="parrafo">{{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, {{ $d['fecha_minuta'] ?? '' }}. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">FIRMAS Y HUELLAS DACTILARES DE: <strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, VENDEDOR.- <strong>{{ strtoupper($d['comprador_nombre'] ?? '') }}</strong>, COMPRADOR(A).- {{ !empty($d['comprador2_nombre']) ? strtoupper($d['comprador2_nombre']) . ', COMPRADOR(A).-' : '' }} = </p>

<p class="parrafo">UNA FIRMA Y UN SELLO DE: {{ strtoupper($d['abogado_nombre'] ?? '') }}. ABOGADO. REG. ICAH. {{ $d['abogado_cau'] ?? '' }}.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>ANOTACIÓN.</strong> = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">{{ strtoupper($d['anotacion'] ?? 'LA COMPRAVENTA SE ENCUENTRA INAFECTO AL PAGO DEL IMPUESTO A LA RENTA DE SEGUNDA CATEGORÍA, ELÉVESE A ESCRITURA PÚBLICA PREVIA LAS FORMALIDADES DE LEY.') }} = = = = = = = </p>

<p class="parrafo">{{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, {{ $d['fecha_minuta'] ?? '' }}. = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">FIRMADO: {{ strtoupper($empresa->minuta_notario_nombre ?? '') }}.- ABOGADO - NOTARIO.- SELLO NOTARIAL.- = = = </p>

<p class="parrafo"><strong>CONSTANCIA.-</strong> SE DEJA CONSTANCIA QUE LOS OTORGANTES HAN SIDO INSTRUIDOS DE LOS ALCANCES Y EFECTOS LEGALES QUE PRODUCE EL PRESENTE INSTRUMENTO, RELEVANDO DE TODA RESPONSABILIDAD AL NOTARIO QUE INTERVIENE EN LA PRESENTE. DOY FE.- = = = = = = = = = </p>

<p class="parrafo"><strong>CONSTANCIA.-</strong> CONFORME AL ARTÍCULO 55 DEL DECRETO LEGISLATIVO N° 1049 "LEY DEL NOTARIADO", MODIFICADO MEDIANTE DECRETO LEGISLATIVO 1232; CERTIFICO QUE HE CORROBORADO LA IDENTIDAD DE TODOS LOS COMPARECIENTES A TRAVÉS DE COMPARACIONES BIOMÉTRICAS DE SUS HUELLAS DACTILARES A TRAVÉS DEL SERVICIO DE LA RENIEC, LAS CUALES ARROJAN RESULTADO POSITIVO A LA CORRESPONDENCIA DE LA HUELLAS LO QUE ARCHIVO EN MI LEGAJO RESPECTIVO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>CONSTANCIA.-</strong> DEJO CONSTANCIA DE HABER TOMADO LA ACCIÓN DE CONTROL Y DEBIDA DILIGENCIA EN MATERIA DE PREVENCIÓN DE LAVADO DE ACTIVOS, PREGUNTANDO A TODOS LOS COMPARECIENTES EN RELACIÓN AL ORIGEN DE LOS FONDOS, BIENES Y ACTIVOS INVOLUCRADOS EN LA PRESENTE TRANSACCIÓN, AL QUE RESPONDEN QUE TODO FUE LÍCITAMENTE ADQUIRIDO; TAMBIÉN SE LES EXIGIÓ LA UTILIZACIÓN DE LOS MEDIOS DE PAGO DISPUESTO POR EL ARTÍCULO QUINTO DE LA LEY NÚMERO 28194. EL NOTARIO QUE AUTORIZA SEÑALA QUE LOS CONTRATANTES UTILIZARON EL MEDIO DE PAGO DE TIPO: {{ strtoupper($d['medios_pago_tipo'] ?? 'DEPÓSITO EN CUENTA') }}, ESTABLECIDOS EN EL ART 5 DE LA MENCIONADA NORMA LEGAL Y CONFORME LOS DETALLES ESTABLECIDOS EN EL ACTO JURÍDICO INCORPORADO. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>CONCLUSIÓN.-</strong> FORMALIZADO EL PRESENTE INSTRUMENTO, DI A CONOCER SU OBJETO Y TENOR A LOS COMPARECIENTES POR LECTURA QUE LES HICE DE PRINCIPIO A FIN, LUEGO DE LO CUAL FIRMAN Y SE RATIFICAN EN SU CONTENIDO Y PROCEDEN A FIRMARLO EN SEÑAL DE CONFORMIDAD, JUNTO CONMIGO. EL PRESENTE INSTRUMENTO SE HALLA EXTENDIDO DE FOJAS <strong>{{ $d['fojas_inicio'] ?? '______' }}</strong> A FOJAS <strong>{{ $d['fojas_fin'] ?? '______' }}</strong>, PAPEL SELLADO DE SERIE <strong>{{ $d['papel_serie_inicio'] ?? '______' }}</strong> A LA SERIE <strong>{{ $d['papel_serie_fin'] ?? '______' }}</strong> DE LO QUE DOY FE.- HACIENDO CONSTAR QUE EL PROCESO DE TOMA DE FIRMAS DE TODOS LOS CONTRATANTES Y FORMALIZACIÓN DE LA PRESENTE ESCRITURA CONCLUYE HOY A LOS <strong>{{ strtoupper($d['fecha_letras'] ?? '______') }}</strong> DE LO QUE DOY FE. = = = = = = </p>

<p class="parrafo">FIRMAS Y HUELLAS DACTILARES DE: <strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, FIRMO: EL {{ $d['fecha_firma'] ?? '______' }}.- <strong>{{ strtoupper($d['comprador_nombre'] ?? '') }}</strong>, FIRMO: EL {{ $d['fecha_firma'] ?? '______' }}.- {{ !empty($d['comprador2_nombre']) ? strtoupper($d['comprador2_nombre']) . ', FIRMO: EL ' . ($d['fecha_firma'] ?? '______') . '.' : '' }} = = = = = = = = = = = = = = = = </p>

<p class="parrafo">FECHA DE SUSCRIPCIÓN O AUTORIZACIÓN DEL NOTARIO: {{ strtoupper($d['fecha_letras'] ?? '______') }}, DOY FE.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo">FIRMADO: {{ strtoupper($empresa->minuta_notario_nombre ?? '') }}, ABOGADO - NOTARIO PUBLICO DE {{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}. UN SELLO NOTARIAL.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">CONCUERDA, CON EL ORIGINAL DE SU REFERENCIA AL QUE ME REMITO EN CASO NECESARIO, EXPIDO EL PRESENTE PARTE NOTARIAL A SOLICITUD DE LA PARTE INTERESADA, PREVIA CONFRONTACIÓN DE LEY. DOY FE.- = = = = = = = = = = = = = = = = = = = = = = = = </p>

<p class="parrafo"><strong>{{ mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO', 'UTF-8') }}, {{ $d['fecha_minuta'] ?? '' }}</strong></p>

</div>
</body>
</html>
