# Stripe Checkout System

A modern, professional payment checkout system built with PHP and Stripe integration. Features a sleek dark theme UI inspired by shadcn/ui design patterns.

## Features

- Modern dark theme UI with shadcn-inspired design
- Product selection with 3 pre-configured products
- Secure payment processing via Stripe Payment Intents API
- Real-time card validation with Stripe Elements
- Order management with MySQL database
- Responsive design (mobile + desktop)
- Loading states and error handling

## Technology Stack

- **Frontend**: HTML5, CSS3 (custom properties), Vanilla JavaScript
- **Backend**: PHP 8.x with PDO
- **Payment**: Stripe API (Payment Intents, Elements)
- **Database**: MySQL

## Project Structure

```
CheckoutStripe/
├── api/
│   ├── config.php           # Database & Stripe configuration
│   ├── products.php        # Product listing API
│   ├── create-payment-intent.php  # Stripe Payment Intent creation
│   ├── create-order.php   # Order record creation
│   └── webhook.php        # Stripe webhook handler
├── css/
│   └── styles.css         # Complete styling
├── js/
│   └── app.js           # Frontend JavaScript
├── index.php            # Main checkout page
├── SPEC.md             # Technical specification
└── README.md          # This file
```

## Prerequisites

1. **PHP 8.0+** with PDO MySQL extension
2. **MySQL 5.7+** or MariaDB
3. **Stripe Account** (free at stripe.com)

## Installation Guide

### Step 1: Database Setup

Create a MySQL database:

```sql
CREATE DATABASE stripe_checkout CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The application will auto-create tables and seed sample products on first run.

### Step 2: Configure Stripe Keys

1. Sign up at [stripe.com](https://stripe.com)
2. Go to Dashboard > Developers > API Keys
3. Copy your test keys

Edit `api/config.php`:

```php
define('STRIPE_SECRET_KEY', 'sk_test_your_key_here');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_key_here');
```

Edit `js/app.js`:

```javascript
const STRIPE_PK = 'pk_test_your_key_here';  // Line 4
```

### Step 3: Configure Database Credentials

Edit `api/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'stripe_checkout');
define('DB_USER', 'your_username');   // usually 'root'
define('DB_PASS', 'your_password'); // your password
```

### Step 4: Run the Application

Start a local PHP server:

```bash
# Windows
php -S localhost:8000

# Linux/macOS  
php -S localhost:8000 -t .
```

Open http://localhost:8000 in your browser.

## Testing Payments

### Test Card Numbers

Use these Stripe test cards:

| Card Number | Description |
|------------|--------------|
| 4242424242424242 | Success (Visa) |
| 4000000000000002 | Declined |
| 4000002500003155 | Requires authentication |

Any future expiry date and any 3-digit CVC will work.

## How It Works

### Payment Flow

1. **Page Load**: Products loaded from database via `api/products.php`
2. **Product Selection**: User selects a product card
3. **Checkout Form**: User enters name, email
4. **Payment Intent**: JS calls `api/create-payment-intent.php` → creates PaymentIntent on Stripe
5. **Card Input**: Stripe Elements displays secure card input
6. **Payment**: `stripe.confirmPayment()` processes payment
7. **Order**: On success, order saved to MySQL via `api/create-order.php`
8. **Confirmation**: Success screen displayed

### API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/products.php` | GET | List active products |
| `/api/create-payment-intent.php` | POST | Create Stripe Payment Intent |
| `/api/create-order.php` | POST | Save order to database |
| `/api/webhook.php` | POST | Handle Stripe webhooks |

### Database Schema

**products** table:
- id, name, description, price, image_url, active, created_at

**orders** table:
- id, stripe_payment_id, customer_name, customer_email, product_id, quantity, amount, currency, status, created_at

## Customization

### Adding Products

Edit config.php or insert directly:

```sql
INSERT INTO products (name, description, price, image_url) VALUES 
('Product Name', 'Description', 49.99, 'https://...');
```

### Changing Colors

Edit `css/styles.css` - all colors defined as CSS variables:

```css
:root {
    --accent-primary: #22c55e;  /* Change this */
    --bg-primary: #09090b;     /* Dark background */
}
```

### Adding Fields

1. Add input to `index.php`
2. Add validation in `js/app.js` validateForm()
3. Save in `api/create-order.php`

## Security Notes

- Never commit Stripe keys to version control
- Use environment variables in production
- Enable Stripe webhooks for reliable payment confirmation
- Use HTTPS in production (required by Stripe)
- Sanitize all user inputs on server side

## Troubleshooting

### "Database connection failed"

Check MySQL is running and credentials are correct in config.php.

### "Payment failed"

- Verify Stripe test keys are correctly set
- Check Stripe Dashboard for error logs
- Ensure cURL extension is enabled: `php -m | grep curl`

### "Card element not loading"

- Check Stripe.js script loads correctly
- Verify publishable key in app.js
- Check browser console for errors

## License

MIT License - Feel free to use for personal or commercial projects.

## Credits

- [Stripe](https://stripe.com) - Payment infrastructure
- [shadcn/ui](https://ui.shadcn.com/) - Design inspiration