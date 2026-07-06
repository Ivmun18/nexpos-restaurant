<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    /* Formato oficial notarial - Verdana 8pt, interlineado 1.25, márgenes simétricos */
    @page {
        size: A4;
        margin-top: 4cm;    /* hojas impares - usamos el mayor para seguridad */
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
        /* DomPDF usa padding para simular márgenes de página */
        padding-top: 0;
        padding-right: 0;
        padding-bottom: 0;
        padding-left: 0;
    }
    .parrafo {
        text-align: justify;
        margin-bottom: 8pt;
        line-height: 1.25;
    }
    .clausula { font-weight: bold; }
    .firma-container { margin-top: 50pt; display: table; width: 100%; }
    .firma { display: table-cell; width: 50%; text-align: center; padding-top: 8pt; }
    .linea-firma { border-top: 1px solid #000; width: 180px; margin: 0 auto 4pt; }
    .anotacion { margin-top: 30pt; border-top: 1px solid #000; padding-top: 10pt; }
</style>
</head>
<body>
<div class="container">

<p class="parrafo"><strong>SEÑOR NOTARIO:</strong></p>

<p class="parrafo">Sírvase Ud. extender en su Registro de Escrituras Públicas una de <strong>COMPRA VENTA DE PREDIO</strong> que celebran de una parte
@if($d['vendedor_tipo'] === 'empresa')
<strong><u>{{ strtoupper($d['vendedor_razon_social']) }}</u></strong>, con RUC N° {{ $d['vendedor_ruc'] }}, con domicilio en {{ $d['vendedor_domicilio'] }},
inscrito en la partida registral N° {{ $d['vendedor_partida_registral'] ?? '___________' }} del Registro de Personas Jurídicas de {{ $d['ciudad'] ?? 'Huánuco' }},
a quien en adelante se le denominará <strong><u>LA VENDEDORA</u></strong> debidamente representada por su {{ $d['representante_cargo'] ?? 'Gerente General' }},
<strong>{{ strtoupper($d['representante_nombre']) }}</strong>, de nacionalidad peruana, identificado con DNI N° {{ $d['representante_dni'] }},
de estado civil {{ $d['representante_estado_civil'] ?? 'soltero' }}, de profesión {{ $d['representante_profesion'] ?? '___________' }},
con domicilio en {{ $d['representante_domicilio'] }};
@else
<strong><u>{{ strtoupper($d['vendedor_nombre']) }}</u></strong>, identificado con DNI N° {{ $d['vendedor_dni'] }},
de estado civil {{ $d['vendedor_estado_civil'] ?? 'soltero' }}, con domicilio en {{ $d['vendedor_domicilio'] }},
a quien en adelante se le denominará <strong><u>EL VENDEDOR</u></strong>;
@endif
y de otra parte <strong><u>{{ strtoupper($d['comprador_nombre']) }}</u></strong>, identificado(a) con Documento Nacional de Identidad N° {{ $d['comprador_dni'] }},
{{ $d['comprador_nacionalidad'] ?? 'peruano(a)' }}, de estado civil {{ $d['comprador_estado_civil'] }},
de profesión {{ $d['comprador_profesion'] }}, con domicilio en {{ $d['comprador_domicilio'] }},
a quien en adelante se le denominará <strong><u>LA COMPRADORA</u></strong>; en los términos y condiciones siguientes:</p>

<p class="parrafo"><span class="clausula">PRIMERO:</span> LA VENDEDORA es propietario del predio signado como {{ $d['predio_descripcion'] }},
con Partida Registral N° {{ $d['predio_partida'] }} de la Oficina Registral de {{ $d['ciudad'] ?? 'Huánuco' }},
en la que consta sus demás características.</p>

@if(!empty($d['es_bien_futuro']))
<p class="parrafo"><span class="clausula">SEGUNDO:</span> En el predio descrito en la cláusula que precede, LA VENDEDORA viene desarrollando un proyecto de
{{ $d['proyecto_descripcion'] }}, cuya licencia fue solicitada ante {{ $d['proyecto_municipalidad'] ?? 'la Municipalidad Distrital de Amarilis' }},
provincia y departamento de Huánuco, con Expediente N° {{ $d['proyecto_expediente'] }}, presentado con fecha {{ $d['proyecto_fecha'] }}.

La distribución de manzanas y lotes, así como las áreas, linderos y medidas perimétricas de cada uno de los lotes, se encuentra detallado en el plano de trazado y lotización
y su respectiva memoria descriptiva, elaborados por {{ $d['proyecto_arquitecto'] }}, lo que es de conocimiento de LA COMPRADORA.

En tal sentido, LA COMPRADORA declara conocer que, a la fecha de la suscripción del presente contrato, el lote materia de esta minuta tiene la condición de bien futuro.</p>
@endif

<p class="parrafo"><span class="clausula">{{ !empty($d['es_bien_futuro']) ? 'TERCERO' : 'SEGUNDO' }}:</span>
@if(!empty($d['es_bien_futuro']))
En tales condiciones, dentro del proyecto señalado, se encuentra el
@else
El predio materia de la presente compraventa es el
@endif
{{ $d['lote_descripcion'] }}, de {{ $d['lote_area'] }} ({{ $d['lote_area_letras'] }}), con linderos y medidas perimétricas siguientes:</p>

<table width="100%" style="margin-bottom:14px; font-size:12px;">
    <tr><td width="30%">- Por el Frente:</td><td>colinda con {{ $d['lindero_frente'] }}, con {{ $d['medida_frente'] }} ml.</td></tr>
    <tr><td>- Por la Derecha:</td><td>colinda con {{ $d['lindero_derecha'] }}, con {{ $d['medida_derecha'] }} ml.</td></tr>
    <tr><td>- Por la Izquierda:</td><td>colinda con {{ $d['lindero_izquierda'] }}, con {{ $d['medida_izquierda'] }} ml.</td></tr>
    <tr><td>- Por el Fondo:</td><td>colinda con {{ $d['lindero_fondo'] }}, con {{ $d['medida_fondo'] }} ml.</td></tr>
</table>

<p class="parrafo"><span class="clausula">{{ !empty($d['es_bien_futuro']) ? 'CUARTO' : 'TERCERO' }}:</span>
En virtud del presente contrato, LA VENDEDORA da en venta real y enajenación perpetua a favor de LA COMPRADORA el lote descrito en la cláusula precedente,
comprenderá sus usos, costumbres, entradas, salidas, aires, suelo, sub suelo, sobre suelo y todo cuanto de hecho y por derecho le corresponda, sin reserva ni limitación alguna.</p>

<p class="parrafo"><span class="clausula">{{ !empty($d['es_bien_futuro']) ? 'QUINTO' : 'CUARTO' }}:</span>
Las partes, de común acuerdo, establecen que el precio total de venta por el predio materia de esta minuta es de
<strong>S/ {{ number_format(floatval($d['precio_total']), 2) }} ({{ strtoupper($d['precio_total_letras']) }})</strong>,
suma de dinero que fue cancelada por LA COMPRADORA de la siguiente manera:</p>

<p class="parrafo" style="white-space: pre-line;">{{ $d['forma_pago_detalle'] }}</p>

<p class="parrafo">En tal sentido LA VENDEDORA declara totalmente cancelado el precio de venta.</p>

@if(!empty($d['es_bien_futuro']))
<p class="parrafo"><span class="clausula">SEXTO:</span> Las partes declaran que entre el valor del inmueble y el precio pactado existe la más justa y perfecta equivalencia,
por lo que de haber alguna diferencia, se hacen mutua gracia y recíproca donación, renunciando a cualquier acción que tenga por objeto invalidar los efectos del presente contrato.</p>

<p class="parrafo"><span class="clausula">SÉPTIMO:</span> Las partes acuerdan que, tratándose de una venta de bien futuro, el presente contrato queda sujeto a la condición suspensiva
de que el bien llegue a tener existencia, en aplicación del Artículo 1534° del Código Civil. El lote llegará a tener existencia legal con la resolución municipal que apruebe
la recepción de obras de la habilitación urbana y/o que apruebe su independización en el registro de predios de Huánuco.</p>

<p class="parrafo"><span class="clausula">OCTAVO:</span> EL VENDEDOR se obliga a obtener la autorización municipal dentro del plazo máximo de {{ $d['plazo_años'] ?? 'tres' }} años,
contados a partir de la fecha de esta minuta.</p>
@endif

@if(!empty($d['es_bien_futuro']))
<p class="parrafo"><span class="clausula">NOVENO:</span> Queda entendido que, aun cuando no se haya emitido la autorización municipal referida en la cláusula séptima,
LA COMPRADORA puede asumir la posesión del lote objeto de esta minuta, pues le ha sido señalado la ubicación física del lote objeto de compraventa,
debiendo en tal caso comunicar, por escrito, de dicha circunstancia al vendedor. Sin embargo, dicha posesión no deberá obstaculizar la obtención de la aprobación
del proyecto de habilitación urbana, en cuyo supuesto, deberán devolver dicha posesión al vendedor de manera incondicional, solo para fines de dicha habilitación urbana,
quedando incólume el derecho de propiedad transferida a favor de LA COMPRADORA.</p>

<p class="parrafo"><span class="clausula">DÉCIMO:</span> Una vez que se haya cumplido la condición suspensiva, LA COMPRADORA deberá declarar la transferencia
contenida en este documento por ante la municipalidad distrital de {{ $d['municipalidad_distrito'] ?? 'Amarilis' }} – {{ $d['ciudad'] ?? 'Huánuco' }},
para los fines del pago del impuesto predial y demás impuestos que afecten al predio sub materia, así como inscribir esta compra venta ante el registro de predios
de la oficina registral de {{ $d['ciudad'] ?? 'Huánuco' }}.

No obstante, correrá de cargo de LA COMPRADORA el pago del impuesto predial y demás arbitrios a partir de la fecha de esta minuta, siendo que, hasta la fecha
de esta minuta la vendedora ha cumplido con el pago de dicho impuesto y demás arbitrios municipales.</p>

<p class="parrafo"><span class="clausula">DÉCIMO PRIMERA:</span> LA VENDEDORA declara que sobre el lote objeto de esta minuta, no pesa ningún gravamen, carga,
medida judicial ni extrajudicial alguna, ni en general, ningún acto o contrato limitativo de sus derechos de dominio y libre disposición, obligándose en todo caso
al saneamiento en los términos más amplios previstos en la ley.</p>

<p class="parrafo"><span class="clausula">DÉCIMO SEGUNDA:</span> LA VENDEDORA se obliga a:</p>
<p class="parrafo">A) Inscribir el predio en su área matriz descrito en la cláusula primera por ante el registro de predios de la oficina registral de {{ $d['ciudad'] ?? 'Huánuco' }}.</p>
<p class="parrafo">B) Continuar y culminar el trámite de habilitación urbana referida en la cláusula segunda de este documento, con la debida diligencia, presentando y/o
subsanando los documentos y demás requerimientos de la autoridad municipal en los plazos establecidos por la ley o por la autoridad municipal, a fin de obtener
la autorización municipal de su objeto dentro del plazo previsto en la cláusula octava de este documento.</p>
<p class="parrafo">C) Solicitar la inscripción de la resolución o autorización municipal que obtenga, relativo al proyecto de habilitación urbana, por ante el registro
de predios de la oficina registral de {{ $d['ciudad'] ?? 'Huánuco' }}, dentro del plazo de 15 días de haberse obtenido dicha autorización.</p>
<p class="parrafo">D) Entregar la posesión del lote objeto de este documento a LA COMPRADORA luego de la fecha que llegue a tener existencia legal, si hasta dicho
momento EL VENDEDOR no hubiere asumido dicha posesión.</p>

