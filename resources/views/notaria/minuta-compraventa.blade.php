<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page { size: A4; margin-top: 4cm; margin-right: 3cm; margin-bottom: 2.5cm; margin-left: 3cm; }
    body { font-family: Verdana, Geneva, sans-serif; font-size: 8pt; line-height: 1.25; color: #000; margin: 0; padding: 0; }
    .parrafo { text-align: justify; margin-bottom: 6pt; line-height: 1.25; }
    .estrellas { text-align: center; margin: 8pt 0; }
    .anotacion { margin-top: 20pt; }
</style>
</head>
<body>
<?php
$d = array_merge($vendedor ?? [], $datos ?? []);
$notario_nombre  = 'ALEX SAÚL HERRERA ARIAS';
$notario_dni     = '44052670';
$notario_rm      = '0044-2025-JUS';
$notario_rm_fecha= '06 DE FEBRERO DEL 2025';
$notario_registro= '042';
$notario_colegio = 'COLEGIO DE NOTARIOS DE HUÁNUCO Y PASCO';
$ciudad          = mb_strtoupper($d['ciudad'] ?? 'HUÁNUCO');
$vendedor_nombre  = mb_strtoupper($d['vendedor_nombre'] ?? '');
$vendedor_dni     = $d['vendedor_dni'] ?? '';
$vendedor_ec      = mb_strtoupper($d['vendedor_estado_civil'] ?? 'SOLTERO');
$vendedor_prof    = mb_strtoupper($d['vendedor_profesion'] ?? 'INDEPENDIENTE');
$vendedor_dom     = mb_strtoupper($d['vendedor_domicilio'] ?? '');
$conyuge_nombre   = mb_strtoupper($d['conyuge_vendedor'] ?? '');
$conyuge_dni      = $d['conyuge_vendedor_dni'] ?? '';
$comprador_nombre = mb_strtoupper($d['comprador_nombre'] ?? '');
$comprador_dni    = $d['comprador_dni'] ?? '';
$comprador_ec     = mb_strtoupper($d['comprador_estado_civil'] ?? 'SOLTERO');
$comprador_prof   = mb_strtoupper($d['comprador_profesion'] ?? 'INDEPENDIENTE');
$comprador_dom    = mb_strtoupper($d['comprador_domicilio'] ?? '');
$predio_desc      = mb_strtoupper($d['predio_descripcion'] ?? $d['lote_descripcion'] ?? '');
$predio_partida   = $d['predio_partida'] ?? '';
$lote_area        = $d['lote_area'] ?? '';
$lote_area_letras = mb_strtoupper($d['lote_area_letras'] ?? '');
$lindero_frente   = mb_strtoupper($d['lindero_frente'] ?? '');
$medida_frente    = $d['medida_frente'] ?? '';
$lindero_derecha  = mb_strtoupper($d['lindero_derecha'] ?? '');
$medida_derecha   = $d['medida_derecha'] ?? '';
$lindero_izquierda= mb_strtoupper($d['lindero_izquierda'] ?? '');
$medida_izquierda = $d['medida_izquierda'] ?? '';
$lindero_fondo    = mb_strtoupper($d['lindero_fondo'] ?? '');
$medida_fondo     = $d['medida_fondo'] ?? '';
$precio_total     = $d['precio_total'] ?? '0';
$precio_letras    = mb_strtoupper($d['precio_total_letras'] ?? '');
$forma_pago       = mb_strtoupper($d['forma_pago_detalle'] ?? '');
$fecha_minuta     = mb_strtoupper($d['fecha_minuta'] ?? '');
$kardex           = $acto->numero_expediente ?? '';
?>

<p class="parrafo"><strong>PARTE NOTARIAL</strong></p>
<p class="parrafo"><strong>KARDEX&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $kardex }}.-</strong> = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>
<p class="parrafo"><strong>INSTRUMENTO&nbsp;: COMPRA VENTA</strong></p>
<p class="parrafo"><strong>VENDEDOR&nbsp;&nbsp;&nbsp;: {{ $vendedor_nombre }}{{ $conyuge_nombre ? ' Y CÓNYUGE ' . $conyuge_nombre : '' }}.</strong> = = = = = = = = = = = = = = = = =</p>
<p class="parrafo"><strong>COMPRADOR&nbsp;&nbsp;: {{ $comprador_nombre }}.</strong> = = = = = = = = = = = = = = = = = = =</p>
<p class="estrellas">*********************************************************************************</p>

