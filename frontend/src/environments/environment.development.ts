/**
 * Development configuration.
 *
 * Points at the Laravel server from `php artisan serve`. With the `ng serve`
 * proxy a relative path would do, but the absolute URL also works when the
 * frontend is served from somewhere else — Storybook, for instance.
 */
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api/v1',
} as const;
