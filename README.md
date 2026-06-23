# Flower Admin Laravel API

Laravel project for:

- Admin panel
- Mobile REST API
- SQLite database
- Demo seed data
- Razorpay payment endpoints with mock fallback

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=8000
```

Open admin panel:

```text
http://127.0.0.1:8000/admin/login
```

Admin login:

```text
admin@example.com
admin123
```

Customer API login:

```text
customer@example.com
customer123
```

## API base URL

```text
http://127.0.0.1:8000/api
```

## Important API endpoints

```text
POST /api/register
POST /api/login
GET  /api/pooja-packets
GET  /api/flowers
POST /api/subscriptions
GET  /api/my-subscriptions
POST /api/custom-orders
GET  /api/my-orders
POST /api/event-bookings
GET  /api/my-quotations
POST /api/quotations/{id}/accept
POST /api/payments/create-order
POST /api/payments/verify
```

## Admin pages

```text
/admin/dashboard
/admin/pooja-packets
/admin/flowers
/admin/custom-orders
/admin/subscriptions
/admin/daily-deliveries
/admin/event-bookings
/admin/quotations
/admin/staff
/admin/payments
/admin/customers
/admin/reports
/admin/settings
```

## Razorpay

For development, the payment endpoints return mock order/payment data when Razorpay keys are not configured.
For live payment, set these in `.env`:

```text
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
```
