<p align="center">
  <a href="#" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Cashela Logo">
  </a>
</p>

<p align="center">
<a href="#"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="#"><img src="https://img.shields.io/badge/PHP-8.2+-8892BF.svg" alt="PHP Version"></a>
<a href="#"><img src="https://img.shields.io/badge/Laravel-11-FF2D20.svg" alt="Laravel Version"></a>
<a href="#"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License"></a>
</p>

## Acerca de Cashela

**Cashela** es un ecosistema de API robusto diseñado para la gestión financiera inteligente y el control de inventarios de alto rendimiento. Construido sobre la elegancia de Laravel, Cashela transforma procesos complejos de transacciones multimoneda en flujos de trabajo simplificados y seguros.

Cashela eleva el estándar de desarrollo para aplicaciones empresariales mediante la integración de:

- **Motor de Transacciones Dinámico:** Conversión automática de divisas con soporte para tasas variables.
- **Auditoría Integral (Audit Trail):** Trazabilidad total de cada cambio en el sistema mediante logs de actividad detallados.
- **Seguridad Multicapa:** Implementación de RBAC (Control de Acceso Basado en Roles) con Spatie y autenticación vía Sanctum.
- **Arquitectura Containerizada:** Despliegue garantizado en cualquier entorno mediante Docker Compose.
- **Documentación OpenApi:** Integración nativa con Swagger para un desarrollo Frontend ágil y documentado.

## Dominando el Ecosistema

Cashela no es solo una base de datos, es una herramienta de ingeniería pensada para la escalabilidad:

- **Documentación de API:** Puedes explorar todos los endpoints disponibles en `/api/documentation`.
- **Gestión de Precios:** Sistema avanzado para manejar costos de manufactura, impuestos y márgenes de ganancia.
- **Infraestructura Cloud-Ready:** Optimizado para despliegues en entornos como Webempresa o servicios de contenedores.

## Desarrollo con IA (Agentic Development)

La estructura predecible de Cashela y sus estrictas convenciones de código la hacen ideal para trabajar con agentes de codificación como **Cursor**, **Claude** o **GitHub Copilot**. Para maximizar la productividad en este proyecto, asegúrate de mantener actualizados los modelos de Eloquent y los Resource de respuesta.

## Guía de Instalación Rápida

Si ya tienes Docker instalado, el despliegue es inmediato:

```bash
# Levantar el entorno
docker compose up -d

# Instalar dependencias
docker exec -it cashela_app composer install

# Configurar base de datos
docker exec -it cashela_app php artisan migrate --seed
