import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';

interface SampleLoan {
  title: string;
  isbn: string;
  stamp: string;
  status: 'active' | 'overdue' | 'returned';
}

interface Feature {
  icon: string;
  title: string;
  body: string;
}

interface RoleCard {
  tone: 'admin' | 'staff' | 'member';
  tag: string;
  verb: string;
  can: string[];
}

/**
 * The public landing page.
 *
 * It is the only screen a visitor sees before signing in, so it does the job a
 * product page does: say what the system is for, and let the three roles
 * recognise themselves in it. The content is static, but it lives in the
 * component rather than the template so the copy stays in one readable place.
 */
@Component({
  selector: 'lib-landing-page',
  standalone: true,
  imports: [RouterLink],
  templateUrl: './landing-page.html',
  styleUrl: './landing-page.scss',
})
export class LandingPage {
  readonly year = new Date().getFullYear();

  readonly sampleLoans: SampleLoan[] = [
    { title: 'Cien años de soledad', isbn: '9780307474728', stamp: 'DEVOLVER · 28 JUL', status: 'active' },
    { title: 'El infinito en un junco', isbn: '9788417860790', stamp: 'VENCIDO · 12 DÍAS', status: 'overdue' },
    { title: 'Matilda', isbn: '9788420482880', stamp: 'DEVUELTO · 1 JUL', status: 'returned' },
  ];

  readonly features: Feature[] = [
    {
      icon: '📚',
      title: 'Catálogo vivo',
      body: 'Altas con validación de ISBN, autores y categorías. La disponibilidad se actualiza sola con cada préstamo y devolución.',
    },
    {
      icon: '🔄',
      title: 'Préstamos sin fricción',
      body: 'Busca al socio, escanea el libro y listo: fecha de devolución automática a 14 días. La devolución es un solo clic.',
    },
    {
      icon: '⏰',
      title: 'Vencidos a la vista',
      body: 'Los préstamos fuera de plazo se marcan con el sello rojo y bloquean nuevos préstamos hasta la devolución. Sin excepciones olvidadas.',
    },
    {
      icon: '🔍',
      title: 'Búsqueda instantánea',
      body: 'Por título, autor o ISBN, desde cualquier pantalla. Compatible con lector de códigos de barras.',
    },
    {
      icon: '📊',
      title: 'Panel con lo importante',
      body: 'Libros en circulación, vencidos, altas del mes y autores más leídos. Los números del día de un vistazo.',
    },
    {
      icon: '🔐',
      title: 'Roles que se componen',
      body: 'Administración, bibliotecarios y socios de serie, y la posibilidad de crear roles nuevos con los permisos que hagan falta.',
    },
  ];

  readonly roles: RoleCard[] = [
    {
      tone: 'admin',
      tag: 'Administración',
      verb: 'Dirige',
      can: ['Compone roles y asigna permisos', 'Gestiona las cuentas del equipo', 'Ve los indicadores y los informes'],
    },
    {
      tone: 'staff',
      tag: 'Bibliotecarios',
      verb: 'Opera',
      can: ['Presta y recibe libros', 'Da de alta títulos y socios', 'Persigue los vencidos'],
    },
    {
      tone: 'member',
      tag: 'Socios',
      verb: 'Lee',
      can: ['Explora el catálogo', 'Consulta sus préstamos', 'Sabe siempre cuándo devolver'],
    },
  ];
}
