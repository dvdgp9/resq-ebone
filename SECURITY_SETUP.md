# Configuración de Seguridad - ResQ

## ⚠️ CREDENCIALES SMTP EXPUESTAS - ACCIÓN REQUERIDA

Se detectaron credenciales SMTP expuestas en el repositorio público de GitHub. 

## Configuración Actual (Segura)

1. **Archivo principal** (`config/app.php`): Ya no contiene credenciales
2. **Archivo local** (`config/local.php`): Contiene credenciales reales (NO se sube a Git)
3. **Archivo ejemplo** (`config/local.example.php`): Template para otros desarrolladores

## Para Nuevos Desarrolladores

1. Copiar `config/local.example.php` como `config/local.php`
2. Rellenar con las credenciales reales del servidor SMTP
3. **NUNCA** subir `config/local.php` a Git

## Estado Actual

- ✅ Credenciales movidas a archivo local
- ✅ .gitignore actualizado
- ✅ Sistema funcionando correctamente
- ❌ **Historial de Git AÚN CONTIENE credenciales**

## IMPORTANTE: Limpiar Historial de Git

Las credenciales siguen visibles en commits anteriores. Ver instrucciones abajo. 