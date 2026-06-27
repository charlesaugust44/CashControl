> This project is intentionally over-engineered for its size.  
> It uses **Service + Repository** layers even where simple Eloquent calls would suffice.  
> This pattern demonstrates familiarity with enterprise Laravel practices commonly expected in job requirements.  
> In a real-world app of this scope, many layers would be collapsed.

> [!WARNING]
> 
> **🚧 Work in Progress 🚧**  
> This project is actively under development. Features may be incomplete or subject to change.

# Cash Control

Personal finance tracking application built with Laravel.

## About

Cash Control helps you manage recurring income, expenses, and transfers across multiple assets. Define templates (headers) with flexible recurrence rules, generate monthly events, and track balances over time.

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3, SQLite
- **Frontend**: Blade templates, Bootstrap 5
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
- Recurring transaction templates (headers) with rules:
  - `fixed` – same amount each occurrence
  - `max_last_five_months` – capped at highest of last 5 months
  - `mean_last_five_months` – calculates the mean of last 5 months
- Monthly event generation with virtual (forecast) and persisted events
- Event consolidation workflow with balance updates
- Month closure system with rollback capability
- Asset balance tracking (actual and forecast)
- Header-asset relationships for automatic event generation
- Transfer support between assets
- Entry history per asset
- REST API for all resources

## Core Concepts

### Headers (Templates)
Headers define recurring transactions with rules for amount calculation. Each header is linked to an asset (or two assets for transfers).

### Events
Events are monthly occurrences generated from headers. They can be:
- **Virtual**: Generated on-the-fly from active headers (forecast)
- **Persisted**: Stored in database when user edits or consolidates

### Consolidation
Events must be consolidated to update asset balances. Consolidation:
- Marks event as finalized
- Updates asset balance with entry amounts
- Can only happen for current/past months
- Transfers auto-consolidate (both entries)

### Month Closure
Closing a month:
- Requires all events to be consolidated
- Updates `closed_up_to` date on all assets
- Prevents further edits to that month
- Can be reopened (unconsolidates all events, recalculates balances)

## Installation

```bash
cd cash-control
composer setup

# to start
composer run dev
```
