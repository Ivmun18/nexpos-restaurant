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
    .container {
        padding: 0;
    }
    .parrafo {
        text-align: justify;
        margin-bottom: 6pt;
    }
</style>
</head>
<body>
<div class="container">

<p class="parrafo"><strong>PRIMER TESTIMONIO</strong></p>

<p class="parrafo"><strong>INSTRUMENTO :</strong> N° {{ $d['num_instrumento'] ?? '______' }}.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>MINUTA :</strong> N° {{ $d['num_minuta'] ?? '______' }}.- = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>COMPRAVENTA DE BIEN FUTURO</strong></p>

<p class="parrafo"><strong>VENDEDOR :</strong> {{ strtoupper($d['vendedor_razon_social'] ?? $d['vendedor_nombre'] ?? '') }} -  = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>COMPRADOR(A) :</strong> {{ strtoupper($d['comprador_nombre'] ?? '') }}.- = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *</p>

<p class="parrafo"><strong>INTRODUCCIÓN:</strong>  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">EN EL DISTRITO, PROVINCIA Y DEPARTAMENTO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, A LOS <strong>{{ strtoupper($d['fecha_letras'] ?? '______') }}</strong>; ANTE MÍ, <strong>{{ strtoupper($empresa->minuta_notario_nombre ?? '') }}</strong>, ABOGADO-NOTARIO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, CON DOCUMENTO NACIONAL DE IDENTIDAD Nº {{ $empresa->minuta_notario_dni ?? '________' }}, SUFRAGANTE, NOMBRADO CON RESOLUCIÓN MINISTERIAL NRO. {{ $d['resolucion_ministerial'] ?? '____' }} DE FECHA {{ $d['fecha_resolucion'] ?? '______' }}, CON REGISTRO Nº {{ $d['registro_notario'] ?? '____' }} DEL COLEGIO DE NOTARIOS DE {{ strtoupper($d['colegio_notarios'] ?? 'HUÁNUCO Y PASCO') }}, <strong>COMPARECEN</strong>:  = = = = = = = = = = = = =</p>

@if(isset($d['vendedor_tipo']) && $d['vendedor_tipo'] === 'empresa')
<p class="parrafo">- <strong>{{ strtoupper($d['vendedor_razon_social']) }}</strong>, CON RUC N° <strong>{{ $d['vendedor_ruc'] }}</strong> CON DOMICILIO EN {{ strtoupper($d['vendedor_domicilio']) }}, DEBIDAMENTE REPRESENTADO POR SU {{ strtoupper($d['representante_cargo'] ?? 'GERENTE GENERAL') }} DON <strong>{{ strtoupper($d['representante_nombre']) }}</strong>, PERUANO, IDENTIFICADO CON DNI Nº <strong>{{ $d['representante_dni'] }}</strong>, {{ strtoupper($d['representante_estado_civil'] ?? 'SOLTERO') }}, {{ strtoupper($d['representante_profesion'] ?? '________') }}, CON DOMICILIO EN {{ strtoupper($d['representante_domicilio']) }}, CUYAS FACULTADES CORREN INSCRITAS EN LA PARTIDA ELECTRÓNICA Nº <strong>{{ $d['vendedor_partida_registral'] ?? '________' }}</strong>, DEL REGISTRO DE PERSONAS JURÍDICAS DE LA OFICINA REGISTRAL DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}; QUIEN PROCEDE POR SU PROPIO DERECHO, A QUIEN IDENTIFICO DE LO QUE DOY FE. -  = = = = = = = = = = = = = = = = = = = = =</p>
@else
<p class="parrafo">- DON/DOÑA <strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, PERUANO(A), IDENTIFICADO(A) CON DNI N° <strong>{{ $d['vendedor_dni'] ?? '' }}</strong>, {{ strtoupper($d['vendedor_estado_civil'] ?? 'SOLTERO') }}, CON DOMICILIO EN {{ strtoupper($d['vendedor_domicilio'] ?? '') }}, DE TRÁNSITO POR ESTA CIUDAD; QUIEN PROCEDE POR SU PROPIO DERECHO, A QUIEN IDENTIFICO DE LO QUE DOY FE. -  = = = = = = = = = = = = = = = = = = = = =</p>
@endif

