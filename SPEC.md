# Stripe Checkout System - Technical Specification

## Project Overview
- **Project Name**: Stripe Checkout System
- **Type**: Full-stack Payment Web Application
- **Core Functionality**: Secure payment processing with Stripe Elements, order management, and professional checkout experience
- **Target Users**: E-commerce customers and merchants

## Technology Stack (Strict)
- Frontend: HTML5, CSS3, Vanilla JavaScript
- Backend: PHP 8.x with JDBC-like database abstraction
- Payment: Stripe API (Elements + Payment Intents)
- Database: MySQL

## UI/UX Specification

### Visual Design (shadcn-inspired)
- **Color Palette**:
  - Background: `#09090b` (zinc-950)
  - Card Background: `#18181b` (zinc-900)
  - Border: `#27272a` (zinc-800)
  - Primary: `#fafafa` (zinc-50)
  - Secondary: `#18181b` (zinc-900)
  - Accent: `#22c55e` (green-500)
  - Error: `#ef4444` (red-500)
  - Muted: `#a1a1aa` (zinc-400)

- **Typography**:
  - Font Family: `"Geist Sans", "Inter", system-ui, sans-serif`
  - Headings: 24px-32px, font-weight 600
  - Body: 14px-16px, font-weight 400
  - Monospace: `"Geist Mono", "JetBrains Mono", monospace`

- **Spacing**: 4px base unit (4, 8, 12, 16, 24, 32, 48, 64)

- **Visual Effects**:
  - Border radius: 8px (cards), 6px (buttons), 4px (inputs)
  - Shadows: `0 1px 2px rgba(0,0,0,0.05)` (subtle)
  - Transitions: 150ms ease-out

### Layout Structure
1. **Header**: Logo, nav links, cart icon with count
2. **Hero/Product Section**: Product image, title, description, price
3. **Checkout Form**: 
   - Customer info fields
   - Stripe Elements card input
   - Order summary sidebar
4. **Footer**: Copyright, links

### Components
- Buttons: Primary (filled), Secondary (outline), Ghost
- Inputs: Text, Email, Card Element container
- Cards: Product card, Order summary card
- Alerts: Success, Error, Warning states
- Loading: Spinner with backdrop

## Functionality Specification

### Core Features
1. **Product Display**
   - Show product with image, name, description, price
   - Quantity selector
   - Add to cart functionality

2. **Checkout Flow**
   - Customer information form (name, email)
   - Stripe Payment Element integration
   - Real-time validation
   - Payment intent creation on server

3. **Payment Processing**
   - Stripe Payment Intents API
   - Secure card input via Stripe Elements
   - Client-side and server-side validation
   - Webhook handling for payment confirmation

4. **Order Management**
   - Store orders in database
   - Order status tracking
   - Order confirmation display

### Database Schema
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stripe_payment_id VARCHAR(255) UNIQUE,
    customer_name VARCHAR(255),
    customer_email VARCHAR(255),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'usd',
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    price DECIMAL(10,2),
    image_url VARCHAR(500),
    active BOOLEAN DEFAULT TRUE
);
```

### API Endpoints (PHP)
- `POST /api/create-payment-intent.php` - Create Stripe Payment Intent
- `POST /api/webhook.php` - Handle Stripe webhooks
- `GET /api/products.php` - Get product list
- `POST /api/create-order.php` - Create order record

## Acceptance Criteria
1. ✓ Page loads with modern dark theme UI
2. ✓ Product displays correctly with image, name, price
3. ✓ Checkout form collects customer info
4. ✓ Stripe Elements card input renders and accepts input
5. ✓ Payment can be processed (test mode)
6. ✓ Order confirmation shown after successful payment
7. ✓ Responsive on mobile and desktop
8. ✓ No console errors
9. ✓ Database stores order records
10. ✓ Professional README with setup instructions