# Flash-Sale API

A Laravel 12 API for a flash-sale system with limited stock, temporary holds, and idempotent payment webhooks.  

No UI; API only.

---

## Features

- Products: GET  /api/getProductById returns product info and accurate stock.
- Holds: POST /api/holdProduct temporarily reserves stock (~2 minutes). Stock released automatically when hold expires.
- Orders: POST /api/createOrder creates pre-payment orders from valid holds.
- Payment Webhook: POST /api/payments/handleWebhook updates order status (paid/failed) with idempotency support. Handles duplicates and out-of-order requests.
- Prevents overselling under high concurrency.
- Logs events with structured info.
- Background task releases expired holds (php artisan holds:release-expired).


## Assumptions & Invariants

- Stock never goes negative, overselling is prevented with lockForUpdate and caching.
- Holds expire automatically after 2 minutes and release stock.
- Each hold can only create one order.
- Orders are marked PAID or FAILED exactly once via idempotent webhook.
- Webhook may arrive multiple times or before order creation.
- Cache stores webhook responses to ensure idempotency for 24 hours.
- Logs capture payments, retries, lock events, and errors.



- clone project
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate --seed
- php artisan serve
- php artisan test














