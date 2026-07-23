/**
 * Production configuration.
 *
 * `apiUrl` is the single door to the backend: services ask for relative paths
 * ('books', 'loans/12/return') and the interceptor resolves them against this
 * base. Keeping the API version in one place is what makes moving to /api/v2
 * cheap the day it exists (ADR-8).
 */
export const environment = {
  production: true,
  apiUrl: '/api/v1',
} as const;
