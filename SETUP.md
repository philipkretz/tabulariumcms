# TabulariumCMS - Installation & Setup Guide

## Overview
TabulariumCMS is a comprehensive content management system and e-commerce platform built with Symfony 7.4, Tailwind CSS, and modern payment integrations. It provides a web-based setup wizard for easy installation.

## Quick Start (Web-Based Setup Wizard)

The easiest way to set up TabulariumCMS is using the built-in web-based setup wizard.

### Step 1: Start Your Environment

**Using Docker (Recommended):**
```bash
git clone https://github.com/yourusername/tabulariumcms.git
cd tabulariumcms
docker-compose up -d
```

**Manual Setup:**
```bash
git clone https://github.com/yourusername/tabulariumcms.git
cd tabulariumcms
composer install
```

### Step 2: Access the Setup Wizard

Open your browser and navigate to:
```
http://localhost:8080/setup
```

The setup wizard will guide you through:

1. **System Requirements Check**
   - PHP version (8.1+)
   - Required extensions (pdo_mysql, intl, mbstring, xml, json, ctype, iconv)
   - Directory permissions (var/, var/cache/, var/log/, public/uploads/)

2. **Database Configuration**
   - Enter database host, port, name, username, and password
   - Test connection before proceeding
   - Automatic database creation if needed

3. **Admin Account Creation**
   - Set up your administrator email and password
   - Configure site name and URL

4. **Database Migration**
   - Automatic schema creation
   - Default data loading (languages, payment methods, shipping methods, email templates)

5. **Finalization**
   - Cache warming
   - Installation lock file creation

### Step 3: Access Your Site

After setup completes:
- **Frontend**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin

## Manual Installation (Alternative)

If you prefer manual installation or the setup wizard is not available:

### 1. Environment Setup
```bash
cd tabulariumcms
composer install
cp .env .env.local
```

### 2. Configure Environment
Edit `.env.local`:
```env
# Database
DATABASE_URL="mysql://user:password@localhost:3306/tabulariumcms"

# Application
APP_ENV=prod
APP_SECRET=your-secret-key-here

# Site Configuration
SITE_URL=https://your-domain.com
```

### 3. Database Setup
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:load-default-data
```

### 4. Create Admin User
```bash
php bin/console app:create-admin
```

### 5. Set Permissions
```bash
chmod -R 777 var/cache var/log public/uploads
```

### 6. Clear Cache
```bash
php bin/console cache:clear --env=prod
```

## Payment Configuration

TabulariumCMS supports multiple payment providers with full integration.

### Stripe Configuration

1. Get your API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)

2. Add to `.env.local`:
```env
STRIPE_PUBLIC_KEY=pk_test_your_publishable_key
STRIPE_SECRET_KEY=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

3. Set up Stripe webhook:
   - Go to Stripe Dashboard > Developers > Webhooks
   - Add endpoint: `https://your-domain.com/webhook/stripe`
   - Select events: `checkout.session.completed`, `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Copy the webhook signing secret to `STRIPE_WEBHOOK_SECRET`

### PayPal Configuration

1. Get your credentials from [PayPal Developer](https://developer.paypal.com/)

2. Add to `.env.local`:
```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_SANDBOX=true  # Set to false for production
```

3. Set up PayPal webhook (optional but recommended):
   - Go to PayPal Developer Dashboard > Webhooks
   - Add webhook URL: `https://your-domain.com/webhook/paypal`
   - Select events: `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`

### Payment Flow

When a customer completes checkout:

1. **Stripe/PayPal**: Customer is redirected to the payment provider
2. **After payment**: Customer returns to your site with payment confirmation
3. **Order status**: Automatically updated to "Payment Received"
4. **Webhooks**: Backup confirmation via webhook for reliability

### Other Payment Methods

- **Prepayment (Bank Transfer)**: Customer receives bank details, manual confirmation required
- **Cash on Delivery**: No online payment, cash collected at delivery
- **Pay at Store**: For store pickup orders

## Directory Structure

```
tabulariumcms/
├── config/              # Configuration files
│   ├── packages/        # Bundle configurations
│   └── routes/          # Route definitions
├── public/              # Public web root
│   ├── uploads/         # User uploads
│   └── assets/          # Compiled assets
├── src/
│   ├── Controller/      # HTTP Controllers
│   ├── Entity/          # Doctrine entities
│   ├── Repository/      # Data access layer
│   ├── Service/         # Business logic
│   │   └── Payment/     # Payment services (Stripe, PayPal)
│   ├── Admin/           # Sonata Admin classes
│   └── EventListener/   # Event listeners
├── templates/           # Twig templates
│   ├── checkout/        # Checkout templates
│   ├── setup/           # Setup wizard templates
│   └── admin/           # Admin templates
├── translations/        # Translation files (en, de, es, fr, ca)
├── var/                 # Cache and logs
├── .env                 # Environment template
├── .env.local           # Local environment (create this)
└── docker-compose.yml   # Docker configuration
```

## Features

### E-Commerce
- Product management with variants
- Shopping cart with session persistence
- Multiple payment methods (Stripe, PayPal, Bank Transfer, etc.)
- Order management and tracking
- Voucher/discount codes
- Shipping method configuration

### Content Management
- Pages with SEO optimization
- Blog posts with categories
- Media management
- Menu builder
- Multi-language support (28 languages)

### User Management
- User registration and authentication
- Two-factor authentication (Google Authenticator)
- User profiles with social features
- Friend system and messaging
- Seller accounts for marketplace

### Admin Panel
- Sonata Admin integration
- Activity logging
- Email templates
- Site settings
- API key management

## Security

- CSRF protection on all forms
- SQL injection prevention via Doctrine ORM
- XSS protection via Twig auto-escaping
- Secure password hashing (bcrypt)
- Rate limiting on authentication
- Two-factor authentication support
- Webhook signature verification

## Troubleshooting

### Setup Wizard Not Available
If `/setup` redirects to a localized URL like `/de/setup`:
- This is fixed in the latest version
- Clear cache: `php bin/console cache:clear`

### Permission Errors
```bash
chmod -R 777 var/cache var/log public/uploads
```

### Database Connection Failed
- Verify database credentials in `.env.local`
- Ensure database server is running
- Check if database exists or needs to be created

### Payment Not Working
- Verify API keys are correct in `.env.local`
- Check that webhook endpoints are accessible
- Review logs: `tail -f var/log/dev.log`

## Support

- GitHub Issues: https://github.com/yourusername/tabulariumcms/issues
- Documentation: https://docs.tabulariumcms.com
- Email: support@tabulariumcms.com

---

**TabulariumCMS - Professional CMS & E-Commerce Platform**