<p class="parrafo"><span class="clausula">DÉCIMO TERCERA:</span> En el supuesto que una de las partes no cumpla con las obligaciones que se derivan de este contrato,
la otra parte podrá, alternativamente, exigir el cumplimiento de la obligación de su contraparte en los términos contenidos en este contrato o resolver el contrato.
En este último supuesto, la parte que desea resolverla cursará previamente comunicación por conducto notarial a la otra parte, requiriendo el cumplimiento de la
obligación incumplida en el plazo de 15 días de recibida dicha comunicación. Si persistiera dicho incumplimiento dará por resuelto este contrato.

En el caso que este contrato quede resuelto, la parte que la causó restituirá, de manera incondicional, a su contraparte las prestaciones que hubiere efectuado.</p>

<p class="parrafo"><span class="clausula">DÉCIMO CUARTA:</span> Las partes acuerdan que todos los gastos y tributos que se originen a la celebración, formalización
y ejecución del presente contrato serán asumidos por LA COMPRADORA.</p>

<p class="parrafo"><span class="clausula">DÉCIMO QUINTA:</span> Para efectos de cualquier controversia que se genere con motivo de la celebración y ejecución de este contrato,
las partes se someten a la competencia territorial de los jueces y tribunales de la ciudad de {{ $d['ciudad'] ?? 'Huánuco' }},
renunciando a los jueces de sus respectivos domicilios.</p>

