# Railway Deployment Checklist

## ✅ What's Already Configured

1. **nixpacks.toml** - Updated with build and start commands, PHP 8.2 extensions (including gd)
2. **Procfile** - Created as backup start command
3. **Database Configuration** - Uses environment variables (ready for Railway)
4. **composer.json** - Updated with ext-gd requirement
5. **.gitignore** - Properly configured

## ⚠️ Important: Update composer.lock

Before deploying, run this locally to update your composer.lock file:
```bash
composer update --lock
```
This ensures composer.lock includes the new `ext-gd` requirement.

## 📋 Required Environment Variables in Railway

Set these in your Railway project settings:

### Application
- `APP_NAME` - Your application name
- `APP_ENV` - Set to `production`
- `APP_KEY` - Run `php artisan key:generate` locally and copy the key, or let Railway generate it
- `APP_DEBUG` - Set to `false` for production
- `APP_URL` - Your Railway app URL (e.g., `https://your-app.railway.app`)

### Database (if using Railway MySQL/PostgreSQL)
- `DB_CONNECTION` - `mysql` or `pgsql`
- `DB_HOST` - Railway provides this
- `DB_PORT` - Railway provides this
- `DB_DATABASE` - Railway provides this
- `DB_USERNAME` - Railway provides this
- `DB_PASSWORD` - Railway provides this
- `DATABASE_URL` - Railway provides this automatically

### Cache & Sessions
- `CACHE_DRIVER` - `redis` or `file` (recommend `redis` for production)
- `SESSION_DRIVER` - `redis` or `file`
- `QUEUE_CONNECTION` - `redis` or `sync`

### Redis (if using Railway Redis)
- `REDIS_HOST` - Railway provides this
- `REDIS_PASSWORD` - Railway provides this
- `REDIS_PORT` - Railway provides this
- `REDIS_URL` - Railway provides this automatically

### Storage
- `FILESYSTEM_DISK` - `local` or `s3` (if using AWS S3)
- If using S3, add AWS credentials

### Other Required Variables
Based on your code, you may also need:
- `PURCHASE_CODE` - If your app requires this
- `BUYER_USERNAME` - If your app requires this
- Payment gateway credentials (PayPal, Stripe, Razorpay, etc.)
- Email/SMS service credentials

## 🚀 Deployment Steps

1. **Connect Repository to Railway**
   - Push your code to GitHub/GitLab
   - Connect the repository to Railway

2. **Add Database Service** (if needed)
   - Add MySQL or PostgreSQL service in Railway
   - Railway will automatically set `DATABASE_URL`

3. **Add Redis Service** (recommended for production)
   - Add Redis service in Railway
   - Railway will automatically set `REDIS_URL`

4. **Set Environment Variables**
   - Go to your Railway service → Variables tab
   - Add all required environment variables listed above

5. **Deploy**
   - Railway will automatically detect `nixpacks.toml` and build your app
   - The build process will:
     - Install PHP dependencies
     - Generate application key (if not set)
     - Cache configuration
     - Create storage link
     - Set permissions

6. **Run Migrations** (First deployment only)
   - In Railway, go to your service → Deployments
   - Click on the latest deployment → View Logs
   - Or use Railway CLI: `railway run php artisan migrate --force`

## ⚠️ Important Notes

1. **Storage**: Railway's filesystem is ephemeral. For file uploads, use:
   - AWS S3 or similar cloud storage, OR
   - Railway Volume (persistent storage)

2. **Queue Workers**: If using queues, you may need to:
   - Add a separate service for queue workers
   - Or use Railway's background services

3. **First Deployment**: After first deployment, you may need to:
   - Run migrations: `php artisan migrate --force`
   - Seed database if needed: `php artisan db:seed --force`
   - Create storage link: `php artisan storage:link`

4. **Custom Domain**: Configure custom domain in Railway settings if needed

## 🔍 Troubleshooting

- Check Railway deployment logs for errors
- Verify all environment variables are set
- Ensure database is accessible
- Check storage permissions
- Review Laravel logs: `storage/logs/laravel.log`

