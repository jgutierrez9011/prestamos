# Backend FastAPI (Credimore)

## Requerimientos
- Python 3.11+
- PostgreSQL accesible con los parámetros usados actualmente por PHP (`pages/cn.php`).
- Entorno virtual recomendado.

Instala dependencias:

```bash
cd backend
python -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Copia el archivo de variables de entorno y ajusta credenciales y secretos:

```bash
cp .env.example .env
```

Variables principales:
- `APP_DB_HOST`, `APP_DB_PORT`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASSWORD`: conexión PostgreSQL (por defecto `localhost:5400`, base `credimore`).
- `APP_JWT_SECRET`, `APP_JWT_ALGORITHM`, `APP_JWT_EXPIRES_MINUTES`: configuración de tokens.

## Ejecución

Levanta la API con recarga en desarrollo:

```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

OpenAPI queda en `http://localhost:8000/docs` y `http://localhost:8000/redoc`.

## Endpoints iniciales
- `POST /auth/login`: recibe JSON `{ "usuario": "", "clave": "" }` (aliases `username`/`password`). Valida usuarios activos, acepta hashes MD5 legados migrándolos a bcrypt, y devuelve JWT con los mismos datos de sesión usados en PHP (`idusuario`, `nombreusuario`, `correousuario`, `perfilusuario`, `carterausuario`).
- `GET /usuarios/me`: usuario autenticado.
- `GET /carteras`: lista carteras (si el perfil no es admin, solo la cartera del usuario).
- `GET /clientes`: clientes filtrados por la cartera del usuario.
- `GET /prestamos`: préstamos filtrados por cartera.

## Modelo de datos
Los modelos SQLAlchemy reflejan las tablas clave usadas por el login y los módulos actuales:
- `tblcatusuario`, `tblcatperfilusr`, `tblcatcartera`, `clientes`, `solicitudprestamo`, `prestamo` (ver `app/models.py`).

## Integración gradual con el frontend PHP/JS
1. **Login**: Actualizar la pantalla de inicio de sesión para enviar las credenciales a `POST /auth/login`. Guardar el `access_token` en almacenamiento seguro (header `Authorization: Bearer ...` en las llamadas posteriores).
2. **Sesión**: Reemplazar el uso de `fnloginusuario.php` y cookies de sesión por la validación de JWT. Los claims del token replican las variables de sesión de `globales_usuario`.
3. **Módulos**: Migrar cada vista (usuarios, carteras, clientes, préstamos) para consumir los endpoints REST equivalentes, respetando los filtros por cartera/perfil. Mantener los componentes de UI mientras se sustituyen las consultas PHP por fetch/AJAX hacia la API.
4. **Permisos**: Usar el claim `perfilusuario` para habilitar/deshabilitar accesos; los perfiles que contienen "admin" pueden consultar cualquier cartera.
5. **Despliegue**: Configurar el backend detrás del mismo dominio o habilitar CORS específico según la URL del frontend.

