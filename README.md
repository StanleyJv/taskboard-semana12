# 🧩 TaskBoard — Semana 11

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue?logo=php)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-TaskBoard-blue?logo=mysql)](https://www.mysql.com/)
[![UPED](https://img.shields.io/badge/UPED-Integración_de_Sistemas-0B2E59)](#)

Proyecto desarrollado para la asignatura **Integración de Sistemas** de la **Universidad Pedagógica de El Salvador**, correspondiente a la **Semana 11**.

## 👨‍💻 Estudiante

**Josthyn Stanley Cruz Vásquez**  
Ingeniería en Sistemas y Computación  
Universidad Pedagógica de El Salvador

## 🎯 Objetivo de la semana

Durante la Semana 11 se completó el CRUD de transacciones incorporando las operaciones de **editar** y **eliminar**, además de una nueva funcionalidad para cambiar rápidamente el estado de una transacción.

También se trabajó con integridad referencial, borrado en cascada y validación de transiciones válidas entre estados.

## ✅ Jueves — Actualizar y eliminar transacciones

Se agregaron las operaciones necesarias para editar y eliminar transacciones existentes.

### Rutas de edición

```php
Route::get('/transacciones/{transaccion}/editar',
    [TransaccionController::class, 'edit'])
    ->name('transacciones.edit');

Route::put('/transacciones/{transaccion}',
    [TransaccionController::class, 'update'])
    ->name('transacciones.update');
```

También se creó la vista:

```text
resources/views/transacciones/edit.blade.php
```

El formulario aparece prellenado utilizando:

```blade
old('cliente_nombre', $transaccion->cliente_nombre)
```

y:

```blade
old('monto', $transaccion->monto)
```

## 🔁 Reutilización del Form Request

El método `update()` reutiliza el Form Request desarrollado durante la Semana 10:

```php
public function update(
    GuardarTransaccionRequest $request,
    Transaccion $transaccion
) {
    $transaccion->update($request->validated());

    return redirect()
        ->route('comercios.show', $transaccion->comercio_id)
        ->with('mensaje', 'Transacción actualizada correctamente.');
}
```

De esta forma, las mismas reglas utilizadas al crear una transacción también se reutilizan al editarla.

## 🗑️ Eliminación de transacciones

Se agregó la ruta:

```php
Route::delete('/transacciones/{transaccion}',
    [TransaccionController::class, 'destroy'])
    ->name('transacciones.destroy');
```

Y el método:

```php
public function destroy(Transaccion $transaccion)
{
    $comercioId = $transaccion->comercio_id;
    $cliente = $transaccion->cliente_nombre;

    $transaccion->delete();

    return redirect()
        ->route('comercios.show', $comercioId)
        ->with('mensaje', "Transacción de {$cliente} eliminada.");
}
```

Antes de eliminar se utiliza una confirmación desde la vista:

```blade
onsubmit="return confirm('¿Eliminar esta transacción? Esta acción no se puede deshacer.')"
```

Se comprobó que al presionar **Cancelar** no se elimina nada y que al confirmar la transacción desaparece correctamente.

## 🔗 Integridad referencial

Se creó un `EventoTransaccion` asociado a una transacción y luego se intentó eliminar dicha transacción.

La base de datos respondió con:

```text
SQLSTATE[23000]: Integrity constraint violation: 1451
```

Esto permitió comprobar que MySQL estaba protegiendo la relación entre:

```text
Transaccion → EventoTransaccion
```

## 🔥 Borrado en cascada

Para permitir que los eventos relacionados se eliminen automáticamente junto con la transacción se modificó la llave foránea:

```php
$table->foreignId('transaccion_id')
    ->constrained('transacciones')
    ->onDelete('cascade');
```

Después se ejecutó:

```bash
php artisan migrate:fresh --seed
```

y se comprobó que la transacción podía eliminarse sin error junto con sus eventos asociados.

## 🧪 Reto del jueves

Se completó la **Misión B — Mensaje de éxito con detalle**.

Ahora, después de eliminar una transacción, el sistema muestra el nombre del cliente:

```text
Transacción de Juan Pérez eliminada.
```

## ✅ Viernes — Mover estado de una transacción

Durante la sesión del viernes se agregó una función específica para cambiar el estado de una transacción sin abrir el formulario completo de edición.

## 🔀 Ruta PATCH

Se agregó:

```php
Route::patch('/transacciones/{transaccion}/mover/{estado}',
    [TransaccionController::class, 'moverEstado'])
    ->name('transacciones.mover');
```

La ruta utiliza `PATCH` porque solamente modifica una parte del recurso: el estado.

## 🚦 Estados disponibles

Los estados utilizados en TaskBoard son:

```text
Iniciada
Completada
Fallida
```

Las transiciones válidas definidas son:

```text
Iniciada → Completada
Iniciada → Fallida
Fallida → Iniciada
```

Una transacción `Completada` no puede regresar directamente a otro estado.

## 🔘 Botones rápidos

Las transacciones con estado `Iniciada` muestran los botones:

```text
Marcar Completada
Marcar Fallida
```

Los botones utilizan formularios con:

```blade
@method('PATCH')
```

y permiten cambiar el estado sin abrir la pantalla de edición.

## ⚠️ Prueba de transición inválida

Inicialmente se comprobó que el sistema permitía cambiar:

```text
Completada → Fallida
```

La prueba devolvió:

```text
true
```

demostrando que todavía faltaba una validación de negocio.

## 🛡️ Validación de transiciones

Se mejoró `moverEstado()` utilizando un mapa de transiciones:

```php
$transiciones = [
    'Iniciada' => ['Completada', 'Fallida'],
    'Completada' => [],
    'Fallida' => ['Iniciada'],
];
```

Antes de actualizar se verifica:

```php
if (!in_array($estado, $transiciones[$transaccion->estado] ?? [])) {
    abort(
        400,
        "No se puede mover de {$transaccion->estado} a {$estado}."
    );
}
```

También se bloquean estados inexistentes:

```php
if (!in_array($estado, ['Iniciada', 'Completada', 'Fallida'])) {
    abort(400, 'Estado no válido.');
}
```

## 🧪 Pruebas realizadas

Durante la Semana 11 se comprobaron los siguientes escenarios:

- Formulario de edición prellenado.
- Actualización correcta de nombre y monto.
- Validación al dejar el monto vacío.
- Confirmación antes de eliminar.
- Cancelación del proceso de eliminación.
- Eliminación correcta de transacciones.
- Error de integridad referencial sin `cascade`.
- Eliminación correcta utilizando `onDelete('cascade')`.
- Error 404 al intentar editar una transacción inexistente.
- Ruta `PATCH` para cambiar estado.
- Cambio válido `Iniciada → Completada`.
- Cambio inválido `Completada → Fallida`.
- Bloqueo correcto de transiciones inválidas.
- Bloqueo de estados inexistentes como `Cancelada`.

## 💳 Flujo de actualización

```text
Comercio
   ↓
Editar transacción
   ↓
GuardarTransaccionRequest
   ↓
TransaccionController@update
   ↓
Eloquent / MySQL
   ↓
Redirección al comercio
   ↓
Mensaje de confirmación
```

## 🚦 Flujo de cambio de estado

```text
Panel del comercio
   ↓
Botón Marcar Completada / Fallida
   ↓
PATCH transacciones.mover
   ↓
TransaccionController@moverEstado
   ↓
Validación de transición
   ↓
Eloquent
   ↓
Actualización del estado
   ↓
Mensaje de confirmación
```

## 🛠️ Tecnologías

- Laravel 12
- PHP 8.2
- MySQL / MariaDB
- Blade
- Eloquent ORM
- Form Requests
- Route Model Binding
- Métodos HTTP `PUT`, `DELETE` y `PATCH`
- XAMPP
- Git
- GitHub

## ▶️ Ejecución del proyecto

```bash
php artisan serve
```

Luego abrir:

```text
http://127.0.0.1:8000/comercios
```

## 📂 Repositorio

```text
https://github.com/StanleyJv/taskboard-semana11
```

---

**Integración de Sistemas · Ciclo 02-2026 · UPED**
