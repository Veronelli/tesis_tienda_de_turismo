# Gestion de Datos - Tienda de Turismo

Modulo PHP preparado con arquitectura hexagonal para aislar la logica de negocio de los detalles externos.

## Estructura

- `Domain/Models`: modelos del dominio definidos por requerimiento explicito.
- `Domain/Repositories`: interfaces de repositorios que necesita el dominio.
- `Application/Input`: datos de entrada para los casos de uso.
- `Application/UseCases`: puntos de control de la logica de negocio.
- `Application/Ports/External`: contratos para servicios externos.
- `Infrastructure/Repositories`: implementaciones concretas de repositorios.
- `Infrastructure/Repositories/ExternalServices`: repositorios/adaptadores que interactuan con servicios externos.
- `Interfaces/Http/Controllers`: puntos de entrada desde HTTP u otras capas de presentacion.

## Flujo esperado

1. La capa `Interfaces` recibe datos externos.
2. La capa `Application` valida el flujo del caso de uso.
3. La capa `Domain` mantiene reglas y modelos centrales.
4. La capa `Infrastructure` persiste datos o consulta servicios externos.

## Restriccion importante

No crear entidades dentro de este modulo salvo pedido explicito del usuario. Mantener la gestion de datos con contratos, entradas, casos de uso y adaptadores sin introducir clases de entidad por defecto.
