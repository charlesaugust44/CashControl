> This project is intentionally over-engineered for its size.  
> It uses **Service + Repository** layers even where simple Eloquent calls would suffice.  
> This pattern demonstrates familiarity with enterprise Laravel practices commonly expected in job requirements.  
> In a real-world app of this scope, many layers would be collapsed.

# Cash Control

Personal finance tracking application built with Laravel and Vue.js.

## About

Cash Control helps you manage recurring income, expenses, and transfers across multiple assets. Define templates (headers) with flexible recurrence rules, generate monthly events, and track balances over time.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3, SQLite
- **Frontend**: Vue 3, Pinia, Vue Router, Tailwind CSS
- **Build**: Vite

## Planned Tech Demonstrations

The following features will be added to showcase additional Laravel capabilities:

| Feature | Purpose |
|---------|---------|
| **Cache** | Redis integration for dashboard metrics and expensive aggregations |
| **Queues & Jobs** | Background processing for monthly event generation and email notifications |
| **Tests** | Feature and unit tests using PHPUnit, covering consolidation logic and API endpoints |

These additions will remain within the same over-engineered structure to maintain consistency as a portfolio piece.

## Features

- Multi-asset support (checking, savings, cash, etc.)
- Recurring transaction templates with rules:
  - `fixed` – same amount each occurrence
  - `max_last_five_months` – capped at highest of last 5 months
  - `mean_last_five_months` – calculates the mean of last 5 months
- Monthly event generation and consolidation
- Entry history per asset
- REST API for all resources

## Installation

```bash
cd cash-control
composer setup

# to start
composer run dev
```
