import { Routes } from '@angular/router';
import { authGuard } from './guards/auth.guard';
import { guestGuard } from './guards/guest.guard';
import { permissionGuard } from './guards/permission.guard';

export const APP_ROUTES: Routes = [
  // The public front door. `pathMatch: 'full'` keeps it from swallowing /login.
  {
    path: '',
    pathMatch: 'full',
    title: 'Librarium — The digital desk for your library',
    canActivate: [guestGuard],
    loadComponent: () => import('../features/landing/landing-page').then(m => m.LandingPage),
  },
  {
    path: '',
    canActivate: [guestGuard],
    loadComponent: () => import('./layouts/auth-layout/auth-layout').then(m => m.AuthLayout),
    children: [
      { path: 'login', title: 'Sign in — Librarium', loadComponent: () => import('../features/auth/login/login-page').then(m => m.LoginPage) },
      { path: 'register', title: 'Create account — Librarium', loadComponent: () => import('../features/auth/register/register-page').then(m => m.RegisterPage) },
    ],
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layouts/app-layout/app-layout').then(m => m.AppLayout),
    children: [
      { path: 'dashboard', title: 'Dashboard — Librarium', canActivate: [permissionGuard(['dashboard.view'])], loadComponent: () => import('../features/dashboard/dashboard-page').then(m => m.DashboardPage) },

      { path: 'books', title: 'Books — Librarium', loadComponent: () => import('../features/books/list/books-list-page').then(m => m.BooksListPage) },
      { path: 'books/new', title: 'New book — Librarium', canActivate: [permissionGuard(['catalog.manage'])], loadComponent: () => import('../features/books/form/book-form-page').then(m => m.BookFormPage) },
      { path: 'books/:id', title: 'Book detail — Librarium', loadComponent: () => import('../features/books/detail/book-detail-page').then(m => m.BookDetailPage) },
      { path: 'books/:id/edit', title: 'Edit book — Librarium', canActivate: [permissionGuard(['catalog.manage'])], loadComponent: () => import('../features/books/form/book-form-page').then(m => m.BookFormPage) },

      { path: 'authors', title: 'Authors — Librarium', loadComponent: () => import('../features/authors/authors-list-page').then(m => m.AuthorsListPage) },
      { path: 'categories', title: 'Categories — Librarium', loadComponent: () => import('../features/categories/categories-list-page').then(m => m.CategoriesListPage) },

      { path: 'loans', title: 'Loans — Librarium', canActivate: [permissionGuard(['loans.view-any'])], loadComponent: () => import('../features/loans/list/loans-list-page').then(m => m.LoansListPage) },
      { path: 'loans/checkout', title: 'Lend a book — Librarium', canActivate: [permissionGuard(['loans.manage'])], loadComponent: () => import('../features/loans/checkout/checkout-page').then(m => m.CheckoutPage) },
      { path: 'my-loans', title: 'My loans — Librarium', loadComponent: () => import('../features/loans/my-loans/my-loans-page').then(m => m.MyLoansPage) },

      { path: 'users', title: 'Users — Librarium', canActivate: [permissionGuard(['users.view-any'])], loadComponent: () => import('../features/users/list/users-list-page').then(m => m.UsersListPage) },
      { path: 'users/new', title: 'New user — Librarium', canActivate: [permissionGuard(['users.create-any', 'users.create-member'])], loadComponent: () => import('../features/users/form/user-form-page').then(m => m.UserFormPage) },
      { path: 'users/:id/edit', title: 'Edit user — Librarium', canActivate: [permissionGuard(['users.manage'])], loadComponent: () => import('../features/users/form/user-form-page').then(m => m.UserFormPage) },

      { path: 'search', title: 'Search — Librarium', loadComponent: () => import('../features/search/search-page').then(m => m.SearchPage) },

      { path: '', pathMatch: 'full', redirectTo: 'books' },
    ],
  },
  { path: 'forbidden', title: 'No access — Librarium', loadComponent: () => import('../features/errors/forbidden-page').then(m => m.ForbiddenPage) },
  { path: '**', title: 'Not found — Librarium', loadComponent: () => import('../features/errors/not-found-page').then(m => m.NotFoundPage) },
];
