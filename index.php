<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Stripe Payment</title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://js.stripe.com/v3/" async></script>
</head>
<body>
    <div class="loading-overlay" id="loading-overlay">
        <div class="spinner"></div>
    </div>

    <header class="header">
        <div class="container">
            <div class="header-inner">
                <a href="/" class="logo">
                    <div class="logo-icon">S</div>
                    StripeCheckout
                </a>
                <nav class="nav">
                    <a href="#" class="nav-link">Products</a>
                    <a href="#" class="nav-link">Documentation</a>
                    <button class="cart-button">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        <span class="cart-count" id="cart-count">1</span>
                    </button>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <div class="page-header">
                <h1 class="page-title">Choose Your Plan</h1>
                <p class="page-subtitle">Select a product and complete your purchase securely</p>
            </div>

            <div class="alert alert-error hidden" id="error-alert"></div>

            <div class="products-grid" id="products-grid">
            </div>

            <form id="checkout-form">
                <div class="checkout-section">
                    <div class="form-card">
                        <h2 class="form-title">Customer Information</h2>
                        
                        <div class="form-group">
                            <label class="form-label" for="customer-name">Full Name</label>
                            <input type="text" id="customer-name" class="form-input" 
                                   placeholder="John Doe" autocomplete="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="customer-email">Email Address</label>
                            <input type="email" id="customer-email" class="form-input" 
                                   placeholder="john@example.com" autocomplete="email" required>
                        </div>

                        <div class="divider">Payment Details</div>

                        <div class="form-group">
                            <label class="form-label">Card Information</label>
                            <div class="card-element-container" id="card-container">
                                <div id="card-element"></div>
                            </div>
                            <div class="form-error" id="card-errors"></div>
                        </div>

                        <div class="supported-cards">
                            <svg width="32" height="20" viewBox="0 0 32 20" fill="none">
                                <rect width="32" height="20" rx="2" fill="#1A1F71"/>
                                <path d="M13 14.5L14.5 5.5H16.5L15 14.5H13ZM20.5 6C20 5.5 19.25 5.25 18.25 5.25C16.75 5.25 15.5 6.25 15.5 7.5C15.5 8.5 16.25 9 16.75 9.25C17.25 9.5 17.5 9.75 17.5 10C17.5 10.75 16.75 11.25 15.75 11.25C14.75 11.25 14 10.75 13.75 10.25L12.75 10.75C13.25 11.5 14.25 12 15.75 12C17.5 12 18.75 11 18.75 9.75C18.75 9 18.25 8.5 17.5 8C17 7.75 16.75 7.5 16.75 7C16.75 6.5 17.25 6 17.75 6C18.25 6 18.5 6.25 18.75 6.5L19.5 5.75C19.25 5.5 18.75 5.25 18.25 5.25L20.5 6ZM10.5 5.5L11.75 10.5L9.75 14.5H7.5L9.5 10.5L8.25 5.5H10.5Z" fill="white"/>
                            </svg>
                            <svg width="32" height="20" viewBox="0 0 32 20" fill="none">
                                <rect width="32" height="20" rx="2" fill="#EB001B"/>
                                <circle cx="12" cy="10" r="5" fill="#F79E1B"/>
                                <path d="M16 6C17.3 7 18 8.2 18 10C18 11.8 17.3 13 16 14C14.7 13 14 11.8 14 10C14 8.2 14.7 7 16 6Z" fill="#F79E1B"/>
                            </svg>
                            <svg width="32" height="20" viewBox="0 0 32 20" fill="none">
                                <rect width="32" height="20" rx="2" fill="#006FCF"/>
                                <path d="M13 7.5L14.25 12.5H12L13 7.5ZM22.5 8.25C22 7.75 21.25 7.5 20.25 7.5C18.75 7.5 17.5 8.5 17.5 9.75C17.5 10.75 18.25 11.25 18.75 11.5C19.25 11.75 19.5 12 19.5 12.25C19.5 13 18.75 13.5 17.75 13.5C16.75 13.5 16 13 15.75 12.5L14.75 13C15.25 13.75 16.25 14.25 17.75 14.25C19.5 14.25 20.75 13.25 20.75 12C20.75 11.25 20.25 10.75 19.5 10.25C19 10 18.75 9.75 18.75 9.25C18.75 8.75 19.25 8.25 19.75 8.25C20.25 8.25 20.5 8.5 20.75 8.75L21.5 8C21.25 7.75 20.75 7.5 20.25 7.5L22.5 8.25ZM10.5 7.5L11.75 12.5H9.75L11.75 7.5H10.5Z" fill="white"/>
                            </svg>
                            <svg width="32" height="20" viewBox="0 0 32 20" fill="none">
                                <rect width="32" height="20" rx="2" fill="#000"/>
                                <path d="M8 14.5L9.5 5.5H11.5L10 14.5H8ZM15.25 12.5L13.5 5.5H16C17.1 5.5 17.9 6.2 17.9 7.25C17.9 8 17.3 8.5 16.6 8.5L17.1 12.5H15.25ZM20.5 10.25H18.5L19.5 8.25L18.2 5.5H20.4C21.3 5.5 22 6.1 22 7C22 7.6 21.6 8 21 8.2L21.9 12.5H20.5V10.25ZM26.75 9.5C26.25 8.9 25.25 8.6 24 8.6L23.5 8.6L24.5 12.5H26.75V11.8C27.1 11.2 27.3 10.5 27.3 9.8C27.3 8.6 26.75 7.8 25.9 7.8C25 7.8 24.5 8.5 24.5 9.2H23C23 10.6 24 11.7 25.3 11.7C26.4 11.7 27 11.1 27.1 10.2L27.75 10.25V9.5H26.75Z" fill="white"/>
                            </svg>
                            <span>+ more</span>
                        </div>

                        <div class="secure-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Secured by Stripe
                        </div>
                    </div>

                    <div class="summary-card">
                        <h2 class="summary-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                            </svg>
                            Order Summary
                        </h2>
                        
                        <div class="summary-row">
                            <span class="summary-label">Product</span>
                            <span class="summary-value" id="summary-name">-</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Price</span>
                            <span class="summary-value" id="summary-price">-</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Quantity</span>
                            <span class="summary-value" id="summary-quantity">1</span>
                        </div>
                        
                        <div class="summary-row summary-total">
                            <span class="summary-label">Total</span>
                            <span class="summary-value" id="summary-subtotal">$0.00</span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg" id="checkout-btn" disabled>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Pay Now
                        </button>

                        <p style="text-align: center; color: var(--text-muted); font-size: 0.8125rem; margin-top: 1rem;">
                            You'll be redirected to complete payment if needed
                        </p>
                    </div>
                </div>
            </form>

            <div class="success-screen hidden" id="success-screen">
                <div class="success-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h2 class="success-title">Payment Successful!</h2>
                <p class="success-message">Thank you for your purchase. A confirmation email has been sent.</p>
                
                <div class="order-details">
                    <div class="order-detail-row">
                        <span class="order-detail-label">Order ID</span>
                        <span class="order-detail-value" id="order-id">-</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="order-detail-label">Email</span>
                        <span class="order-detail-value" id="order-email">-</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="order-detail-label">Amount Paid</span>
                        <span class="order-detail-value" id="order-amount">-</span>
                    </div>
                    <div class="order-detail-row">
                        <span class="order-detail-label">Status</span>
                        <span class="order-detail-value" style="color: var(--success);">Completed</span>
                    </div>
                </div>

                <button class="btn btn-secondary" onclick="window.location.reload()">
                    Make Another Purchase
                </button>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p class="footer-text">
                Powered by <a href="https://stripe.com" class="footer-link" target="_blank">Stripe</a>. 
                Secure payment processing.
            </p>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>