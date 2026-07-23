import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';
import { roleGuard } from './guards/role.guard';

export const APP_ROUTES: Routes = [
  {
    path: '',
    loadComponent: () => import('./layouts/auth-layout/auth-layout').then(m => m.AuthLayout),
    children: [
      { path: 'login', title: 'Iniciar sesión — Librarium', loadComponent: () => import('../features/auth/login/login-page').then(m => m.LoginPage) },
      { path: 'register', title: 'Crear cuenta — Librarium', loadComponent: () => import('../features/auth/register/register-page').then(m => m.RegisterPage) },
    ],
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layouts/app-layout/app-layout').then(m => m.AppLayout),
    children: [
      { path: 'dashboard', title: 'Panel — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/dashboard/dashboard-page').then(m => m.DashboardPage) },

      { path: 'books', title: 'Libros — Librarium', loadComponent: () => import('../features/books/list/books-list-page').then(m => m.BooksListPage) },
      { path: 'books/new', title: 'Nuevo libro — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/books/form/book-form-page').then(m => m.BookFormPage) },
      { path: 'books/:id', title: 'Detalle de libro — Librarium', loadComponent: () => import('../features/books/detail/book-detail-page').then(m => m.BookDetailPage) },
      { path: 'books/:id/edit', title: 'Editar libro — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/books/form/book-form-page').then(m => m.BookFormPage) },

      { path: 'authors', title: 'Autores — Librarium', loadComponent: () => import('../features/authors/authors-list-page').then(m => m.AuthorsListPage) },
      { path: 'categories', title: 'Categorías — Librarium', loadComponent: () => import('../features/categories/categories-list-page').then(m => m.CategoriesListPage) },

      { path: 'loans', title: 'Préstamos — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/loans/list/loans-list-page').then(m => m.LoansListPage) },
      { path: 'loans/checkout', title: 'Prestar libro — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/loans/checkout/checkout-page').then(m => m.CheckoutPage) },
      { path: 'my-loans', title: 'Mis préstamos — Librarium', loadComponent: () => import('../features/loans/my-loans/my-loans-page').then(m => m.MyLoansPage) },

      { path: 'users', title: 'Usuarios — Librarium', canActivate: [roleGuard(['admin'])], loadComponent: () => import('../features/users/list/users-list-page').then(m => m.UsersListPage) },
      { path: 'users/new', title: 'Nuevo usuario — Librarium', canActivate: [roleGuard(['admin', 'librarian'])], loadComponent: () => import('../features/users/form/user-form-page').then(m => m.UserFormPage) },
      { path: 'users/:id/edit', title: 'Editar usuario — Librarium', canActivate: [roleGuard(['admin'])], loadComponent: () => import('../features/users/form/user-form-page').then(m => m.UserFormPage) },

      { path: 'search', title: 'Buscar — Librarium', loadComponent: () => import('../features/search/search-page').then(m => m.SearchPage) },

      { path: '', pathMatch: 'full', redirectTo: 'books' },
    ],
  },
  { path: 'forbidden', title: 'Sin acceso — Librarium', loadComponent: () => import('../features/errors/forbidden-page').then(m => m.ForbiddenPage) },
  { path: '**', title: 'No encontrado — Librarium', loadComponent: () => import('../features/errors/not-found-page').then(m => m.NotFoundPage) },
];
