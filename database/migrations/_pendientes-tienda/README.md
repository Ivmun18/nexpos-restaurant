# Migraciones "tienda" — pendientes de rediseño

Estas 5 migraciones implementan un concepto de "tienda" (un negocio físico que
agrupa varias `empresas`/RUC bajo una misma caja/inventario/ventas — pensado
para un caso como Llantas Pucallpa: una tienda, dos razones sociales). El
`up()` de cada una hace backfill **de toda la tabla, sin filtrar por
empresa_id**, asignando todo a la primera fila de `tiendas` (T01).

Eso es incorrecto para esta base de datos: `nexpos_restaurant` es la base
compartida multi-tenant de la plataforma (restaurantes, notarías,
farmacias, minimarkets, odontología, etc.), no la de un solo negocio. El
27/08 se corrió parcialmente `2026_08_05_100007_add_tienda_id_to_caja_table`
en producción: quedó backfillear `tienda_id=1` en la tabla `caja` para
empresas completamente ajenas entre sí (El Sabor, Notaría Demo, Cevichería
El Punto de Encuentro...), y el `unique(tienda_id, codigo)` final falló
porque 5 empresas distintas usan el mismo código de caja por defecto
(`CAJA01`) — dato normal en un sistema multi-tenant, no un error a corregir.

El 2026-08-31 se revirtió ese cambio parcial en producción (columna, FK e
índice de vuelta al estado original `unique(empresa_id, codigo)`) y se
movieron estos 5 archivos aquí para que `php artisan migrate` deje de
tropezar con ellos en cada deploy.

Antes de volver a mover estos archivos a `database/migrations/`, el `up()`
de cada uno necesita re-scopear el backfill (por ejemplo, solo a los
`empresa_id` que realmente pertenecen a la tienda en cuestión, vía la tabla
`empresa_tienda` que sí se creó y sí tiene sentido tal cual está), no a la
plataforma entera.
