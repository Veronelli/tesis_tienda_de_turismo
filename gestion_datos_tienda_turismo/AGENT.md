# Instrucciones Para Agentes

Este modulo no debe contener entidades.

- No crear clases de entidad en `Domain/Models` ni en otra carpeta del modulo, salvo pedido explicito del usuario.
- No introducir objetos de dominio que representen entidades persistibles.
- Mantener la arquitectura hexagonal con contratos, datos de entrada, casos de uso, adaptadores e infraestructura.
- Si se necesita transportar datos, usar DTOs de entrada/salida o arrays tipados segun el estilo existente.
- Antes de agregar una clase nueva, verificar que no sea una entidad encubierta.