<p class="parrafo"><span class="clausula">DÉCIMO SEXTA:</span> Para la validez de todas las comunicaciones y notificaciones a las partes, con motivo de la ejecución
de este contrato, ambas señalan como sus respectivos domicilios los indicados en la introducción de este documento. El cambio de domicilio de cualquiera de las partes
surtirá efecto desde la fecha de comunicación de dicho cambio a la otra parte, por vía notarial.</p>

<p class="parrafo"><span class="clausula">DÉCIMO SÉPTIMA:</span> En todo lo no previsto por las partes en el presente contrato, ambas se someten a lo establecido
por las normas del código civil y demás del sistema jurídico que resulten aplicables.</p>

@else

<p class="parrafo"><span class="clausula">QUINTO:</span> Las partes declaran que entre el valor del inmueble y el precio pactado existe la más justa y perfecta
equivalencia, por lo que de haber alguna diferencia de más o de menos, que en el presente no advierten, se hacen mutua gracia y recíproca donación, renunciando
a cualquier acción que tenga por objeto invalidar los efectos del presente contrato.</p>

<p class="parrafo"><span class="clausula">SEXTO:</span> LA VENDEDORA declara que sobre el lote objeto de esta minuta, no pesa ningún gravamen, carga,
medida judicial ni extrajudicial alguna, ni en general, ningún acto o contrato limitativo de sus derechos de dominio y libre disposición, obligándose en todo caso
al saneamiento en los términos más amplios previstos en la ley.</p>