<p class="parrafo">- DOÑA/DON <strong>{{ strtoupper($d['comprador_nombre']) }}</strong>, PERUANA/O, IDENTIFICADA/O CON DNI N° <strong>{{ $d['comprador_dni'] }}</strong>, {{ strtoupper($d['comprador_estado_civil'] ?? 'SOLTERA') }}, {{ strtoupper($d['comprador_profesion'] ?? '________') }}, CON DOMICILIO EN {{ strtoupper($d['comprador_domicilio']) }}, DE TRÁNSITO POR ESTA CIUDAD; QUIEN PROCEDE POR SU PROPIO DERECHO, A QUIEN IDENTIFICO DE LO QUE DOY FE. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">LOS COMPARECIENTES SON MAYORES DE EDAD, SUFRAGANTES, HÁBILES PARA CONTRATAR E INTELIGENTES EN EL IDIOMA CASTELLANO, QUIENES SE OBLIGAN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO SUFICIENTE, DE LO QUE DOY FE. ASÍ COMO DE HABER CONSTATADO QUE PROCEDEN CON CAPACIDAD, LIBERTAD Y CONOCIMIENTO CON QUE SE OBLIGA CONFORME A LA LEY DEL NOTARIADO, ASIMISMO SE ADVIRTIÓ SOBRE LOS EFECTOS LEGALES DEL PRESENTE INSTRUMENTO PÚBLICO NOTARIAL, DE CONFORMIDAD AL ARTÍCULO VEINTISIETE DEL DECRETO LEGISLATIVO NÚMERO MIL CUARENTA Y NUEVE Y ME ENTREGAN UNA MINUTA DE <strong>COMPRAVENTA DE BIEN FUTURO</strong>, PARA QUE SU TENOR SE ELEVE A ESCRITURA PÚBLICA LA CUAL ARCHIVO EN SU LEGAJO RESPECTIVO, BAJO EL NÚMERO RESPECTIVO, SIENDO SU CONTENIDO LITERAL COMO SIGUE:  = = = = = = = = =</p>

<p class="parrafo"><strong>MINUTA.-</strong> <strong>SEÑOR NOTARIO:</strong> SÍRVASE UD. EXTENDER EN SU REGISTRO DE ESCRITURAS PÚBLICAS UNA DE <strong>COMPRAVENTA DE BIEN FUTURO</strong> QUE CELEBRAN DE UNA PARTE
@if(isset($d['vendedor_tipo']) && $d['vendedor_tipo'] === 'empresa')
LA SOCIEDAD DENOMINADA <strong>{{ strtoupper($d['vendedor_razon_social']) }}</strong>, CON RUC N° <strong>{{ $d['vendedor_ruc'] }}</strong>, CON DOMICILIO EN EL {{ strtoupper($d['vendedor_domicilio']) }}, INSCRITO EN LA PARTIDA REGISTRAL N° <strong>{{ $d['vendedor_partida_registral'] ?? '________' }}</strong> DEL REGISTRO DE PERSONAS JURÍDICAS DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, DEBIDAMENTE REPRESENTADO POR SU {{ strtoupper($d['representante_cargo'] ?? 'GERENTE GENERAL') }} <strong>{{ strtoupper($d['representante_nombre']) }}</strong>, DE NACIONALIDAD PERUANO, IDENTIFICADO CON DOCUMENTO NACIONAL DE IDENTIDAD N° <strong>{{ $d['representante_dni'] }}</strong>, DE ESTADO CIVIL {{ strtoupper($d['representante_estado_civil'] ?? 'SOLTERO') }}, DE PROFESIÓN {{ strtoupper($d['representante_profesion'] ?? '________') }}, CON DOMICILIO EN {{ strtoupper($d['representante_domicilio']) }}, A QUIEN EN ADELANTE SE LE DENOMINARÁ <strong>EL VENDEDOR</strong>
@else
<strong>{{ strtoupper($d['vendedor_nombre'] ?? '') }}</strong>, DE NACIONALIDAD PERUANA, IDENTIFICADO CON DOCUMENTO NACIONAL DE IDENTIDAD N° <strong>{{ $d['vendedor_dni'] ?? '' }}</strong>, DE ESTADO CIVIL {{ strtoupper($d['vendedor_estado_civil'] ?? 'SOLTERO') }}, CON DOMICILIO EN {{ strtoupper($d['vendedor_domicilio'] ?? '') }}, A QUIEN EN ADELANTE SE LE DENOMINARÁ <strong>EL VENDEDOR</strong>
@endif
Y, DE OTRA PARTE, <strong>{{ strtoupper($d['comprador_nombre']) }}</strong>, DE NACIONALIDAD PERUANA, IDENTIFICADO CON DOCUMENTO NACIONAL DE IDENTIDAD N° <strong>{{ $d['comprador_dni'] }}</strong>, DE ESTADO CIVIL {{ strtoupper($d['comprador_estado_civil'] ?? 'SOLTERO') }}, DE PROFESIÓN {{ strtoupper($d['comprador_profesion'] ?? '________') }}, CON DOMICILIO EN {{ strtoupper($d['comprador_domicilio']) }}, DE TRÁNSITO POR ESTA CIUDAD, A QUIEN EN ADELANTE SE LE DENOMINARÁ <strong>LA COMPRADORA</strong>; EN LOS TÉRMINOS Y CONDICIONES DE LAS CLÁUSULAS SIGUIENTES:</p>

