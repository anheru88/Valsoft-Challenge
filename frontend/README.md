# Librarium — Frontend (Angular 20)

Todas las vistas HTML, componentes standalone y stories de Storybook del sistema de gestión de biblioteca, listos para copiar dentro de `src/` de un proyecto Angular 20.

## Estructura

```
src/
├── styles/
│   ├── tokens.scss          # Design tokens (colores, tipografía, espaciado, dark mode)
│   └── base.scss            # Reset + utilidades (.page, .card, .lib-table, .form-grid…)
├── core/
│   ├── models.ts            # Tipos del API /api/v1
│   ├── auth.store.ts        # Sesión con signals (token, user, rol)
│   ├── app.routes.ts        # Rutas lazy con guards por rol
│   ├── guards/              # authGuard, roleGuard(['admin','librarian'])
│   ├── interceptors/        # Bearer token + manejo global 401/403/5xx
│   └── layouts/             # AppLayout (sidenav + topbar) y AuthLayout
├── shared/ui/               # Design system (12 componentes standalone)
│   └── stories/             # Historias de Storybook (CSF3)
└── features/
    ├── auth/                # login, register
    ├── dashboard/           # KPIs, actividad, barras por categoría
    ├── books/               # list, detail, form (con validador de ISBN)
    ├── authors/             # lista + diálogo de alta/edición
    ├── categories/          # lista + diálogo de alta/edición
    ├── loans/               # list (staff), checkout (mostrador), my-loans (socio)
    ├── users/               # list (admin), form
    ├── search/              # resultados de la búsqueda global
    └── errors/              # 403, 404
```

## Puesta en marcha

1. **Dependencias**

```bash
ng add @angular/material        # tema base; los colores reales vienen de los tokens
```

2. **Fuentes** (en `index.html`)

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Public+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
```

3. **Estilos globales** (`styles.scss`)

```scss
@use './styles/tokens';
@use './styles/base';
```

4. **Arranque** (`app.config.ts`)

```ts
import { ApplicationConfig } from '@angular/core';
import { provideRouter, withComponentInputBinding } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { APP_ROUTES } from './core/app.routes';
import { authTokenInterceptor } from './core/interceptors/auth-token.interceptor';
import { apiErrorInterceptor } from './core/interceptors/api-error.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideRouter(APP_ROUTES, withComponentInputBinding()),
    provideHttpClient(withInterceptors([authTokenInterceptor, apiErrorInterceptor])),
    provideAnimationsAsync(),
  ],
};
```

5. **Storybook**

```bash
npx storybook@latest init --type angular
npm run storybook
```

Las historias están en `shared/ui/stories/` y se detectan con el glob por defecto (`src/**/*.stories.ts`). Para que los tokens se apliquen en Storybook, importa `styles/tokens.scss` y `styles/base.scss` en `.storybook/preview.ts`.

## Conexión con el API

Las vistas funcionan con **datos de demostración** para poder revisarlas sin backend. Cada punto de integración está marcado con `// TODO API:` e indica método, ruta y los códigos de error de negocio a tratar (p. ej. `LOAN_LIMIT_REACHED`, `BOOK_HAS_ACTIVE_LOANS`, `LAST_ADMIN_PROTECTED`). El contrato completo está en `04-api-specification.md`.

Convenciones aplicadas:

- Errores de negocio (409) → `<lib-inline-alert>` junto al control, nunca en toast.
- Errores de validación (422) → señal `fieldErrors` mapeada a `mat-error` por campo.
- 401/403/5xx → los resuelve `api-error.interceptor` de forma global.

## Identidad visual

- **Paleta**: verde lámpara de biblioteca `#2F6B4F` (primario), latón `#A97E2F` (acento), rojo sello `#B3402A` (vencidos y acciones destructivas), papel `#F5F6F2`.
- **Tipografía**: Fraunces (títulos), Public Sans (interfaz), IBM Plex Mono (ISBN, fechas y códigos — el dato "de ficha" siempre en mono).
- **Elemento firma**: `<lib-due-stamp>`, el sello de fecha de devolución con rotación de tampón. Verde en activo, rojo en vencido, gris punteado en devuelto.
- **Dark mode**: alterna la clase `dark-theme` en `<body>` (ya cableado en el topbar); todos los componentes lo heredan vía tokens.
