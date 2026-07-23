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

interface DemoAccount {
  role: string;
  email: string;
  sees: string;
}

/**
 * The public landing page.
 *
 * It is the only screen a visitor sees before signing in, so it does two jobs:
 * say what the system is for, and say plainly what it is — an assessment build
 * with a defined scope, not a product with a company behind it. Overselling a
 * demo is how a reviewer ends up hunting for a feature that was never in the
 * brief.
 *
 * Copy lives here rather than in the template so it stays readable in one place.
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
    { title: 'One Hundred Years of Solitude', isbn: '9780307474728', stamp: 'DUE · 28 JUL', status: 'active' },
    { title: 'Papyrus', isbn: '9788417860790', stamp: 'OVERDUE · 12 DAYS', status: 'overdue' },
    { title: 'Matilda', isbn: '9788420482880', stamp: 'RETURNED · 1 JUL', status: 'returned' },
  ];

  readonly features: Feature[] = [
    {
      icon: '📚',
      title: 'A living catalogue',
      body: 'Titles are added with a checksum-validated ISBN, authors and categories. Availability keeps itself current with every loan and return.',
    },
    {
      icon: '🔄',
      title: 'Lending without friction',
      body: 'Find the member, pick the book, done: the due date defaults to fourteen days out. Checking a book back in is a single click.',
    },
    {
      icon: '⏰',
      title: 'Overdue items in plain sight',
      body: 'Anything past its date carries the red stamp and blocks new loans until it comes back. No forgotten exceptions.',
    },
    {
      icon: '🔍',
      title: 'Search that answers',
      body: 'By title, author or ISBN, from any screen. An ISBN goes straight to an exact match; everything else is ranked by relevance.',
    },
    {
      icon: '📊',
      title: 'A dashboard worth reading',
      body: 'Books in circulation, overdue items, additions this month and the most borrowed authors. The day at a glance.',
    },
    {
      icon: '🔐',
      title: 'Roles you compose',
      body: 'Administration, librarians and members out of the box, with capabilities held as data rather than written into the code.',
    },
  ];

  readonly roles: RoleCard[] = [
    {
      tone: 'admin',
      tag: 'Administration',
      verb: 'Directs',
      can: ['Manages accounts and their roles', 'Reads the indicators and the reports', 'Keeps the catalogue and circulation in order'],
    },
    {
      tone: 'staff',
      tag: 'Librarians',
      verb: 'Operates',
      can: ['Lends and receives books', 'Adds titles and registers members', 'Chases the overdue items'],
    },
    {
      tone: 'member',
      tag: 'Members',
      verb: 'Reads',
      can: ['Browses the catalogue', 'Checks their own loans', 'Always knows when a book is due'],
    },
  ];

  /** What the brief asked for and this build actually does. */
  readonly delivered: string[] = [
    'Token authentication with three roles and capability-based permissions',
    'Catalogue: books, authors and categories, with ISBN validation',
    'Circulation: check-out and check-in under the business rules, with a row lock so the last copy cannot be oversold',
    'Search across titles, ISBNs, authors and categories',
    'Dashboard indicators and the overdue and most-borrowed reports',
    'An OpenAPI document generated from the code, and a test suite covering every rule',
  ];

  /** Named on purpose: the brief placed these outside the MVP. */
  readonly outOfScope: string[] = [
    'The AI features, which the brief lists only as future enhancements',
    'Email notifications and reservations',
    'Fines, renewals and multi-branch libraries',
    'Most screens still read demo data — only signing in talks to the API so far',
  ];

  readonly demoAccounts: DemoAccount[] = [
    { role: 'Administrator', email: 'admin@librarium.test', sees: 'Everything, including user management and the reports' },
    { role: 'Librarian', email: 'librarian@librarium.test', sees: 'The desk: catalogue, circulation and the dashboard' },
    { role: 'Member', email: 'member@librarium.test', sees: 'The catalogue and their own loans' },
  ];
}
