import { ApplicationConfig, provideBrowserGlobalErrorListeners, provideZoneChangeDetection } from '@angular/core';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideRouter, withComponentInputBinding } from '@angular/router';
import { APP_ROUTES } from './core/app.routes';
import { apiBaseInterceptor } from './core/interceptors/api-base.interceptor';
import { apiErrorInterceptor } from './core/interceptors/api-error.interceptor';
import { authTokenInterceptor } from './core/interceptors/auth-token.interceptor';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(APP_ROUTES, withComponentInputBinding()),
    // El orden importa: primero se resuelve la URL contra la base del API, y
    // solo entonces se decide si la petición merece llevar el token. Los
    // 401/403/5xx se resuelven aquí, así que las vistas solo tratan sus propios
    // errores de negocio.
    provideHttpClient(withInterceptors([apiBaseInterceptor, authTokenInterceptor, apiErrorInterceptor])),
    provideAnimationsAsync(),
  ],
};
