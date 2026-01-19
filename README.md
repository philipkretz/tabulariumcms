# TabulariumCMS

**TabulariumCMS** is a professional, feature-rich Content Management System and E-Commerce platform built with Symfony 6. It combines powerful content management capabilities with a full-featured online store, advanced user management, and modern AI-powered content generation.

![TabulariumCMS](https://via.placeholder.com/800x400/667eea/ffffff?text=TabulariumCMS)

## Key Features

### Content Management System (CMS)
- **Multi-language Support** - Full support for English, German, Spanish, French, and Catalan
- **Page Builder** - Create and manage unlimited pages with SEO optimization
- **Blog System** - Full-featured blog with posts, categories, and comments
- **Media Management** - Upload and organize images, videos, and documents
- **Menu Management** - Flexible menu system with drag-and-drop ordering
- **Template System** - Customizable themes and templates

### E-Commerce Platform
- **Product Management** - Comprehensive product catalog with variants and attributes
- **Category System** - Organize products in hierarchical categories
- **Shopping Cart** - Full-featured cart with session persistence
- **Order Management** - Complete order processing workflow
- **Multiple Payment Methods**:
  - Prepayment (Bank Transfer)
  - Payment at Store
  - Stripe (Credit/Debit Cards)
  - PayPal
  - Amazon Pay
  - Klarna (Buy Now, Pay Later)
  - AliPay
  - BitPay (Bitcoin)
  - Google Pay
- **Shipping Methods** - Configurable shipping options with pricing
- **Price Formatting** - Currency-specific formatting (€ symbol positioning)
- **Voucher System** - Discount codes and promotional campaigns

### User Management & Social Features
- **User Registration & Authentication** - Secure user accounts
- **Two-Factor Authentication (2FA)** - Google Authenticator & Microsoft Authenticator support
- **User Profiles** - Customizable user profile pages
- **Friend System** - Send/accept friend requests, manage friendships
- **Private Messaging** - Direct messaging between users
- **User Blocking** - Block unwanted users
- **Media Uploads** - Isolated user media storage (up to 50 files per user)
- **Seller System** - Allow users to become sellers and manage their own products
- **Role-Based Access Control** - User, Seller, Admin roles

### Communication & Marketing
- **Newsletter System** - GDPR-compliant double opt-in newsletter
- **Email Templates** - Editable email templates for all events:
  - User registration
  - Newsletter confirmation/welcome/unsubscribe
  - Order confirmation
  - Order status changes
- **Cookie Banner** - GDPR-compliant cookie consent with editable text
- **Contact Forms** - Built-in contact form system

### AI-Powered Content Generation
- **One-Click Content Generation** - Generate content with AI using OpenAI GPT-4o-mini
- **Long Descriptions** - Automatically generate 200-300 word product/page descriptions
- **SEO Titles** - AI-optimized titles (max 60 characters)
- **SEO Descriptions** - AI-generated meta descriptions (max 155 characters)
- **SEO Keywords** - Automatic keyword generation
- **Works everywhere** - Automatic buttons in all admin forms (Products, Pages, Posts, Categories)

### Activity Logging & Security
- **Comprehensive Activity Logs** - Automatic logging of all important events:
  - Admin login/logout
  - Order creation and updates
  - Page/Post creation, updates, and deletion
  - Site settings changes
- **IP Tracking** - Track IP addresses and user agents
- **Change History** - Detailed change tracking with old/new values
- **Security Features** - CSRF protection, secure password hashing, XSS prevention

### Site Settings & Configuration
- **Feature Toggles** - Enable/disable features per user type:
  - User profiles
  - Friend system
  - Messaging
  - User media uploads
  - Seller system
  - User blocking
  - Two-factor authentication (per role)
- **Media Limits** - Configurable media limits per user
- **Privacy Settings** - Public/private profiles, approval requirements

### Admin Panel (Sonata Admin)
- **Professional Dashboard** - Clean, organized admin interface
- **Grouped Navigation** - Administration, CMS, E-Commerce, System, API sections
- **Activity Log Viewer** - Filter and search activity logs
- **User Management** - Manage users, roles, and permissions
- **Content Editors** - Rich text editors with media management
- **Bulk Actions** - Batch operations for products, orders, users
- **Export/Import** - Data export capabilities

## Requirements

### System Requirements
- **PHP**: 8.1 or higher
- **Composer**: 2.x
- **Database**: MariaDB 10.6+ or MySQL 8.0+
- **Web Server**: Nginx or Apache
- **Node.js**: 16.x or higher (for asset compilation)
- **Redis**: 6.x or higher (for caching and sessions)

### PHP Extensions
- `pdo_mysql`
- `intl`
- `json`
- `mbstring`
- `xml`
- `curl`
- `gd` or `imagick`
- `zip`
- `opcache`

### Optional but Recommended
- **Docker** & **Docker Compose** (for easy setup)
- **OpenAI API Key** (for AI content generation)

## Installation

### Method 1: Web-Based Setup Wizard (Recommended)

The easiest way to install TabulariumCMS is using the built-in web-based setup wizard.

#### Step 1: Start Docker Environment
```bash
git clone https://github.com/yourusername/tabulariumcms.git
cd tabulariumcms
docker-compose up -d
```

#### Step 2: Run the Setup Wizard
Open your browser and navigate to:
```
http://localhost:8080/setup
```

The wizard will guide you through:
1. **System Requirements** - Automatic check of PHP version, extensions, and directory permissions
2. **Database Configuration** - Enter your database credentials and test connection
3. **Admin Account** - Create your administrator account
4. **Installation** - Automatic database migration and default data loading
5. **Finalization** - Cache warming and installation completion

#### Step 3: Access Your Site
- **Frontend**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin
- **Mailhog** (Email Testing): http://localhost:8025

---

### Method 2: Docker Installation (Manual)

#### Step 1: Clone the Repository
```bash
git clone https://github.com/yourusername/tabulariumcms.git
cd tabulariumcms
```

#### Step 2: Configure Environment
```bash
cp .env .env.local
```

Edit `.env.local` and configure:
```env
# Database
DATABASE_URL="mysql://tabulariumcms:password@mariadb:3306/tabulariumcms"

# Mailer
MAILER_DSN=smtp://mailhog:1025

# Site Configuration
SITE_URL=http://localhost:8080
APP_ENV=dev

# Payment Configuration
STRIPE_PUBLIC_KEY=pk_test_your_key
STRIPE_SECRET_KEY=sk_test_your_key
STRIPE_WEBHOOK_SECRET=whsec_your_secret

PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_SANDBOX=true

# OpenAI (Optional - for AI content generation)
OPENAI_API_KEY=sk-your-openai-api-key-here
```

#### Step 3: Start Docker Containers
```bash
docker-compose up -d
```

This will start:
- **PHP-FPM** (tabulariumcms_php)
- **Nginx** (tabulariumcms_nginx)
- **MariaDB** (tabulariumcms_mariadb)
- **Redis** (tabulariumcms_redis)
- **Mailhog** (tabulariumcms_mailhog) - for email testing

#### Step 4: Install Dependencies
```bash
docker exec tabulariumcms_php composer install
```

#### Step 5: Create Database Schema
```bash
docker exec tabulariumcms_php php bin/console doctrine:migrations:migrate --no-interaction
```

#### Step 6: Load Default Data
```bash
docker exec tabulariumcms_php php bin/console app:load-default-data
```

This loads:
- Default languages (EN, DE, ES, FR, CA)
- Shipping methods
- Payment methods
- Email templates

#### Step 7: Create Admin User
```bash
docker exec tabulariumcms_php php bin/console app:create-admin
```

Follow the prompts to create your admin account.

**Default Admin Credentials:**
- Email: `admin@tabulariumcms.com`
- Password: `admin123`
- Role: Super Admin

**Important:** Change the default admin password after first login for security reasons.

#### Step 8: Clear Cache
```bash
docker exec tabulariumcms_php php bin/console cache:clear
```

#### Step 9: Access the Application

- **Frontend**: http://localhost:8080
- **Admin Panel**: http://localhost:8080/admin
- **Mailhog** (Email Testing): http://localhost:8025

---

### Method 3: Manual Installation

#### Step 1: Clone Repository
```bash
git clone https://github.com/yourusername/tabulariumcms.git
cd tabulariumcms
```

#### Step 2: Install PHP Dependencies
```bash
composer install
```

#### Step 3: Configure Environment
```bash
cp .env .env.local
```

Edit `.env.local` with your database credentials and configuration.

#### Step 4: Create Database
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

#### Step 5: Load Default Data
```bash
php bin/console app:load-default-data
```

#### Step 6: Create Admin User
```bash
php bin/console app:create-admin
```

**Default Admin Credentials:**
- User: `admin`
- Password: `admin123`
- Role: Super Admin

**Important:** Change the default admin password after first login for security reasons.

#### Step 7: Set Permissions
```bash
chmod -R 777 var/cache var/log
```

#### Step 8: Start Development Server
```bash
symfony server:start
# or
php -S localhost:8000 -t public
```

## Configuration

### Payment Configuration

TabulariumCMS supports multiple payment providers with full checkout integration.

#### Stripe Setup
1. Get your API keys from [Stripe Dashboard](https://dashboard.stripe.com/apikeys)
2. Add to `.env.local`:
```env
STRIPE_PUBLIC_KEY=pk_test_your_publishable_key
STRIPE_SECRET_KEY=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```
3. Configure webhook at Stripe Dashboard > Developers > Webhooks:
   - Endpoint URL: `https://your-domain.com/webhook/stripe`
   - Events: `checkout.session.completed`, `payment_intent.succeeded`

#### PayPal Setup
1. Get credentials from [PayPal Developer](https://developer.paypal.com/)
2. Add to `.env.local`:
```env
PAYPAL_CLIENT_ID=your_client_id
PAYPAL_CLIENT_SECRET=your_client_secret
PAYPAL_SANDBOX=true
```
3. Optional: Configure webhook at PayPal Developer Dashboard:
   - URL: `https://your-domain.com/webhook/paypal`
   - Events: `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`

### OpenAI API Key (AI Content Generation)

To enable AI-powered content generation:

1. Get an API key from [OpenAI Platform](https://platform.openai.com/)
2. Add to `.env.local`:
```env
OPENAI_API_KEY=sk-your-actual-api-key-here
```
3. Clear cache:
```bash
php bin/console cache:clear
```

### Email Configuration

For production, configure a real SMTP server in `.env.local`:
```env
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

### Site Settings

Configure site-wide settings in the admin panel:
1. Navigate to **Admin → Administration → Site Settings**
2. Configure:
   - User features (profiles, friends, messaging)
   - Social features (friend system, blocking)
   - Media settings (max files, max size)
   - Seller system
   - Two-factor authentication per user type

## 📖 Usage

### Admin Panel

Access the admin panel at `/admin`:

**Default Admin Credentials:**
- Email: `admin@tabulariumcms.com`
- Password: `admin123`

**Important:** Change the default password immediately after first login.

**Dashboard Sections:**
- **Administration** - Users, Roles, Site Settings, Activity Logs
- **CMS** - Pages, Posts, Media, Menus
- **E-Commerce** - Products, Categories, Orders, Vouchers
- **System** - Languages, Templates, Themes

### AI Content Generation

In any admin form (Product, Page, Post, Category):

1. Enter the **title** field
2. Optionally fill **short description**
3. Click **"Generate with AI"** button next to any field:
   - Long description/content
   - SEO title
   - SEO description
   - SEO keywords
4. Wait 2-5 seconds for generation
5. Review and edit the content
6. Save!

**Supported Fields:**
- Long descriptions (200-300 words)
- SEO titles (max 60 characters)
- SEO meta descriptions (max 155 characters)
- SEO keywords (5-10 keywords)

### Two-Factor Authentication

**Enable 2FA:**
1. Navigate to `/account/2fa`
2. Scan QR code with Google Authenticator or Microsoft Authenticator
3. Enter 6-digit code to verify
4. 2FA is now active

**Disable 2FA:**
1. Navigate to `/account/2fa`
2. Click "Disable 2FA"
3. Confirm action

**Admin Control:**
- Navigate to **Admin → Site Settings**
- Enable/disable 2FA per user type (Users, Sellers, Admins)
- Make 2FA mandatory if desired

### Newsletter System

**Subscribe:**
- Use the newsletter form on the homepage
- Receive confirmation email
- Click confirmation link (double opt-in)
- Receive welcome email

**Unsubscribe:**
- Click unsubscribe link in any newsletter email
- Confirmation of unsubscription

**Admin Management:**
- View subscribers in **Admin → Communication → Newsletter**
- Filter by active/confirmed status
- Export subscriber lists

### Activity Logs

View all system activity at **Admin → Administration → Activity Log**

**Logged Events:**
- Admin login/logout
- Order creation and updates
- Page/Post creation, updates, deletion
- Site settings changes

**Features:**
- Filter by action type, user, date range, IP address
- View detailed change history
- Export logs

## Project Structure

```
tabulariumcms/
├── config/              # Configuration files
│   ├── packages/        # Bundle configurations
│   └── routes/          # Route definitions
├── docs/                # Documentation
├── migrations/          # Database migrations
├── public/              # Public web root
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── uploads/        # User uploads
├── src/
│   ├── Admin/          # Sonata Admin classes
│   ├── Command/        # Console commands
│   ├── Controller/     # Controllers
│   ├── Entity/         # Doctrine entities
│   ├── EventListener/  # Event listeners
│   ├── Repository/     # Doctrine repositories
│   ├── Service/        # Business logic services
│   └── Twig/           # Twig extensions
├── templates/          # Twig templates
│   ├── admin/         # Admin templates
│   ├── frontend/      # Frontend templates
│   └── security/      # Auth templates
├── translations/       # Translation files
├── var/               # Cache, logs
└── docker-compose.yml # Docker configuration
```

## Development

### Running Tests
```bash
php bin/phpunit
```

### Code Quality
```bash
# PHP CS Fixer
php vendor/bin/php-cs-fixer fix

# PHPStan
php vendor/bin/phpstan analyse
```

### Database Migrations

**Create Migration:**
```bash
php bin/console doctrine:migrations:diff
```

**Run Migrations:**
```bash
php bin/console doctrine:migrations:migrate
```

**Rollback Migration:**
```bash
php bin/console doctrine:migrations:execute --down DoctrineMigrations\VersionXXXXXXXXXXXXXX
```

### Debugging

**Clear Cache:**
```bash
php bin/console cache:clear
```

**View Logs:**
```bash
tail -f var/log/dev.log
```

**Database Queries:**
```bash
php bin/console doctrine:query:sql "SELECT * FROM user LIMIT 5"
```

## Multi-Language Support

TabulariumCMS supports 5 languages out of the box:
- 🇬🇧 English (en)
- 🇩🇪 German (de)
- 🇪🇸 Spanish (es)
- 🇫🇷 French (fr)
- 🇪🇸 Catalan (ca)

**Add New Language:**
1. Create translation file: `translations/messages.{locale}.yaml`
2. Add language to database: `INSERT INTO language (code, name) VALUES ('it', 'Italiano')`
3. Clear cache

## Security

### Best Practices
- Never commit `.env` files with sensitive data
- Use strong passwords for admin accounts
- Enable 2FA for all admin users
- Keep dependencies updated: `composer update`
- Use HTTPS in production
- Configure proper file permissions
- Regular database backups

### Security Features
- CSRF protection on all forms
- XSS prevention via Twig auto-escaping
- SQL injection prevention via Doctrine ORM
- Password hashing with bcrypt
- Rate limiting on login attempts
- Secure session handling
- User input validation

## Performance

### Optimization Tips
- Enable PHP OPcache in production
- Use Redis for sessions and cache
- Enable Symfony cache warmer
- Optimize images before upload
- Use CDN for static assets
- Enable Gzip compression
- Database query optimization with indexes

### Cache Management
```bash
# Production cache
php bin/console cache:clear --env=prod --no-debug

# Warm up cache
php bin/console cache:warmup --env=prod
```

## Troubleshooting

### Common Issues

**Error: "An exception occurred in driver: SQLSTATE[HY000] [2002] Connection refused"**
- Check database connection in `.env.local`
- Ensure MariaDB/MySQL is running
- Verify database credentials

**Error: "Unable to write in the cache directory"**
```bash
chmod -R 777 var/cache var/log
```

**AI Generation Returns "Not Available"**
- Check OpenAI API key is configured in `.env.local`
- Verify API key starts with `sk-`
- Clear Symfony cache
- Check OpenAI account has credits

**Admin Panel Not Loading**
```bash
php bin/console cache:clear
php bin/console assets:install --symlink
```

**2FA Not Working**
- Ensure time is synchronized on server and device
- Check that 2FA bundle is configured
- Verify routes are loaded: `php bin/console debug:router | grep 2fa`

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

## Acknowledgments

- Built with [Symfony](https://symfony.com/)
- Admin panel powered by [Sonata Admin](https://sonata-project.org/)
- AI generation using [OpenAI GPT-4o-mini](https://openai.com/)
- Icons from [Font Awesome](https://fontawesome.com/)
- Styling with [Tailwind CSS](https://tailwindcss.com/)

## Support

For support, issues, or feature requests:
- Open an issue on [GitHub Issues](https://github.com/yourusername/tabulariumcms/issues)
- Email: support@tabulariumcms.com
- Documentation: [Full Documentation](https://docs.tabulariumcms.com)

## Roadmap

### Upcoming Features
- [ ] Advanced SEO analytics dashboard
- [ ] Multi-vendor marketplace functionality
- [ ] Advanced product filtering and search
- [ ] Customer reviews and ratings system
- [ ] Wishlists and product comparison
- [ ] Advanced shipping calculator
- [ ] Tax calculation by region
- [ ] Invoice generation
- [ ] Inventory management
- [ ] Email marketing automation
- [ ] A/B testing for content
- [ ] Progressive Web App (PWA) support
- [ ] Mobile app (iOS/Android)
- [ ] API documentation with Swagger
- [ ] GraphQL API
- [ ] Headless CMS mode

---

**Made with ❤️ by Profundi Web Solutions, S.L.**

*Last Updated: January 2026*
