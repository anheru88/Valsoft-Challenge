import { Injectable, computed, signal } from '@angular/core';

/**
 * How many HTTP requests are in flight right now.
 *
 * A counter, not a boolean: two overlapping requests must both finish before
 * the global bar goes away, and the first one to return should not switch it
 * off under the second. The `loadingInterceptor` moves the count; the layout
 * reads `active` (PRD 8.4).
 */
@Injectable({ providedIn: 'root' })
export class LoadingStore {
  private readonly count = signal(0);

  readonly active = computed(() => this.count() > 0);

  begin(): void {
    this.count.update(n => n + 1);
  }

  end(): void {
    this.count.update(n => Math.max(0, n - 1));
  }
}
