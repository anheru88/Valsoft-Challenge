# Prompt - Generate Complete PRD & RFC Documentation for a Library Management System

## Role

You are a **Principal Software Architect**, **Senior Product Manager**, and **Staff Backend Engineer** with extensive experience designing enterprise applications.

Your goal is **NOT** to implement the software.

Your goal is to create professional documentation that could be used by a real engineering team before development begins.

The documentation should look like something produced by companies such as Stripe, GitHub, Linear, Shopify, Microsoft, Atlassian or Airbnb.

The project must **NOT** look like a coding challenge. It should look like the MVP of a real SaaS product.

---

# Context

This project is being developed as part of a Senior Backend Engineer technical assessment.

The original assignment is:

> Mini Library Management System

Minimum Features

- Book Management
    - Create books
    - Edit books
    - Delete books
- Check In / Check Out
- Search books

Bonus

- Authentication
- Different roles
- AI Features
- Deploy
- Additional features

The objective is to produce documentation for a solution that demonstrates Senior-level architecture, engineering practices and software design.

---

# Important

Do NOT generate code.

Only generate documentation.

Every document must be extremely detailed.

Think as if a software company will implement the project from these documents.

---

# Deliverables

Generate the following documents.

---

# Document 1

# Product Requirements Document (PRD) - Backend

The PRD should include at minimum:

## Executive Summary

## Product Vision

## Business Goals

## Technical Goals

## Scope

### In Scope

### Out of Scope

## Personas

- Administrator
- Librarian
- Member

## User Stories

Include all user stories.

Example:

"As an Administrator I want..."

"As a Librarian I want..."

"As a Member I want..."

---

## Functional Requirements

Include detailed requirements for:

Authentication

Users

Roles

Permissions

Books

Authors

Categories

Loans

Search

Dashboard

Reports

Pagination

Sorting

Filtering

Validation

Error Handling

---

## Non Functional Requirements

Performance

Security

Scalability

Maintainability

Availability

Logging

API Versioning

Caching

Localization readiness

Accessibility considerations

---

## Business Rules

Very detailed.

Examples:

A book cannot be borrowed if unavailable.

A member cannot borrow more than X books simultaneously.

Only Admin can delete users.

Only Librarian/Admin can create books.

Etc.

---

## Acceptance Criteria

For every feature.

---

## Future Enhancements

Include a section describing optional future features.

Mark them clearly as:

Future Enhancement (Optional)

Include AI ideas only as future enhancements.

Examples:

- AI Book Summary
- AI Book Categorization
- AI Recommendations
- Natural Language Search
- Duplicate Detection

These features should NOT be part of the MVP.

---

# Document 2

# Product Requirements Document (PRD) - Frontend

This document should describe the Angular application.

Include:

Navigation

Layouts

Responsive behavior

Authentication flow

User Experience

Dashboard

Books Module

Authors Module

Categories Module

Loans Module

Users Module

Search

Pagination

Sorting

Loading States

Skeletons

Empty States

Error States

Toast Notifications

Confirmation dialogs

Accessibility

Dark Mode readiness

Storybook organization

Component hierarchy

Reusable components

Design System

Routing

Guards

State management

Signals

RxJS

Angular Material usage

Storybook usage

---

# Document 3

# Architecture RFC

This document must be highly technical.

Explain every architecture decision.

It should justify WHY each technology was chosen.

Include diagrams (using Mermaid when appropriate).

---

## Architecture Style

The backend is NOT a traditional Laravel MVC application.

The backend follows a **Modular Monolith** architecture with **Lightweight Domain Driven Design (DDD Lite)**.

Laravel is only the application framework.

Business logic must remain isolated from Laravel as much as possible.

---

## Backend Architecture

The project uses Laravel 12.

PHP 8.4

MariaDB

Sanctum Authentication

Redis (prepared for future caching although not required by the MVP)

Swagger / OpenAPI

Repository Pattern

Service Layer

DTOs

Policies

API Resources

Form Requests

SOLID

Clean Architecture principles

---

### Folder Structure

The application must isolate business logic inside:

```text
app/

Library/
```

Everything related to business logic lives inside Library.

Example:

```text
app/
└── Library
    ├── Application
    ├── Domains
    ├── Infrastructure
    └── Shared
```

Each Domain should be independent.

Example:

Books

Authors

Categories

Users

Loans

Dashboard

Auth

Each module should contain its own:

Models

Services

Repositories

Actions

DTOs

Policies

Events

Requests

Resources

Contracts

Enums

Exceptions

Value Objects

Explain the responsibility of each folder.

Explain why this architecture is superior to putting everything under Laravel's default folders.

---

## Explain

Dependency Flow

Request Lifecycle

Repository Pattern

DTO usage

API Resources

Policies

Validation Flow

Authorization Flow

Authentication Flow

Error Handling

Exception Handling

Logging Strategy

Performance Strategy

Database Strategy

Caching Strategy

Indexing Strategy

Eager Loading Strategy

Pagination Strategy

Search Strategy

Scalability Strategy

Testing Strategy

---

## Frontend Architecture

Angular 20

Standalone Components

Signals

RxJS

Angular Material

SCSS

Storybook

Feature Based Architecture

Folder structure example:

```text
src/

core/

shared/

features/

storybook/
```

Explain every folder.

Explain why Feature-Based Architecture was selected.

Explain component communication.

Explain Signals.

Explain Services.

Explain Guards.

Explain Interceptors.

Explain Route Organization.

Explain Storybook organization.

---

# Document 4

# REST API Specification

Generate a complete API specification.

Include:

Authentication

Books

Authors

Categories

Loans

Dashboard

Users

Search

Each endpoint must include:

Method

Route

Headers

Authentication

Request

Response

Status Codes

Validation Errors

Examples

---

# Technology Stack

Backend

Laravel 12

PHP 8.4

MariaDB

Sanctum

REST API

Redis (future)

Swagger

OpenAPI

Laravel Pint

---

Frontend

Angular 20

Standalone Components

Signals

RxJS

Angular Material

SCSS

Storybook

---

Authentication

Laravel Sanctum

Three Roles

Administrator

Librarian

Member

---

# Modules

The MVP should include:

Authentication

Dashboard

Users

Books

Authors

Categories

Loans

Search

Permissions

---

# Dashboard

The dashboard should include meaningful KPIs.

Examples:

Total Books

Available Books

Borrowed Books

Books Added

Books Returned

Recent Activity

Popular Authors

Recently Added Books

Books by Category

Monthly Statistics

---

# Database

Use MariaDB.

Design a normalized relational model.

Include relationships.

Recommend indexes.

Discuss performance considerations.

---

# Search

Support searching by:

Book Title

ISBN

Author

Category

Description

Publisher

Include recommendations for scalable searching while keeping the MVP implementation simple.

---

# Security

Discuss:

Authentication

Authorization

Policies

Permissions

Input Validation

Rate Limiting

API Security

OWASP recommendations

---

# Performance

Discuss:

Pagination

Indexes

N+1 prevention

Eager Loading

Caching opportunities

Query optimization

---

# AI Features

These are NOT part of the MVP.

Document them only under:

Future Enhancements

Include ideas such as:

Book Recommendations

Automatic Summaries

Category Classification

Duplicate Detection

Natural Language Search

Chat Assistant

---

# Expected Quality

The generated documentation should resemble documentation produced by a mature engineering organization.

Avoid generic explanations.

Every section should include rationale.

Every architecture decision should explain:

- Why it was selected.
- Alternatives considered.
- Trade-offs.
- Future scalability.

Write professionally using clear headings, tables, diagrams (Mermaid where appropriate), and engineering best practices.