<p class="parrafo"><strong>PRIMERA. -</strong> <strong>EL VENDEDOR</strong> ES COPROPIETARIO DEL PREDIO SIGNADO COMO LOTE Nº 1 EN EL PLANO DE LOTIZACIÓN DEL FUNDO RÚSTICO ESPERANZA, DEL DISTRITO DE AMARILIS, PROVINCIA Y DEPARTAMENTO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, CON PARTIDA REGISTRAL N° <strong>{{ $d['predio_partida'] }}</strong> DE LA OFICINA REGISTRAL DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, EN LA QUE CONSTA SUS DEMAS CARACTERÍSTICAS FÍSICAS.  = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>SEGUNDA. -</strong> EL PREDIO DESCRITO EN LA CLÁUSULA QUE PRECEDE, <strong>EL VENDEDOR</strong> VIENE DESARROLLANDO UN PROYECTO DE HABILITACIÓN URBANA, MODALIDAD B, DENOMINADO <strong>"{{ strtoupper($d['proyecto_descripcion'] ?? '') }}"</strong>, CUYA LICENCIA FUE SOLICITADA ANTE LA {{ strtoupper($d['proyecto_municipalidad'] ?? 'MUNICIPALIDAD DISTRITAL DE AMARILIS') }}, PROVINCIA Y DEPARTAMENTO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, CON <strong>EXPEDIENTE N° {{ $d['proyecto_expediente'] ?? '______' }}</strong>, PRESENTADO CON FECHA {{ strtoupper($d['proyecto_fecha'] ?? '______') }}.</p>

<p class="parrafo">LA DISTRIBUCIÓN DE MANZANAS Y LOTES, ASÍ COMO LAS ÁREAS, LINDEROS Y MEDIDAS PERIMÉTRICAS DE CADA UNO DE LOS LOTES, SE ENCUENTRA DETALLADO EN EL PLANO DE TRAZADO Y LOTIZACIÓN Y SU RESPECTIVA MEMORIA DESCRIPTIVA, AMBOS ELABORADOS Y AUTORIZADO POR EL ARQUITECTO {{ strtoupper($d['proyecto_arquitecto'] ?? '________') }}, LO QUE ES DE CONOCIMIENTO DE <strong>LA COMPRADORA</strong>.  = = = = = =</p>

<p class="parrafo">EN TAL SENTIDO, <strong>LA COMPRADORA</strong> DECLARA CONOCER QUE, A LA FECHA DE LA SUSCRIPCIÓN DEL PRESENTE CONTRATO, TODOS LOS LOTES QUE CONFORMAN EL PROYECTO DE HABILITACIÓN URBANA ANTES SEÑALADO, INCLUIDO EL LOTE MATERIA DE ESTA MINUTA, TIENEN LA CONDICIÓN DE BIENES FUTUROS.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>TERCERA. -</strong> EN TALES CONDICIONES, DENTRO DEL PROYECTO DE HABILITACIÓN URBANA, SEÑALADA EN LA CLÁUSULA QUE PRECEDE, SE ENCUENTRA EL <strong>{{ strtoupper($d['lote_descripcion'] ?? '') }}</strong>, DE <strong>{{ $d['lote_area'] ?? '______' }} ({{ strtoupper($d['lote_area_letras'] ?? '______') }})</strong>, CON LINDEROS Y MEDIDAS PERIMÉTRICAS SIGUIENTES:  = = = = =</p>

<p class="parrafo">- <strong>POR EL FRENTE:</strong> COLINDA CON {{ strtoupper($d['lindero_frente'] ?? '') }}, CON {{ $d['medida_frente'] ?? '' }} ML.  = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">- <strong>POR LA DERECHA:</strong> COLINDA CON {{ strtoupper($d['lindero_derecha'] ?? '') }}, CON {{ $d['medida_derecha'] ?? '' }} ML.  = = = = = = = = = = = = = = = =</p>

<p class="parrafo">- <strong>POR LA IZQUIERDA:</strong> COLINDA CON {{ strtoupper($d['lindero_izquierda'] ?? '') }}, CON {{ $d['medida_izquierda'] ?? '' }} ML.  = = = = = = = = = = = = = = =</p>