<p class="parrafo"><span class="clausula">SÉPTIMO:</span> Las partes acuerdan que todos los gastos y tributos que se originen a la celebración, formalización
y ejecución del presente contrato serán asumidos por LA COMPRADORA.</p>

<p class="parrafo"><span class="clausula">OCTAVO:</span> Para efectos de cualquier controversia que se genere con motivo de la celebración y ejecución de este contrato,
las partes se someten a la competencia territorial de los jueces y tribunales de la ciudad de {{ $d['ciudad'] ?? 'Huánuco' }},
renunciando a los jueces de sus respectivos domicilios.</p>

<p class="parrafo"><span class="clausula">NOVENO:</span> Para la validez de todas las comunicaciones y notificaciones a las partes, con motivo de la ejecución
de este contrato, ambas señalan como sus respectivos domicilios los indicados en la introducción de este documento. El cambio de domicilio de cualquiera de las partes
surtirá efecto desde la fecha de comunicación de dicho cambio a la otra parte, por vía notarial.</p>

<p class="parrafo"><span class="clausula">DÉCIMO:</span> En todo lo no previsto por las partes en el presente contrato, ambas se someten a lo establecido
por las normas del código civil y demás del sistema jurídico que resulten aplicables.</p>

@endif

<p class="parrafo">Sírvase Ud. Señor Notario, agregar lo que fuera de ley.</p>

<p class="parrafo">{{ $d['ciudad'] ?? 'Huánuco' }}, {{ $d['fecha_minuta'] }}.</p>

<div class="firma-container">
    <div class="firma">
        <div class="linea-firma"></div>
        <strong>{{ strtoupper($d['vendedor_tipo'] === 'empresa' ? $d['representante_nombre'] : $d['vendedor_nombre']) }}</strong><br>
        @if($d['vendedor_tipo'] === 'empresa')
        por {{ strtoupper($d['vendedor_razon_social']) }}<br>
        @endif
        VENDEDOR(A)
    </div>
    <div class="firma">
        <div class="linea-firma"></div>
        <strong>{{ strtoupper($d['comprador_nombre']) }}</strong><br>
        COMPRADOR(A)
    </div>
</div>

<div class="anotacion">
    <p class="parrafo"><strong>Anotación.</strong></p>
    <p class="parrafo">La presente se encuentra afecta al pago del impuesto de alcabala. Acredite su pago y elévese a escritura pública con las formalidades de Ley.</p>
    <p class="parrafo">{{ $d['ciudad'] ?? 'Huánuco' }}, {{ $d['fecha_minuta'] }}.</p>
</div>

</div>
</body>
</html>
