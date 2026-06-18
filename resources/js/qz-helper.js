export async function imprimirTicketQZ(venta, empresa) {
    if (typeof window.qz === 'undefined' || !window.qz) return
    try {
        window.qz.security.setCertificatePromise(() => Promise.resolve(''))
        window.qz.security.setSignatureAlgorithm('SHA512')
        window.qz.security.setSignaturePromise(() => Promise.resolve(''))
        if (!window.qz.websocket.isActive()) { await window.qz.websocket.connect() }
        const impresoras = await window.qz.printers.find()
        const impresora = impresoras.find(p =>
            p.toLowerCase().includes('xprinter') ||
            p.toLowerCase().includes('xp-') ||
            p.toLowerCase().includes('thermal') ||
            p.toLowerCase().includes('pos')
        ) || impresoras[0]
        if (!impresora) return
        const config = window.qz.configs.create(impresora, { encoding: 'ISO-8859-1' })
        const sep = '-'.repeat(42) + String.fromCharCode(10)
        const ESC = String.fromCharCode(27)
        const GS = String.fromCharCode(29)
        const items = (venta.detalle || []).map(d => {
            const desc = (d.descripcion || '').substring(0, 24)
            const cant = String(d.cantidad)
            const precio = 'S/' + Number(d.precio_unitario || 0).toFixed(2)
            const subtotal = 'S/' + Number(d.total || 0).toFixed(2)
            return ESC + String.fromCharCode(97,0) + desc + String.fromCharCode(10) +
                   '  ' + cant + ' x ' + precio + ' = ' + subtotal + String.fromCharCode(10)
        }).join('')
        const data = [
            ESC + String.fromCharCode(64),
            ESC + String.fromCharCode(97,1),
            ESC + String.fromCharCode(33,16),
            (empresa.razon_social || empresa.nombre || 'MINIMARKET') + String.fromCharCode(10),
            ESC + String.fromCharCode(33,0),
            'RUC: ' + (empresa.ruc || '') + String.fromCharCode(10),
            (empresa.direccion || '') + String.fromCharCode(10),
            sep,
            ESC + String.fromCharCode(97,1),
            (venta.tipo_comprobante === '03' ? 'BOLETA DE VENTA' : venta.tipo_comprobante === '01' ? 'FACTURA' : 'TICKET') + String.fromCharCode(10),
            (venta.numero_completo || '') + String.fromCharCode(10),
            'Fecha: ' + (venta.fecha_emision || '') + String.fromCharCode(10),
            ESC + String.fromCharCode(97,0),
            sep,
            items,
            sep,
            ESC + String.fromCharCode(97,2),
            ESC + String.fromCharCode(33,16),
            'TOTAL: S/' + Number(venta.total || 0).toFixed(2) + String.fromCharCode(10),
            ESC + String.fromCharCode(33,0),
            ESC + String.fromCharCode(97,1),
            'Metodo: ' + (venta.metodo_pago || '') + String.fromCharCode(10),
            sep,
            '   Gracias por su compra!' + String.fromCharCode(10),
            String.fromCharCode(10,10,10),
            GS + String.fromCharCode(86,66,0),
            ESC + String.fromCharCode(112,0,25,250),
        ]
        await window.qz.print(config, data)
        await window.qz.websocket.disconnect()
    } catch (e) { alert('ERROR QZ: ' + (e.message || JSON.stringify(e))) }
}