<p class="parrafo">- <strong>POR EL FONDO:</strong> COLINDA CON {{ strtoupper($d['lindero_fondo'] ?? '') }}, CON {{ $d['medida_fondo'] ?? '' }} ML.  = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CUARTA. –</strong> EN VIRTUD DEL PRESENTE CONTRATO, <strong>EL VENDEDOR</strong> TRANSFIERE EN VENTA REAL Y ENAJENACIÓN PERPETUA A FAVOR DE <strong>LA COMPRADORA</strong>, EL LOTE DESCRITO EN LA CLÁUSULA TERCERA DE ESTA MINUTA, CUYA EXISTENCIA Y EFICACIA DE ESTE CONTRATO SE SUJETA A LO ESTABLECIDO EN ESTE DOCUMENTO. DICHA TRANSFERENCIA SE REALIZA AD CORPUS, POR LO QUE, CUMPLIDA LA CONDICIÓN SUSPENSIVA CONTENIDA EN ESTA MINUTA, COMPRENDERÁ SUS USOS, COSTUMBRES, ENTRADAS, SALIDAS, AIRES, SUELO, SUB SUELO, SOBRE SUELO Y TODO CUANTO DE HECHO Y POR DERECHO LE CORRESPONDA, SIN RESERVA NI LIMITACIÓN ALGUNA.  = = = = = = = =</p>

<p class="parrafo"><strong>QUINTA. –</strong> LAS PARTES, DE COMÚN ACUERDO, ESTABLECEN QUE EL PRECIO TOTAL DE VENTA POR EL PREDIO MATERIA DE ESTA MINUTA ES DE <strong>S/ {{ number_format(floatval($d['precio_total'] ?? 0), 2) }} ({{ strtoupper($d['precio_total_letras'] ?? '') }})</strong> TOTALMENTE CANCELADO POR <strong>LA COMPRADORA</strong> MEDIANTE {{ strtoupper($d['forma_pago_detalle'] ?? '') }} POR LO QUE ÉSTA DECLARA TOTALMENTE CANCELADO EL PRECIO DE VENTA DEL PREDIO OBJETO DE ESTE CONTRATO.  = = = = =</p>

<p class="parrafo"><strong>SEXTA. -</strong> LAS PARTES DECLARAN QUE ENTRE EL VALOR DEL INMUEBLE Y EL PRECIO PACTADO EXISTE LA MÁS JUSTA Y PERFECTA EQUIVALENCIA, POR LO QUE DE HABER ALGUNA DIFERENCIA DE MÁS O DE MENOS, QUE EN EL PRESENTE NO ADVIERTEN, SE HACEN MUTUA GRACIA Y RECÍPROCA DONACIÓN, RENUNCIANDO A CUALQUIER ACCIÓN QUE TENGA POR OBJETO INVALIDAR LOS EFECTOS DEL PRESENTE CONTRATO, ASÍ COMO A LOS PLAZOS PARA INTERPONERLA.  = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>SÉPTIMA. -</strong> EL PRESENTE CONTRATO QUEDA SUJETO A LA CONDICIÓN SUSPENSIVA DE QUE EL BIEN LLEGUE A TENER EXISTENCIA, EN APLICACIÓN DEL ARTÍCULO 1534º DEL CÓDIGO CIVIL.  = = = = =</p>

