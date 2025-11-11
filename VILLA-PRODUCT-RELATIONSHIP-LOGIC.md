# Lógica de Relación Villa-Producto (Equipment)

## Descripción General

Este documento explica cómo el sistema detecta qué villa está en el carrito de WooCommerce y cómo verifica si un producto de equipamiento (rent-equipment) está relacionado con esa villa.

---

## Proceso Completo: 3 Pasos Principales

### Paso 1: Obtener el ID de la Villa desde el Carrito

**Función:** `aura_multi_cart_get_villa_id_from_cart()`
**Ubicación:** `includes/helpers.php` (líneas 57-87)

```php
function aura_multi_cart_get_villa_id_from_cart() {
    // 1. Verificar que WooCommerce y el carrito existan
    if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
        return null;
    }

    // 2. Recorrer todos los items del carrito
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        // 3. Buscar el item que tenga un _calendar_id (esto indica que es una villa)
        if ( isset( $cart_item['_calendar_id'] ) ) {
            $calendar_id = intval( $cart_item['_calendar_id'] );

            // 4. Buscar en la base de datos el post (villa) que tiene ese calendar_id
            global $wpdb;
            $villa_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT post_id FROM {$wpdb->postmeta}
                     WHERE meta_key = '_wpbs_calendar_id'
                     AND meta_value = %d
                     LIMIT 1",
                    $calendar_id
                )
            );

            // 5. Retornar el ID de la villa encontrada
            if ( $villa_id ) {
                return intval( $villa_id );
            }
        }
    }

    return null;
}
```

#### ¿Cómo funciona?

1. **Recorre el carrito de WooCommerce** buscando items con metadata `_calendar_id`
2. **`_calendar_id`** es un identificador único del calendario de WP Booking System asociado a cada villa
3. **Consulta la base de datos** para encontrar qué post (villa) tiene ese calendario asignado
4. **Retorna el `post_id`** de la villa (ejemplo: 12345)

#### Ejemplo:
- Usuario agrega "Villa Paradise" al carrito
- La villa tiene `_calendar_id = 42` en su metadata
- El item del carrito lleva `_calendar_id = 42`
- La función busca en `postmeta` y encuentra que el `post_id = 12345` tiene `_wpbs_calendar_id = 42`
- **Retorna: `12345`** (ID de la villa)

---

### Paso 2: Obtener Productos Relacionados con la Villa

**Función:** `aura_multi_cart_get_related_equipment()`
**Ubicación:** `includes/helpers.php` (líneas 95-125)

```php
function aura_multi_cart_get_related_equipment( $villa_id ) {
    if ( ! $villa_id ) {
        return array();
    }

    global $wpdb;

    // 1. Consultar la tabla de relaciones de JetEngine
    $related_product_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT parent_object_id
             FROM {$wpdb->prefix}jet_rel_default
             WHERE child_object_id = %d",
            $villa_id
        )
    );

    if ( empty( $related_product_ids ) ) {
        return array();
    }

    // 2. Filtrar solo productos que tengan la categoría 'rent-equipment'
    $equipment_ids = array();
    foreach ( $related_product_ids as $product_id ) {
        if ( has_term( 'rent-equipment', 'product_cat', $product_id ) ) {
            $equipment_ids[] = intval( $product_id );
        }
    }

    return $equipment_ids;
}
```

#### ¿Cómo funciona?

1. **Consulta la tabla JetEngine** (`wp_jet_rel_default`) donde se almacenan las relaciones
2. **Estructura de la tabla:**
   - `parent_object_id` = ID del producto de equipamiento
   - `child_object_id` = ID de la villa
3. **Busca todos los productos** donde `child_object_id = villa_id`
4. **Filtra solo productos** que pertenezcan a la categoría `rent-equipment`
5. **Retorna array de IDs** de productos relacionados

#### Ejemplo de tabla `wp_jet_rel_default`:

| rel_id | parent_object_id | child_object_id |
|--------|------------------|-----------------|
| 1      | 201 (Kayak)      | 12345 (Villa)   |
| 2      | 202 (Bike)       | 12345 (Villa)   |
| 3      | 203 (Surfboard)  | 12345 (Villa)   |
| 4      | 204 (Paddle)     | 67890 (Otra villa) |

Si llamamos `aura_multi_cart_get_related_equipment(12345)`:
- **Encuentra:** productos 201, 202, 203
- **Verifica categoría:** todos tienen `rent-equipment`
- **Retorna:** `[201, 202, 203]`

---

### Paso 3: Obtener Productos Finales (Con Filtros Opcionales)

**Función:** `aura_multi_cart_get_equipment_products()`
**Ubicación:** `includes/helpers.php` (líneas 133-170)

