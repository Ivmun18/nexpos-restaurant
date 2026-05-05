<?php

return [
    'ambiente'    => env('SUNAT_AMBIENTE', 'beta'),
    'sol_usuario' => env('SUNAT_SOL_USUARIO', '20123456789MODDATOS'),
    'sol_clave'   => env('SUNAT_SOL_CLAVE', 'moddatos'),
    'ruc'         => env('SUNAT_RUC', '20123456789'),
    'url_factura' => env('SUNAT_URL_FACTURA', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService?wsdl'),
    'url_guia'    => env('SUNAT_URL_GUIA', 'https://e-beta.sunat.gob.pe/ol-ti-itemision-guia-remision-beta/billService?wsdl'),
    'url_percepcion' => env('SUNAT_URL_PERCEPCION', 'https://e-beta.sunat.gob.pe/ol-ti-itemision-otroscpe-beta/billService?wsdl'),
];