<p class="parrafo">POR TANTO, DE MANERA CONSENSUADA, LAS PARTES ESTABLECEN QUE EL LOTE OBJETO DE ESTA MINUTA LLEGARÁ A TENER EXISTENCIA LEGAL CON LA RESOLUCIÓN MUNICIPAL QUE APRUEBE LA RECEPCIÓN DE OBRAS DE DICHA HABILITACIÓN URBANA Y/O QUE APRUEBE SU INDEPENDIZACIÓN EN EL REGISTRO DE PREDIOS DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, ACTO CON EL CUAL, ESTE DOCUMENTO SURTIRÁ PLENOS EFECTOS EN FORMA AUTOMÁTICA, SIN EXIGIRSE DECLARACIÓN ADMINISTRATIVA, MUNICIPAL O REGISTRAL ADICIONAL, NI LA SUSCRIPCIÓN DE DOCUMENTO PÚBLICO O PRIVADO ALGUNO DE PARTE DE LOS OTORGANTES DE ESTE DOCUMENTO, PUES LA EFICACIA DE ESTA TRANSFERENCIA OPERARÁ DE MANERA AUTOMÁTICA. EN TAL SENTIDO, EL INSTRUMENTO PÚBLICO EN EL QUE CONSTE ESTA MINUTA SERÁ SUFICIENTE PARA LA INSCRIPCIÓN REGISTRAL DE LA TRANSFERENCIA DEL LOTE SUB MATERIA A FAVOR DE <strong>LA COMPRADORA</strong>.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>OCTAVA. -</strong> <strong>EL VENDEDOR</strong> SE OBLIGA A OBTENER LA AUTORIZACIÓN MUNICIPAL SEÑALADA EN LA CLÁUSULA QUE PRECEDE DENTRO DEL PLAZO MÁXIMO DE {{ strtoupper($d['plazo_anos'] ?? 'TRES') }} AÑOS, CONTADOS A PARTIR DE LA FECHA DE ESTA MINUTA. PARA TAL FIN, <strong>LA COMPRADORA</strong> DEBERÁ REALIZAR TODAS LAS ACTIVIDADES NECESARIAS A SU CARGO POR ANTE LA MUNICIPALIDAD DISTRITAL DE AMARILIS – {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>NOVENA. -</strong> QUEDA ENTENDIDO QUE, AUN CUANDO NO SE HAYA EMITIDO LA AUTORIZACIÓN MUNICIPAL REFERIDO EN LA CLÁUSULA SÉPTIMA, <strong>LA COMPRADORA</strong> PUEDE ASUMIR LA POSESIÓN DEL LOTE OBJETO DE ESTA MINUTA, PUES LES HA SIDO SEÑALADO LA UBICACIÓN FÍSICA DEL LOTE OBJETO DE COMPRAVENTA, DEBIENDO EN TAL CASO COMUNICAR, POR ESCRITO, DE DICHA CIRCUNSTANCIA A EL VENDEDOR. SIN EMBARGO, DICHA POSESIÓN NO DEBERÁ OBSTACULIZAR LA OBTENCIÓN DE LA APROBACIÓN DEL PROYECTO DE HABILITACIÓN URBANA, EN CUYO SUPUESTO, DEBERÁN DEVOLVER DICHA POSESIÓN A <strong>EL VENDEDOR</strong> DE MANERA INCONDICIONAL, SOLO PARA FINES DE DICHA HABILITACIÓN URBANA, QUEDANDO INCÓLUME EL DERECHO DE PROPIEDAD TRANSFERIDA A FAVOR DE <strong>LA COMPRADORA</strong>. = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DECIMO. -</strong> UNA VEZ QUE SE HAYA CUMPLIDO LA CONDICIÓN SUSPENSIVA, <strong>LA COMPRADORA</strong> DEBERÁ DECLARAR LA TRANSFERENCIA CONTENIDA EN ESTE DOCUMENTO POR ANTE LA MUNICIPALIDAD DISTRITAL DE AMARILIS – {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, PARA LOS FINES DEL PAGO DEL IMPUESTO PREDIAL Y DEMÁS IMPUESTOS QUE AFECTEN AL PREDIO SUB MATERIA, ASÍ COMO INSCRIBIR ESTA COMPRA VENTA ANTE EL REGISTRO DE PREDIOS DE LA OFICINA REGISTRAL DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}.  = = = = = = = = = = = = = =</p>

<p class="parrafo">NO OBSTANTE, CORRERÁ DE CARGO DE <strong>LA COMPRADORA</strong> EL PAGO DEL IMPUESTO PREDIAL Y DEMÁS ARBITRIOS A PARTIR DE LA FECHA DE ESTA MINUTA, SIENDO QUE, HASTA LA FECHA DE ESTA MINUTA LA VENDEDORA HA CUMPLIDO CON EL PAGO DE DICHO IMPUESTO Y DEMÁS ARBITRIOS MUNICIPALES.</p>

<p class="parrafo"><strong>DECIMO PRIMERA. -</strong> <strong>EL VENDEDOR</strong> DECLARA QUE SOBRE EL LOTE OBJETO DE ESTA MINUTA, NO PESA NINGÚN GRAVAMEN, CARGA, MEDIDA JUDICIAL NI EXTRAJUDICIAL ALGUNA, NI EN GENERAL, NINGÚN ACTO O CONTRATO LIMITATIVO DE SUS DERECHOS DE DOMINIO Y LIBRE DISPOSICIÓN, OBLIGÁNDOSE EN TODO CASO AL SANEAMIENTO EN LOS TÉRMINOS MÁS AMPLIOS PREVISTOS EN LA LEY.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DECIMO SEGUNDA. -</strong> <strong>EL VENDEDOR</strong> SE OBLIGA A:  = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">A) INSCRIBIR EL PREDIO EN SU ÁREA MATRIZ DESCRITO EN LA CLÁUSULA PRIMERA POR ANTE EL REGISTRO DE PREDIOS DE LA OFICINA REGISTRAL DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}.  = = = = = = = = = = = = = = = =</p>

<p class="parrafo">B) CONTINUAR Y CULMINAR EL TRÁMITE DE HABILITACIÓN URBANA REFERIDA EN LA CLÁUSULA SEGUNDA DE ESTE DOCUMENTO, CON LA DEBIDA DILIGENCIA, PRESENTANDO Y/O SUBSANANDO LOS DOCUMENTOS Y DEMÁS REQUERIMIENTOS DE LA AUTORIDAD MUNICIPAL EN LOS PLAZOS ESTABLECIDOS POR LA LEY O POR LA AUTORIDAD MUNICIPAL, A FIN DE OBTENER LA AUTORIZACIÓN MUNICIPAL DE SU OBJETO DENTRO DEL PLAZO PREVISTO EN LA CLÁUSULA OCTAVA DE ESTE DOCUMENTO.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">C) SOLICITAR LA INSCRIPCIÓN DE LA RESOLUCIÓN O AUTORIZACIÓN MUNICIPAL QUE OBTENGA, RELATIVO AL PROYECTO DE HABILITACIÓN URBANA, POR ANTE EL REGISTRO DE PREDIOS DE LA OFICINA REGISTRAL DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, DENTRO DEL PLAZO DE 15 DÍAS DE HABERSE OBTENIDO DICHA AUTORIZACIÓN.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">D) ENTREGAR LA POSESIÓN DEL LOTE OBJETO DE ESTE DOCUMENTO A <strong>LA COMPRADORA</strong> LUEGO DE LA FECHA QUE LLEGUE A TENER EXISTENCIA LEGAL, SI HASTA DICHO MOMENTO <strong>EL VENDEDOR</strong> NO HUBIERE ASUMIDO DICHA POSESIÓN.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DECIMO TERCERA. -</strong> EN EL SUPUESTO QUE UNA DE LAS PARTES NO CUMPLA CON LAS OBLIGACIONES QUE SE DERIVAN DE ESTE CONTRATO, LA OTRA PARTE PODRÁ, ALTERNATIVAMENTE, EXIGIR EL CUMPLIMIENTO DE LA OBLIGACIÓN DE SU CONTRAPARTE EN LOS TÉRMINOS CONTENIDOS EN ESTE CONTRATO O RESOLVER EL CONTRATO. EN ESTE ÚLTIMO SUPUESTO, LA PARTE QUE DESEA RESOLVERLA CURSARÁ PREVIAMENTE COMUNICACIÓN POR CONDUCTO NOTARIAL A LA OTRA PARTE, REQUIRIENDO EL CUMPLIMIENTO DE LA OBLIGACIÓN INCUMPLIDA EN EL PLAZO DE 15 DÍAS DE RECIBIDA DICHA COMUNICACIÓN. SI PERSISTIERA DICHO INCUMPLIMIENTO DARÁ POR RESUELTO ESTE CONTRATO.  =</p>

