'use strict';

const STRIPE_PK = 'pk_test_your_stripe_publishable_key';

let stripe;
let elements;
let cardElement;
let selectedProduct = null;
let products = [];
let orderData = null;

async function initStripe() {
    try {
        stripe = Stripe(STRIPE_PK);
        elements = stripe.elements({
            appearance: {
                theme: 'night',
                variables: {
                    colorPrimary: '#22c55e',
                    colorBackground: '#09090b',
                    colorText: '#fafafa',
                    colorTextSecondary: '#a1a1aa',
                    colorTextPlaceholder: '#71717a',
                    colorDanger: '#ef4444',
                    fontFamily: 'Inter, system-ui, sans-serif',
                    borderRadius: '6px',
                    spacingUnit: '4px'
                },
                rules: {
                    '.Input': {
                        border: '1px solid #27272a',
                        backgroundColor: '#09090b'
                    },
                    '.Input:focus': {
                        border: '1px solid #22c55e',
                        boxShadow: '0 0 0 3px rgba(34, 197, 94, 0.3)'
                    },
                    '.Label': {
                        fontWeight: '500',
                        marginBottom: '6px'
                    }
                }
            }
        });
        
        cardElement = elements.create('payment');
        cardElement.mount('#card-element');
        
        cardElement.on('change', (event) => {
            const errorEl = document.getElementById('card-errors');
            const container = document.getElementById('card-container');
            
            if (event.error) {
                errorEl.textContent = event.error.message;
                container.classList.add('error');
            } else {
                errorEl.textContent = '';
                container.classList.remove('error');
            }
        });
        
        cardElement.on('focus', () => {
            document.getElementById('card-container').classList.add('focused');
        });
        
        cardElement.on('blur', () => {
            document.getElementById('card-container').classList.remove('focused');
        });
        
    } catch (error) {
        console.error('Stripe initialization error:', error);
        showError('Failed to initialize payment system');
    }
}

async function loadProducts() {
    try {
        showLoading(true);
        
        const response = await fetch('api/products.php');
        const data = await response.json();
        
        if (data.success) {
            products = data.products;
            renderProducts();
        } else {
            showError(data.error || 'Failed to load products');
        }
        
    } catch (error) {
        console.error('Error loading products:', error);
        showError('Failed to load products');
    } finally {
        showLoading(false);
    }
}

function renderProducts() {
    const grid = document.getElementById('products-grid');
    if (!grid || products.length === 0) return;
    
    grid.innerHTML = products.map((product, index) => `
        <div class="product-card" data-id="${product.id}" onclick="selectProduct(${index})">
            <img src="${product.image_url}" alt="${product.name}" class="product-image" 
                 onerror="this.src='https://images.unsplash.com/photo-1557682250-33bd709cbe85?w=800'">
            <div class="product-content">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-footer">
                    <span class="product-price">$${parseFloat(product.price).toFixed(2)} <span>/ one-time</span></span>
                    <button class="product-button" onclick="event.stopPropagation(); selectProduct(${index})">
                        Select
                    </button>
                </div>
            </div>
        </div>
    `).join('');
    
    if (products.length > 0) {
        selectProduct(0);
    }
}

function selectProduct(index) {
    selectedProduct = products[index];
    
    document.querySelectorAll('.product-card').forEach((card, i) => {
        card.classList.toggle('selected', i === index);
    });
    
    updateSummary();
}

function updateSummary() {
    if (!selectedProduct) return;
    
    const name = document.getElementById('summary-name');
    const price = document.getElementById('summary-price');
    const quantity = document.getElementById('summary-quantity');
    const subtotal = document.getElementById('summary-subtotal');
    
    if (name) name.textContent = selectedProduct.name;
    if (price) price.textContent = `$${parseFloat(selectedProduct.price).toFixed(2)}`;
    if (quantity) quantity.textContent = '1';
    if (subtotal) subtotal.textContent = `$${parseFloat(selectedProduct.price).toFixed(2)}`;
    
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = !selectedProduct;
    }
}

function validateForm() {
    const name = document.getElementById('customer-name');
    const email = document.getElementById('customer-email');
    
    let isValid = true;
    
    if (!name.value.trim()) {
        showFieldError(name, 'Name is required');
        isValid = false;
    } else {
        clearFieldError(name);
    }
    
    if (!email.value.trim()) {
        showFieldError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value)) {
        showFieldError(email, 'Please enter a valid email');
        isValid = false;
    } else {
        clearFieldError(email);
    }
    
    return isValid && selectedProduct !== null;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showFieldError(input, message) {
    input.classList.add('error');
    let errorEl = input.nextElementSibling;
    if (!errorEl || !errorEl.classList.contains('form-error')) {
        errorEl = document.createElement('div');
        errorEl.className = 'form-error';
        input.parentNode.insertBefore(errorEl, input.nextSibling);
    }
    errorEl.textContent = message;
}