```php
function aura_multi_cart_get_equipment_products( $settings = array() ) {
    $product_ids = array();

    // 1. Obtener el ID de la villa del carrito (Paso 1)
    $villa_id = aura_multi_cart_get_villa_id_from_cart();

    if ( ! $villa_id ) {
        return array();
    }

    // 2. Obtener equipamiento relacionado con esa villa (Paso 2)
    $related_equipment = aura_multi_cart_get_related_equipment( $villa_id );

    if ( empty( $related_equipment ) ) {
        return array();
    }

    // 3. Filtrar por IDs específicos (OPCIONAL)
    if ( ! empty( $settings['product_ids'] ) ) {
        $specific_ids = array_map( 'intval', array_map( 'trim', explode( ',', $settings['product_ids'] ) ) );
        $product_ids = array_intersect( $related_equipment, $specific_ids );
    } else {
        $product_ids = $related_equipment;
    }

    // 4. Filtrar por categoría adicional (OPCIONAL)
    if ( ! empty( $settings['category'] ) && ! empty( $product_ids ) ) {
        $filtered_ids = array();
        foreach ( $product_ids as $product_id ) {
            if ( has_term( $settings['category'], 'product_cat', $product_id ) ) {
                $filtered_ids[] = $product_id;
            }
        }
        $product_ids = $filtered_ids;
    }

    return $product_ids;
}
```

#### ¿Cómo funciona?

Esta es la función principal que combina todo:

1. **Ejecuta Paso 1:** Obtiene el ID de la villa del carrito
2. **Ejecuta Paso 2:** Obtiene todos los productos relacionados con esa villa
3. **Aplica filtros opcionales del widget Elementor:**
   - **`product_ids`:** Si el usuario pone "201,202" en el widget, solo muestra esos 2
   - **`category`:** Si el usuario pone "water-sports", solo muestra productos con esa categoría adicional
4. **Retorna array final** de IDs de productos a mostrar

---

## Flujo Completo - Ejemplo Real

### Escenario:
1. Usuario visita página de "Villa Paradise" (ID: 12345)
2. Agrega villa al carrito (7 días de renta)
3. En la página del carrito hay un widget de equipamiento

### Proceso:

```
1. Widget Elementor llama a:
   aura_multi_cart_get_equipment_products()

2. Función ejecuta:
   villa_id = aura_multi_cart_get_villa_id_from_cart()

   2a. Busca en carrito item con _calendar_id = 42
   2b. Consulta BD: ¿Qué villa tiene _wpbs_calendar_id = 42?
   2c. Respuesta: post_id = 12345
   2d. villa_id = 12345

3. Función ejecuta:
   related_equipment = aura_multi_cart_get_related_equipment(12345)

   3a. Consulta: SELECT parent_object_id FROM wp_jet_rel_default
                 WHERE child_object_id = 12345
   3b. Resultado: [201, 202, 203, 204, 205]
   3c. Filtra por categoría 'rent-equipment'
   3d. related_equipment = [201, 202, 203]

4. Aplica filtros opcionales:
   - Si widget tiene "product_ids: 201,203" → [201, 203]
   - Si widget tiene "category: water-sports" → Solo los que también estén en esa categoría

5. RESULTADO FINAL:
   product_ids = [201, 203]

6. Widget muestra:
   - Kayak (ID: 201)
   - Surfboard (ID: 203)
```

---

## Tablas de Base de Datos Involucradas

### 1. `wp_postmeta` (Asociación Villa → Calendario)
```sql
| post_id | meta_key           | meta_value |
|---------|--------------------|------------|
| 12345   | _wpbs_calendar_id  | 42         |
```

### 2. `wp_jet_rel_default` (Relaciones JetEngine)
```sql
| parent_object_id | child_object_id |
|------------------|-----------------|
| 201 (Producto)   | 12345 (Villa)   |
| 202 (Producto)   | 12345 (Villa)   |
```

### 3. `wp_term_relationships` (Categorías de Productos)
```sql
| object_id | term_taxonomy_id     |
|-----------|----------------------|
| 201       | 5 (rent-equipment)   |
| 202       | 5 (rent-equipment)   |
```

---

## Condiciones para que un Producto se Muestre

✅ **El producto SE MUESTRA si:**
1. Hay una villa en el carrito
2. El producto está relacionado con esa villa en JetEngine (`wp_jet_rel_default`)
3. El producto tiene la categoría `rent-equipment`
4. (Opcional) El producto cumple con los filtros del widget (IDs específicos o categoría adicional)

❌ **El producto NO SE MUESTRA si:**
1. No hay villa en el carrito
2. El producto NO está relacionado con la villa actual
3. El producto NO tiene categoría `rent-equipment`
4. El producto no cumple con filtros opcionales del widget

---

## Uso en el Widget Elementor

El widget llama a estas funciones así:

```php
// En widget-equipment-rental.php línea 158
$villa_id = aura_multi_cart_get_villa_id_from_cart();

if ( ! $villa_id ) {
    echo 'Please add a villa to your cart to see available equipment.';
    return;
}

// Línea 168
$product_ids = aura_multi_cart_get_equipment_products( $settings );

if ( empty( $product_ids ) ) {
    echo 'No equipment available for this villa.';
    return;
}

// Línea 188-231: Loop para mostrar cada producto
foreach ( $product_ids as $product_id ) {
    // Renderiza cada item con botones +/-
}
```

---

## Resumen Simple

**Pregunta:** ¿Cómo sabe el plugin qué productos mostrar?

**Respuesta en 3 pasos:**

1. **Detecta la villa en el carrito** → Busca `_calendar_id` en items del carrito → Encuentra `villa_id`
2. **Busca productos relacionados** → Consulta tabla JetEngine con ese `villa_id` → Obtiene lista de productos
3. **Filtra por categoría** → Solo muestra productos con categoría `rent-equipment`

**Resultado:** Solo se muestran productos de equipamiento que estén relacionados con la villa actual del carrito.
