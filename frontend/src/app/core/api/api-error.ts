import { HttpErrorResponse } from '@angular/common/http';

/**
 * The error envelope every non-2xx response carries (FR-ERR-1).
 */
export interface ApiError {
  code: string;
  message: string;
  details?: Record<string, unknown>;
  trace_id: string;
}

export function apiError(error: unknown): ApiError | null {
  if (!(error instanceof HttpErrorResponse)) {
    return null;
  }

  const body = error.error as { error?: ApiError } | null;

  return body?.error ?? null;
}

/** The machine-readable code, for deciding what to say about a refusal. */
export function apiErrorCode(error: unknown): string | null {
  return apiError(error)?.code ?? null;
}

/**
 * Per-field validation messages from a 422 (FR-VAL-2), flattened to one message
 * per field — which is what a form control can show.
 */
export function fieldErrors(error: unknown): Record<string, string> {
  const details = apiError(error)?.details as { errors?: Record<string, string[]> } | undefined;
  const errors = details?.errors ?? {};

  return Object.fromEntries(
    Object.entries(errors).map(([field, messages]) => [field, messages[0] ?? '']),
  );
}
