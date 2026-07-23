import { Component, input } from '@angular/core';

/** Skeletons that mirror the real geometry, so nothing shifts when data lands.
 *  Variants: 'rows' (table), 'stat' (KPI), 'card' (detail). */
@Component({
  selector: 'lib-skeleton',
  standalone: true,
  template: `
    @switch (variant()) {
      @case ('stat') {
        <div class="sk-card"><div class="sk sk-line w40"></div><div class="sk sk-big w60"></div></div>
      }
      @case ('card') {
        <div class="sk-card">
          <div class="sk sk-line w60"></div><div class="sk sk-line w90"></div>
          <div class="sk sk-line w80"></div><div class="sk sk-line w50"></div>
        </div>
      }
      @default {
        @for (r of rowsArray(); track $index) {
          <div class="sk-row">
            <div class="sk sk-line w30"></div><div class="sk sk-line w20"></div>
            <div class="sk sk-line w15"></div><div class="sk sk-line w10"></div>
          </div>
        }
      }
    }
  `,
  styles: [`
    :host { display: block; }
    .sk { background: var(--lib-surface-2); border-radius: 4px; animation: pulse 1.4s ease-in-out infinite; }
    .sk-line { height: 13px; } .sk-big { height: 30px; margin-top: 8px; }
    .sk-row { display: flex; gap: var(--sp-4); padding: var(--sp-3) var(--sp-4); border-bottom: 1px solid var(--lib-line); }
    .sk-card { display: grid; gap: 10px; padding: var(--sp-5); background: var(--lib-surface); border: 1px solid var(--lib-line); border-radius: var(--lib-radius); }
    .w10{width:10%} .w15{width:15%} .w20{width:20%} .w30{width:30%} .w40{width:40%} .w50{width:50%} .w60{width:60%} .w80{width:80%} .w90{width:90%}
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .45; } }
  `],
})
export class Skeleton {
  readonly variant = input<'rows' | 'stat' | 'card'>('rows');
  readonly rows = input(5);
  rowsArray(): number[] { return Array.from({ length: this.rows() }); }
}
