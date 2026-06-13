# Instrucciones Para Agentes

Este modulo no debe contener entidades.

- No crear clases de entidad en `Domain/Models` ni en otra carpeta del modulo, salvo pedido explicito del usuario.
- No introducir objetos de dominio que representen entidades persistibles.
- Mantener la arquitectura hexagonal con contratos, datos de entrada, casos de uso, adaptadores e infraestructura.
- Si se necesita transportar datos, usar DTOs de entrada/salida o arrays tipados segun el estilo existente.
- Antes de agregar una clase nueva, verificar que no sea una entidad encubierta.
- No crear metodos, casos de uso, scripts ni endpoints que eliminen destinos. Los destinos no se pueden eliminar bajo ningun caso.

## Dashboard / Frontend

El diseno del dashboard debe seguir el prototipo en `resources/prototipe.pdf`.

### Estilo visual
- Fondo: beige claro `#f0ebe5`
- Tarjetas: blancas `#ffffff` con bordes redondeados (8px) y sombra suave
- Acento principal: naranja quemado `#ff7c00` (usado en botones, enlaces, bordes activos)
- Texto principal: `#1a1a2e`
- Texto secundario: `#78716c`
- Tipografia: sistema (`-apple-system, BlinkMacSystemFont, sans-serif`)
- Inputs: borde `#bccad8`, foco con anillo naranja

### Secciones del panel admin
El panel `admin.html` contiene estas secciones navegables via sidebar:

| Seccion | Contenido |
|---------|-----------|
| **Consultas** | Listado con busqueda por destino/hotel/titulo. Cada consulta muestra: cliente, paquete, hotel, estado, fecha, indicador temperatura (Tibio/Caliente/Frio). Modal crear/editar. |
| **Paquetes** | Listado con filtro por fecha. Modal crear/editar con: nombre, hotel, fechas, descripcion, imagenes (principal/secundaria), servicios incluidos (checkboxes: Desayuno, All inclusive, Pileta). |
| **Clientes** | Listado con total. Modal crear/editar con: nombre, apellido, email, telefono, DNI, ubicacion. |
| **Hoteles** | Listado. Modal crear/editar con: nombre, destino, ubicacion. |
| **Destinos** | Listado. Modal crear/editar con: destino, provincia, pais. |

### Estados de consulta
- `En Proceso` (amarillo)
- `Concretada` (verde)
- `Cancelada` (rojo)

### Indicador temperatura
- `Caliente` - rojo/naranja
- `Tibio` - amarillo
- `Frio` - azul

### Paginas publicas
- `buscar-paquetes.html` — Busqueda con filtros destino/hotel/fecha. Muestra tarjetas de paquetes con boton "Consultar".
- `consultar-paquete.html` — Detalle del paquete + formulario de consulta (nombre, apellido, ubicacion, email, telefono, mensaje). Boton "Enviar consulta".
- `consulta-enviada.html` — Pantalla de exito con mensaje de confirmacion y resumen del paquete consultado.

### API endpoints esperados (a implementar)
Para que el dashboard funcione completamente, se necesitan estos endpoints REST:

```
GET    /api/consultas
POST   /api/consultas
PUT    /api/consultas/{id}
GET    /api/paquetes
POST   /api/paquetes
PUT    /api/paquetes/{id}
GET    /api/clientes
POST   /api/clientes
PUT    /api/clientes/{id}
GET    /api/hoteles
POST   /api/hoteles
PUT    /api/hoteles/{id}
GET    /api/destinos
POST   /api/destinos
PUT    /api/destinos/{id}
```
