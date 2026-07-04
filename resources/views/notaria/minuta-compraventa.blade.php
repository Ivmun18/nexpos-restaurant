<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #000; margin: 0; padding: 0; }
    .container { padding: 40px 50px; }
    .titulo { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
    .parrafo { text-align: justify; margin-bottom: 14px; }
    .clausula { font-weight: bold; }
    .firma-container { margin-top: 60px; display: table; width: 100%; }
    .firma { display: table-cell; width: 50%; text-align: center; padding-top: 10px; }
    .linea-firma { border-top: 1px solid #000; width: 200px; margin: 0 auto 6px; }
    .anotacion { margin-top: 40px; border-top: 1px solid #000; padding-top: 14px; }
    .tab { display: inline-block; width: 30px; }
</style>
</head>
<body>
<div class="container">

<p class="parrafo"><strong>SEÑOR NOTARIO:</strong></p>

<p class="parrafo">Sírvase Ud. extender en su Registro de Escrituras Públicas una de <strong>COMPRA VENTA DE PREDIO</strong> que celebran de una parte
@if($d['vendedor_tipo'] === 'empresa')
<strong>{{ strtoupper($d['vendedor_razon_social']) }}</strong>, con RUC N° {{ $d['vendedor_ruc'] }}, con domicilio en {{ $d['vendedor_domicilio'] }},
inscrito en la partida registral N° {{ $d['vendedor_partida_registral'] ?? '___________' }} del Registro de Personas Jurídicas de {{ $d['ciudad'] ?? 'Huánuco' }},
a quien en adelante se le denominará <strong>LA VENDEDORA</strong> debidamente representada por su {{ $d['representante_cargo'] ?? 'Gerente General' }},
<strong>{{ strtoupper($d['representante_nombre']) }}</strong>, de nacionalidad peruana, identificado con DNI N° {{ $d['representante_dni'] }},
de estado civil {{ $d['representante_estado_civil'] ?? 'soltero' }}, de profesión {{ $d['representante_profesion'] ?? '___________' }},
con domicilio en {{ $d['representante_domicilio'] }};
@else
<strong>{{ strtoupper($d['vendedor_nombre']) }}</strong>, identificado con DNI N° {{ $d['vendedor_dni'] }},
de estado civil {{ $d['vendedor_estado_civil'] ?? 'soltero' }}, con domicilio en {{ $d['vendedor_domicilio'] }},
a quien en adelante se le denominará <strong>EL VENDEDOR</strong>;
@endif
y de otra parte <strong>{{ strtoupper($d['comprador_nombre']) }}</strong>, identificado(a) con Documento Nacional de Identidad N° {{ $d['comprador_dni'] }},
{{ $d['comprador_nacionalidad'] ?? 'peruano(a)' }}, de estado civil {{ $d['comprador_estado_civil'] }},
de profesión {{ $d['comprador_profesion'] }}, con domicilio en {{ $d['comprador_domicilio'] }},
a quien en adelante se le denominará <strong>LA COMPRADORA</strong>; en los términos y condiciones siguientes:</p>

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
@if(!empty($d['es_bien_futuro']))En tales condiciones, dentro del proyecto señalado, se encuentra el@else El predio materia de la presente compraventa es el@endif
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

<p class="parrafo"><span class="clausula">{{ !empty($d['es_bien_futuro']) ? 'NOVENO' : 'QUINTO' }}:</span>
LA VENDEDORA declara que sobre el lote objeto de esta minuta, no pesa ningún gravamen, carga, medida judicial ni extrajudicial alguna,
obligándose en todo caso al saneamiento en los términos más amplios previstos en la ley.</p>

<p class="parrafo"><span class="clausula">{{ !empty($d['es_bien_futuro']) ? 'DÉCIMO' : 'SEXTO' }}:</span>
Para efectos de cualquier controversia que se genere con motivo de la celebración y ejecución de este contrato, las partes se someten a la competencia territorial
de los jueces y tribunales de la ciudad de {{ $d['ciudad'] ?? 'Huánuco' }}, renunciando a los jueces de sus respectivos domicilios.</p>

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
