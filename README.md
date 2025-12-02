# Flash-Sale API

A Laravel 12 API for a flash-sale checkout system with limited stock, high concurrency handling, temporary holds, and an idempotent payment webhook. No UI.

## Features

- **Product Endpoint**: Returns product info and accurate available stock.
- **Create Hold**: Temporarily reserve stock (~2 minutes) to prevent overselling.
- **Create Order**: Converts valid holds into pre-payment orders.
- **Payment Webhook**: Idempotent and out-of-order safe webhook to mark orders as paid or failed.
- Handles high concurrency, race conditions, and deadlocks.
- Uses caching to improve read performance without stale stock.
- Structured logging around contention, retries, and webhook deduplication.

---

## Assumptions & Invariants

- Stock cannot go negative; overselling is prevented even under high concurrency.
- Holds expire automatically after ~2 minutes and release stock.
- Each hold can be used only once to create an order.
- Payment webhook may arrive multiple times or before order creation — final order state must remain correct.
- Idempotency is enforced via `idempotency_key`.
- Cache is used to store webhook responses for 24 hours to handle duplicates.
- Logs provide information about payments, retries, and errors.


clone project

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate --seed

php artisan serve

php artisan test
