import { ApplicationConfig, provideAppInitializer, provideBrowserGlobalErrorListeners, provideZoneChangeDetection } from '@angular/core';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { provideRouter, withComponentInputBinding, withInMemoryScrolling } from '@angular/router';
import { APP_ROUTES } from './core/app.routes';
import { apiBaseInterceptor } from './core/interceptors/api-base.interceptor';
import { apiErrorInterceptor } from './core/interceptors/api-error.interceptor';
import { authTokenInterceptor } from './core/interceptors/auth-token.interceptor';
import { restoreSession } from './features/auth/data/session.initializer';

export const appConfig: ApplicationConfig = {
  providers: [
    provideBrowserGlobalErrorListeners(),
    provideZoneChangeDetection({ eventCoalescing: true }),
    // anchorScrolling lets the "Change password" menu item deep-link to the
    // form's section on the profile page (`/profile#password`).
    provideRouter(APP_ROUTES, withComponentInputBinding(),
      withInMemoryScrolling({ anchorScrolling: 'enabled' })),
    // Order matters: the URL is resolved against the API base first, and only
    // then is it decided whether the request deserves the token. 401/403/5xx are
    // handled there, so views only deal with their own business errors.
    provideHttpClient(withInterceptors([apiBaseInterceptor, authTokenInterceptor, apiErrorInterceptor])),
    // The guards ask the session what the account may do, so the session is
    // rebuilt from the stored token before the first route is matched.
    provideAppInitializer(restoreSession),
    provideAnimationsAsync(),
  ],
};