function clearFieldError(input) {
    input.classList.remove('error');
    const errorEl = input.nextElementSibling;
    if (errorEl && errorEl.classList.contains('form-error')) {
        errorEl.textContent = '';
    }
}

async function handlePayment() {
    if (!validateForm()) {
        return;
    }
    
    if (!selectedProduct || !stripe || !cardElement) {
        showError('Please configure your payment method');
        return;
    }
    
    try {
        showLoading(true);
        hideError();
        
        const amount = parseFloat(selectedProduct.price);
        
        const intentResponse = await fetch('api/create-payment-intent.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: amount,
                currency: 'usd',
                productId: selectedProduct.id,
                quantity: 1
            })
        });
        
        const intentData = await intentResponse.json();
        
        if (!intentData.success) {
            throw new Error(intentData.error || 'Failed to create payment intent');
        }
        
        const { clientSecret, paymentIntentId } = intentData;
        
        orderData = {
            stripePaymentId: paymentIntentId,
            customerName: document.getElementById('customer-name').value.trim(),
            customerEmail: document.getElementById('customer-email').value.trim(),
            productId: selectedProduct.id,
            quantity: 1,
            amount: amount,
            currency: 'USD'
        };
        
        const { error, paymentIntent } = await stripe.confirmPayment({
            elements,
            clientSecret,
            confirmParams: {
                return_url: window.location.href,
                payment_method_data: {
                    billing_details: {
                        name: orderData.customerName,
                        email: orderData.customerEmail
                    }
                }
            },
            redirect: 'if_required'
        });
        
        if (error) {
            throw new Error(error.message);
        }
        
        if (paymentIntent && paymentIntent.status === 'succeeded') {
            await createOrder(paymentIntent.status);
            showSuccess(paymentIntent);
        }
        
    } catch (error) {
        console.error('Payment error:', error);
        showError(error.message || 'Payment failed. Please try again.');
    } finally {
        showLoading(false);
    }
}

async function createOrder(status = 'succeeded') {
    if (!orderData) return;
    
    try {
        orderData.status = status;
        
        await fetch('api/create-order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });
        
    } catch (error) {
        console.error('Error creating order:', error);
    }
}

function showSuccess(paymentIntent) {
    const checkout = document.getElementById('checkout-form');
    const success = document.getElementById('success-screen');
    
    if (checkout && success) {
        checkout.classList.add('hidden');
        success.classList.remove('hidden');
        success.classList.add('fade-in');
        
        document.getElementById('order-id').textContent = `#${paymentIntent.id.slice(-8).toUpperCase()}`;
        document.getElementById('order-email').textContent = orderData.customerEmail;
        document.getElementById('order-amount').textContent = `$${parseFloat(orderData.amount).toFixed(2)}`;
    }
}

function showLoading(show) {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.classList.toggle('active', show);
    }
}

function showError(message) {
    const alert = document.getElementById('error-alert');
    if (alert) {
        alert.textContent = message;
        alert.classList.remove('hidden');
        alert.classList.add('fade-in');
        
        setTimeout(() => {
            alert.classList.add('hidden');
        }, 5000);
    }
}

function hideError() {
    const alert = document.getElementById('error-alert');
    if (alert) {
        alert.classList.add('hidden');
    }
}

function checkPaymentStatus() {
    const urlParams = new URLSearchParams(window.location.search);
    const paymentIntentClientSecret = urlParams.get('payment_intent_client_secret');
    const redirectStatus = urlParams.get('redirect_status');
    
    if (paymentIntentClientSecret && redirectStatus === 'succeeded') {
        stripe.retrievePaymentIntent(paymentIntentClientSecret).then(async (result) => {
            if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                await createOrder('succeeded');
                showSuccess(result.paymentIntent);
            }
        }).catch(console.error);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    await initStripe();
    await loadProducts();
    await checkPaymentStatus();
    
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handlePayment();
        });
    }
    
    const customerName = document.getElementById('customer-name');
    const customerEmail = document.getElementById('customer-email');
    
    if (customerName) {
        customerName.addEventListener('input', () => clearFieldError(customerName));
    }
    if (customerEmail) {
        customerEmail.addEventListener('input', () => clearFieldError(customerEmail));
    }
    
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handlePayment);
    }
});

window.selectProduct = selectProduct;
window.STRIPE_PK = STRIPE_PK;