<p class="parrafo"><strong>INTRODUCCIÓN</strong>: = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">EN EL DISTRITO, PROVINCIA Y DEPARTAMENTO DE {{ $ciudad }}, A LOS <strong>{{ $fecha_minuta ?: '_____ DÍAS DEL MES DE _____ DEL DOS MIL _____' }}</strong>; ANTE MÍ, <strong>{{ $notario_nombre }}</strong>, ABOGADO-NOTARIO DE {{ $ciudad }}, CON DOCUMENTO NACIONAL DE IDENTIDAD Nº {{ $notario_dni }}, SUFRAGANTE, NOMBRADO CON RESOLUCIÓN MINISTERIAL NRO. {{ $notario_rm }} DE FECHA {{ $notario_rm_fecha }}, CON REGISTRO Nº {{ $notario_registro }} DEL {{ $notario_colegio }}, <strong>COMPARECEN</strong>: = = = = = = = = = = = = =</p>

<p class="parrafo">- DON/DOÑA <strong>{{ $vendedor_nombre }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI Nº <strong>{{ $vendedor_dni }}</strong>, {{ $vendedor_prof }}, {{ $vendedor_ec }}{{ $conyuge_nombre ? ', Y SU CÓNYUGE DOÑA <strong>' . $conyuge_nombre . '</strong>, PERUANA, IDENTIFICADA CON DNI Nº <strong>' . $conyuge_dni . '</strong>' : '' }}, CON DOMICILIO EN {{ $vendedor_dom }}, QUIENES PROCEDEN POR SU PROPIO DERECHO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">- DON/DOÑA <strong>{{ $comprador_nombre }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI Nº <strong>{{ $comprador_dni }}</strong>, {{ $comprador_prof }}, {{ $comprador_ec }}, CON DOMICILIO EN {{ $comprador_dom }}, QUIEN PROCEDE POR SU PROPIO DERECHO.-</p>

<p class="parrafo">LOS COMPARECIENTES SON MAYORES DE EDAD, SUFRAGANTES, HÁBILES PARA CONTRATAR E INTELIGENTES EN EL IDIOMA CASTELLANO, QUIENES SE OBLIGAN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO SUFICIENTE, DE LO QUE DOY FE. ASÍ COMO DE HABER CONSTATADO QUE PROCEDEN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO CON QUE SE OBLIGAN CONFORME A LA LEY DEL NOTARIADO, ASIMISMO SE ADVIRTIÓ SOBRE LOS EFECTOS LEGALES DEL PRESENTE INSTRUMENTO PÚBLICO NOTARIAL, DE CONFORMIDAD AL ARTÍCULO VEINTISIETE DEL DECRETO LEGISLATIVO NÚMERO MIL CUARENTA Y NUEVE Y ME ENTREGAN UNA MINUTA DE <strong>COMPRA VENTA</strong>, PARA QUE SU TENOR SE ELEVE A ESCRITURA PÚBLICA LA CUAL ARCHIVO EN SU LEGAJO RESPECTIVO, BAJO EL NÚMERO RESPECTIVO, SIENDO SU CONTENIDO LITERAL COMO SIGUE: = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>SEÑOR NOTARIO: </strong>= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">SÍRVASE UD. EXTENDER EN SU REGISTRO DE ESCRITURAS PÚBLICAS UNA DE <strong>COMPRA VENTA</strong> QUE CELEBRAN DE UNA PARTE, <strong>{{ $vendedor_nombre }}</strong>{{ $conyuge_nombre ? ', Y CÓNYUGE <strong>' . $conyuge_nombre . '</strong>' : '' }}, IDENTIFICADO(A) CON DOCUMENTO NACIONAL DE IDENTIDAD N° <strong>{{ $vendedor_dni }}</strong>{{ $conyuge_dni ? ', E IDENTIFICADA CON DNI N° <strong>' . $conyuge_dni . '</strong>' : '' }}, PERUANO(A), DE OCUPACIÓN {{ $vendedor_prof }}, DE ESTADO CIVIL {{ $vendedor_ec }}, CON DOMICILIO EN {{ $vendedor_dom }}, A QUIEN(ES) EN ADELANTE SE LE(S) DENOMINARÁ <strong>EL/LOS VENDEDOR(ES)</strong> Y, DE OTRA PARTE, <strong>{{ $comprador_nombre }}</strong>, IDENTIFICADO(A) CON DOCUMENTO NACIONAL DE IDENTIDAD N° <strong>{{ $comprador_dni }}</strong>, PERUANO(A), DE ESTADO CIVIL {{ $comprador_ec }}, DE OCUPACIÓN {{ $comprador_prof }}, DOMICILIADO(A) EN {{ $comprador_dom }}, A QUIEN EN ADELANTE SE LE DENOMINARÁ <strong>EL/LA COMPRADOR(A)</strong>; EN LOS TÉRMINOS Y CONDICIONES DE LAS CLÁUSULAS SIGUIENTES: =</p>

<p class="parrafo"><strong>PRIMERA:</strong> EL/LOS VENDEDOR(ES) ES(SON) PROPIETARIO(S) DEL {{ $predio_desc }}, INSCRITO EN LA PARTIDA REGISTRAL N° <strong>{{ $predio_partida }}</strong> DEL REGISTRO DE PREDIOS DE LA OFICINA REGISTRAL DE {{ $ciudad }}, EN LA QUE CONSTAN SUS DEMÁS CARACTERÍSTICAS FÍSICAS. = = = = = = =</p>

<p class="parrafo"><strong>SEGUNDA:</strong> POR EL MÉRITO DE ESTE CONTRATO EL/LOS VENDEDOR(ES) DA(N) EN VENTA REAL Y ENAJENACIÓN PERPETUA A FAVOR DE EL/LA COMPRADOR(A) EL PREDIO DESCRITO EN LA CLÁUSULA ANTERIOR, CONSISTENTE EN UN LOTE DE <strong>{{ $lote_area }}</strong> ({{ $lote_area_letras }}), CON LINDEROS Y MEDIDAS PERIMÉTRICAS SIGUIENTES: POR EL FRENTE CON {{ $lindero_frente }} EN {{ $medida_frente }}, POR LA DERECHA ENTRANDO CON {{ $lindero_derecha }} EN {{ $medida_derecha }}, POR LA IZQUIERDA ENTRANDO CON {{ $lindero_izquierda }} EN {{ $medida_izquierda }} Y POR EL FONDO CON {{ $lindero_fondo }} EN {{ $medida_fondo }}. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">DICHA VENTA ES, <em>AD CORPUS</em>, POR LO QUE, COMPRENDE TODO LO INHERENTE Y ACCESORIO, ESTO ES, LA FÁBRICA, AIRE, ENTRADAS, SALIDAS, USOS, COSTUMBRES, SERVIDUMBRES Y TODO CUANTO DE HECHO O POR DERECHO LE CORRESPONDA A DICHO INMUEBLE, SIN RESERVA NI LIMITACIÓN ALGUNA. =</p>

<p class="parrafo"><strong>TERCERA:</strong> EL PRECIO TOTAL DE VENTA POR EL PREDIO DESCRITO EN LA CLÁUSULA PRIMERA DE ESTE DOCUMENTO, PACTADO DE COMÚN ACUERDO, ES DE <strong>S/ {{ number_format(floatval($precio_total), 2) }} ({{ $precio_letras }})</strong>, CANCELADO EN SU TOTALIDAD POR EL/LA COMPRADOR(A) A FAVOR DE EL/LOS VENDEDOR(ES) MEDIANTE {{ $forma_pago }}, POR LO QUE ÉSTOS DECLARAN TOTALMENTE CANCELADO EL PRECIO DE VENTA. = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CUARTA:</strong> AMBAS PARTES DECLARAN QUE EXISTE LA MÁS JUSTA Y PERFECTA EQUIVALENCIA ENTRE EL PRECIO PACTADO Y EL VALOR REAL DEL INMUEBLE, MATERIA DE VENTA, Y QUE, SI ALGUNA DIFERENCIA HUBIERE DE MÁS O DE MENOS, QUE AL MOMENTO NO SE ADVIERTE, SE HACEN DE ELLA MUTUA GRACIA Y RECÍPROCA DONACIÓN, RENUNCIANDO DESDE AHORA EN FORMA EXPRESA A TODA ACCIÓN O EXCEPCIÓN QUE, POR ERROR, DOLO, U OTRA CAUSA CUALQUIERA TIENDA A INVALIDAR ESTE CONTRATO, ASÍ COMO A LOS PLAZOS PARA INTERPONERLAS. = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>QUINTA:</strong> EL/LOS VENDEDOR(ES) DECLARA(N) QUE SOBRE EL INMUEBLE QUE ENAJENA(N) NO PESA CARGA, EMBARGO, MEDIDA JUDICIAL O EXTRAJUDICIAL, NI GRAVAMEN ALGUNO QUE EN CUALQUIER FORMA AFECTE O LIMITE EL DERECHO DE SU LIBRE USO Y DISPOSICIÓN. EN TODO CASO EL/LOS VENDEDOR(ES), SE OBLIGA(N) AL SANEAMIENTO DE LEY. = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>SEXTA:</strong> EL/LOS VENDEDOR(ES) DECLARA(N) QUE RESPECTO DEL PREDIO OBJETO DE ESTA VENTA SE ENCUENTRAN AL DÍA EN EL PAGO DEL IMPUESTO AL PATRIMONIO PREDIAL Y DE CUALQUIER OTRO TRIBUTO QUE PUDIEREN AFECTAR AL INMUEBLE TRANSFERIDO; SIENDO DE CARGO DE EL/LA COMPRADOR(A) EL PAGO DE LOS MISMOS A PARTIR DE LA FECHA DE ESTA MINUTA. = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>SÉPTIMA:</strong> EN TODO LO NO PREVISTO EN ESTA MINUTA SE APLICARÁN LAS NORMAS PERTINENTES DEL CÓDIGO CIVIL. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">SÍRVASE UD. SEÑOR NOTARIO, AGREGAR LO QUE FUERA DE LEY. = = = = = = = = = = = = = = = =</p>

<p class="parrafo">{{ $ciudad }}, {{ $fecha_minuta }}.</p>

<p class="parrafo"><strong>FIRMA Y HUELLA DACTILAR DE: {{ $vendedor_nombre }}, VENDEDOR(A){{ $conyuge_nombre ? '.- ' . $conyuge_nombre . ', VENDEDOR(A)' : '' }}.- {{ $comprador_nombre }}, COMPRADOR(A).-</strong> = = = = = = = = =</p>

<p class="parrafo"><strong>UNA FIRMA Y UN SELLO DE: ABOGADO AUTORIZANTE.-</strong> =</p>

<p class="parrafo anotacion"><strong>ANOTACIÓN</strong>. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">LA COMPRAVENTA SE ENCUENTRA AFECTA AL PAGO DEL IMPUESTO DE ALCABALA. ELÉVESE A ESCRITURA PÚBLICA PREVIA LAS FORMALIDADES DE LEY. = = = = = = = = = =</p>

<p class="parrafo">{{ $ciudad }}, {{ $fecha_minuta }}.</p>

<p class="parrafo"><strong>FIRMADO: {{ $notario_nombre }}.- ABOGADO - NOTARIO.- SELLO NOTARIAL.-</strong> = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA.-</strong> SE DEJA CONSTANCIA QUE LOS OTORGANTES HAN SIDO INSTRUIDOS DE LOS ALCANCES Y EFECTOS LEGALES QUE PRODUCE EL PRESENTE INSTRUMENTO, RELEVANDO DE TODA RESPONSABILIDAD AL NOTARIO QUE INTERVIENE EN LA PRESENTE. DOY FE.- = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA</strong>.- CONFORME AL ARTÍCULO 55 DEL DECRETO LEGISLATIVO N° 1049 "LEY DEL NOTARIADO", MODIFICADO MEDIANTE DECRETO LEGISLATIVO 1232; CERTIFICO QUE HE CORROBORADO LA IDENTIDAD DE LOS COMPARECIENTES A TRAVÉS DE COMPARACIONES BIOMÉTRICAS DE SUS HUELLAS DACTILARES A TRAVÉS DEL SERVICIO DE LA RENIEC, LAS CUALES ARROJAN RESULTADO POSITIVO A LA CORRESPONDENCIA DE LAS HUELLAS LO QUE ARCHIVO EN MI LEGAJO RESPECTIVO.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONCLUSIÓN.-</strong> FORMALIZADO EL PRESENTE INSTRUMENTO, DI A CONOCER SU OBJETO Y TENOR A LOS COMPARECIENTES POR LECTURA QUE LES HICE DE PRINCIPIO A FIN, LUEGO DE LO CUAL FIRMAN Y SE RATIFICAN EN SU CONTENIDO Y PROCEDEN A FIRMARLO EN SEÑAL DE CONFORMIDAD, JUNTO CONMIGO. DE LO QUE DOY FE.- = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>FIRMADO: {{ $notario_nombre }}, ABOGADO - NOTARIO PÚBLICO DE {{ $ciudad }}. UN SELLO NOTARIAL.-</strong> = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONCUERDA,</strong> CON EL ORIGINAL DE SU REFERENCIA AL QUE ME REMITO EN CASO NECESARIO, EXPIDO EL PRESENTE <strong>PARTE NOTARIAL</strong> A SOLICITUD DE LA PARTE INTERESADA, PREVIA CONFRONTACIÓN DE LEY. DOY FÉ.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>{{ $ciudad }}, {{ $fecha_minuta }}.-</strong></p>

</body>
</html>