<p class="parrafo">EN EL CASO QUE ESTE CONTRATO QUEDE RESUELTO, LA PARTE QUE LA CAUSÓ RESTITUIRÁ, DE MANERA INCONDICIONAL, A SU CONTRAPARTE LAS PRESTACIONES QUE HUBIERE EFECTUADO.  = = = = = = =</p>

<p class="parrafo"><strong>DECIMA CUARTA. -</strong> LAS PARTES ACUERDAN QUE TODOS LOS GASTOS Y TRIBUTOS QUE SE ORIGINE A LA CELEBRACIÓN, FORMALIZACIÓN Y EJECUCIÓN DEL PRESENTE CONTRATO SERÁN ASUMIDOS <strong>LA COMPRADORA</strong>.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DÉCIMO QUINTA. -</strong> PARA EFECTOS DE CUALQUIER CONTROVERSIA QUE SE GENERE CON MOTIVO DE LA CELEBRACIÓN Y EJECUCIÓN DE ESTE CONTRATO, LAS PARTES SE SOMETEN A LA COMPETENCIA TERRITORIAL DE LOS JUECES Y TRIBUNALES DE LA CIUDAD DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, RENUNCIANDO A LOS JUECES DE SUS RESPECTIVOS DOMICILIOS.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DECIMO SEXTA. -</strong> PARA LA VALIDEZ DE TODAS LAS COMUNICACIONES Y NOTIFICACIONES A LAS PARTES, CON MOTIVO DE LA EJECUCIÓN DE ESTE CONTRATO, AMBAS SEÑALAN COMO SUS RESPECTIVOS DOMICILIOS LOS INDICADOS EN LA INTRODUCCIÓN DE ESTE DOCUMENTO. EL CAMBIO DE DOMICILIO DE CUALQUIERA DE LAS PARTES SURTIRÁ EFECTO DESDE LA FECHA DE COMUNICACIÓN DE DICHO CAMBIO A LA OTRA PARTE, POR VÍA NOTARIAL.  = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>DECIMO SÉPTIMA. - </strong>EN TODO LO NO PREVISTO POR LAS PARTES EN EL PRESENTE CONTRATO, AMBAS SE SOMETEN A LO ESTABLECIDO POR LAS NORMAS DEL CÓDIGO CIVIL Y DEMÁS DEL SISTEMA JURÍDICO QUE RESULTEN APLICABLES.  = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">SÍRVASE UD. SEÑOR NOTARIO, AGREGAR LO QUE FUERA DE LEY. - {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, {{ $d['fecha_minuta'] ?? '' }}.  </p>

<p class="parrafo"><strong>FIRMAS Y HUELLAS DACTILARES DE:</strong> {{ strtoupper($d['vendedor_razon_social'] ?? $d['vendedor_nombre'] ?? '') }} - {{ strtoupper($d['representante_nombre'] ?? '') }}, VENDEDOR. - {{ strtoupper($d['comprador_nombre'] ?? '') }}, COMPRADORA. -  = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>UNA FIRMA Y UN SELLO DE:</strong> {{ strtoupper($d['abogado_nombre'] ?? '') }}. ABOGADO. CAU. {{ $d['abogado_cau'] ?? '' }}.-  = = =</p>

<p class="parrafo"><strong>ANOTACIÓN. -</strong>  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">LA PRESENTE SE ENCUENTRA <strong>AFECTA</strong> AL PAGO DEL <strong>IMPUESTO DE ALCABALA</strong>. ACREDITE SU PAGO Y ELÉVESE A ESCRITURA PÚBLICA.  = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo">{{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, {{ $d['fecha_minuta'] ?? '' }}.-  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>FIRMADO:</strong> {{ strtoupper($empresa->minuta_notario_nombre ?? '') }}. - ABOGADO - NOTARIO. - SELLO NOTARIAL. –  = = =</p>

<p class="parrafo"><strong>CONSTANCIA.</strong> – LAS PARTES EXHIBIERON MEDIO DE PAGO BANCARIO, EN CUMPLIMIENTO DE LO DISPUESTO POR LA LEY Nº 30730, CONSISTENTE EN {{ strtoupper($d['medios_pago_descripcion'] ?? '______') }}, CUYAS COPIAS SE ADJUNTA A SU LEGAJO. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA.</strong> –DOY FE, HABER TENIDO A LA VISTA EL RECIBO DE PAGO DEL IMPUESTO DE ALCABALA POR LA TRANSFERENCIA DEL <strong>{{ strtoupper($d['lote_descripcion'] ?? '') }}</strong> – <strong>"{{ strtoupper($d['proyecto_descripcion'] ?? '') }}"</strong>, UBICADO EN EL DISTRITO DE AMARILIS, PROVINCIA Y DEPARTAMENTO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, ASCENDIENTE A LA SUMA <strong>S/ {{ $d['alcabala_monto'] ?? '______' }}</strong>, DE FECHA {{ strtoupper($d['alcabala_fecha'] ?? '______') }}, <strong>RECIBO DE CAJA N° {{ $d['alcabala_recibo'] ?? '______' }}</strong> – MUNICIPALIDAD DISTRITAL DE AMARILIS – {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}. - CUYA COPIA SE ADJUNTA A SU LEGADO. DOY FE. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA. -</strong> SE DEJA CONSTANCIA QUE LOS OTORGANTES HAN SIDO INSTRUIDOS DE LOS ALCANCES Y EFECTOS LEGALES QUE PRODUCE EL PRESENTE INSTRUMENTO, RELEVANDO DE TODA RESPONSABILIDAD AL NOTARIO QUE INTERVIENE EN LA PRESENTE. DOY FE. -  = = = = = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA.</strong> - CONFORME AL ARTÍCULO 55 INCISO "B" DEL DECRETO LEGISLATIVO N° 1049 "LEY DEL NOTARIADO", MODIFICADO MEDIANTE DECRETO LEGISLATIVO 1232; CERTIFICO QUE HE CORROBORADO LA IDENTIDAD DE TODOS LOS COMPARECIENTES A TRAVÉS DE COMPARACIONES BIOMÉTRICAS DE SUS HUELLAS DACTILARES A TRAVÉS DEL SERVICIO DE LA RENIEC, LAS CUALES ARROJAN RESULTADO POSITIVO A LA CORRESPONDENCIA DE LA HUELLAS LOS QUE ARCHIVO EN MI LEGAJO RESPECTIVO. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONSTANCIA.</strong> - DEJO CONSTANCIA DE HABER TOMADO LA ACCIÓN DE CONTROL Y DEBIDA DILIGENCIA EN MATERIA DE PREVENCIÓN DE LAVADO DE ACTIVOS, PREGUNTANDO A TODOS LOS COMPARECIENTES EN RELACIÓN AL ORIGEN DE LOS FONDOS, BIENES Y ACTIVOS INVOLUCRADOS EN LA PRESENTE TRANSACCIÓN, AL QUE RESPONDEN QUE TODO FUE LÍCITAMENTE ADQUIRIDO; TAMBIÉN SE LES EXIGIÓ LA UTILIZACIÓN DE LOS MEDIOS DE PAGO DISPUESTO POR EL ARTÍCULO QUINTO DE LA LEY NÚMERO 28194. EL NOTARIO QUE AUTORIZA SEÑALA QUE LOS CONTRATANTES UTILIZARON COMO MEDIO DE PAGO {{ strtoupper($d['medios_pago_tipo'] ?? 'DEPÓSITO BANCARIO') }}, EL CUAL SE ENCUENTRA DESCRITO EN LA CLÁUSULA QUINTA DE LA MINUTA CONTENIDA EN ESTE INSTRUMENTO. -  = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONCLUSIÓN. - </strong>FORMALIZADO EL PRESENTE INSTRUMENTO, DI A CONOCER SU OBJETO Y TENOR A LOS COMPARECIENTES POR LECTURA QUE TODO EL LES HICE DE PRINCIPIO A FIN, LUEGO DE LO CUAL FIRMAN Y RATIFICAN EN SU CONTENIDO Y PROCEDEN A FIRMARLO EN SEÑAL DE CONFORMIDAD, JUNTO CONMIGO. EL PRESENTE INSTRUMENTO SE HALLA EXTENDIDO DE FOJAS <strong>{{ $d['fojas_inicio'] ?? '______' }}</strong> A FOJAS <strong>{{ $d['fojas_fin'] ?? '______' }}</strong>, PAPEL SELLADO DE SERIE <strong>{{ $d['papel_serie_inicio'] ?? '______' }}</strong> A LA SERIE <strong>{{ $d['papel_serie_fin'] ?? '______' }}</strong> DE LO QUE DOY FE. - HACIENDO CONSTAR QUE EL PROCESO DE TOMA DE FIRMAS DE TODOS LOS CONTRATANTES Y FORMALIZACIÓN DE LA PRESENTE ESCRITURA CONCLUYE HOY A LOS <strong>{{ strtoupper($d['fecha_letras'] ?? '______') }}</strong> DE LO QUE DOY FE. –WAQ  = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>FIRMAS Y HUELLAS DACTILARES DE:</strong> {{ strtoupper($d['vendedor_razon_social'] ?? $d['vendedor_nombre'] ?? '') }} - {{ strtoupper($d['representante_nombre'] ?? '') }}, FIRMÓ EL: {{ $d['fecha_firma'] ?? '______' }}. - {{ strtoupper($d['comprador_nombre'] ?? '') }}, FIRMÓ EL: {{ $d['fecha_firma'] ?? '______' }}. -  = = = = = =</p>

<p class="parrafo"><strong>FECHA DE SUSCRIPCIÓN O AUTORIZACIÓN DEL NOTARIO:</strong> A LOS {{ strtoupper($d['fecha_letras'] ?? '______') }}, DOY FE. -  = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>FIRMADO:</strong> {{ strtoupper($empresa->minuta_notario_nombre ?? '') }}, ABOGADO - NOTARIO PÚBLICO DE {{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}. UN SELLO NOTARIAL. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>CONCUERDA,</strong> CON EL ORIGINAL DE SU REFERENCIA AL QUE ME REMITO EN CASO NECESARIO, EXPIDO EL PRESENTE <strong>PRIMER TESTIMONIO</strong> A SOLICITUD DE LA PARTE INTERESADA, PREVIA CONFRONTACIÓN DE LEY. DOY FÉ. -  = = = = = = = = = = = = = = = = = = = = = = = = = = = = =</p>

<p class="parrafo"><strong>{{ strtoupper($d['ciudad'] ?? 'HUÁNUCO') }}, {{ $d['fecha_minuta'] ?? '' }}</strong>.</p>

</div>
</body>
</html>
