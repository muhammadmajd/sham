# MVPN Backend Deployment Guide

## Step 1: Install Laravel Dependencies on Server

### Update System and Install Required Packages
```bash
sudo apt-get update
sudo apt-get upgrade
sudo apt-get install -y unzip curl git supervisor
```

### Install PHP and Extensions
```bash
sudo apt-get install -y php php-cli php-fpm php-mysql php-xml php-mbstring php-curl php-zip php-gd php-bcmath php-sqlite3
```

### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Install Node.js (for asset building)
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
sudo npm install -g npm@latest
```

### Install MySQL/PostgreSQL (if using)
```bash
sudo apt-get install -y mysql-server
# OR
sudo apt-get install -y postgresql
```

---

## Step 2: Create Project Directory and Clone

```bash
sudo mkdir -p /var/www/mvpnapi
sudo chown -R $USER:$USER /var/www/mvpnapi
cd /var/www/mvpnapi

# Clone your project (using git, FTP, or transfer files)
# Example with git:
git clone https://your-repo-url.git .
```

---

## Step 3: Install Laravel Dependencies

```bash
cd /var/www/mvpnapi
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

---

## Step 4: Configure Environment

```bash
cp .env.example .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
```

Edit `.env` with your database and server settings:
```bash
nano .env
```

Key settings:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `DB_*` (database settings)

---

## Step 5: Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/mvpnapi/storage /var/www/mvpnapi/bootstrap/cache
sudo chmod -R 775 /var/www/mvpnapi/storage /var/www/mvpnapi/bootstrap/cache
```

---

## Step 6: Run Migrations

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder  # If you have admin seeder
```

---

## Running Laravel Scheduler with the Application

For production deployment, you need to run both the web server and the scheduler. Here are your options:

---

## Option 1: Using Supervisor (RECOMMENDED for Production)

### Install Supervisor
```bash
sudo apt-get install supervisor
```

### Create Configuration File
```bash
sudo nano /etc/supervisor/conf.d/mvpnapi.conf
```

### Add This Configuration
```ini
[program:mvpnapi]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/mvpnapi/artisan serve --host=0.0.0.0 --port=8000
directory=/path/to/your/mvpnapi
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/mvpnapi/app.log
stopwaitsecs=3600

[program:mvpnapi-scheduler]
process_name=%(program_name)s
command=php /path/to/your/mvpnapi/artisan schedule:run
directory=/path/to/your/mvpnapi
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/mvpnapi/scheduler.log
user=www-data
```

### Start Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start mvpnapi:*
sudo supervisorctl start mvpnapi-scheduler
```

### Check Status
```bash
sudo supervisorctl status
```

---

## Option 2: Using Screen/Tmux

### Start the App
```bash
screen -S mvpn
php artisan serve --host=0.0.0.0 --port=8000
# Press Ctrl+A then D to detach
```

### Start the Scheduler
```bash
screen -S scheduler
php artisan schedule:work
# Press Ctrl+A then D to detach
```

### Reattach to Sessions
```bash
screen -r mvpn    # Reattach to app
screen -r scheduler  # Reattach to scheduler
```

---

## Option 3: Using Systemd

### Create App Service
```bash
sudo nano /etc/systemd/system/mvpnapi.service
```

```ini
[Unit]
Description=MVPN API Application
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/mvpnapi
ExecStart=php /path/to/your/mvpnapi/artisan serve --host=0.0.0.0 --port=8000
Restart=always

[Install]
WantedBy=multi-user.target
```

### Create Scheduler Service
```bash
sudo nano /etc/systemd/system/mvpnapi-scheduler.service
```

```ini
[Unit]
Description=MVPN API Scheduler
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/your/mvpnapi
ExecStart=php /path/to/your/mvpnapi/artisan schedule:run
Restart=always
```

### Enable and Start Services
```bash
sudo systemctl daemon-reload
sudo systemctl enable mvpnapi mvpnapi-scheduler
sudo systemctl start mvpnapi mvpnapi-scheduler
```

### Check Status
```bash
sudo systemctl status mvpnapi
sudo systemctl status mvpnapi-scheduler
```

---

## Option 4: Quick Test (Development Only)

```bash
# Run both in background
php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
php artisan schedule:work > /dev/null 2>&1 &
```

---

## Production Checklist

1. **Set Environment Variables**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

2. **Configure Database**
   ```bash
   php artisan migrate
   ```

3. **Build Assets**
   ```bash
   npm install
   npm run build
   ```

4. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /path/to/mvpnapi
   sudo chmod -R 755 /path/to/mvpnapi/storage
   sudo chmod -R 755 /path/to/mvpnapi/bootstrap/cache
   ```

5. **Set Up Queue Worker (if using queues)**
   ```bash
   php artisan queue:work
   ```

---

## Recommended Setup for Production

Use **Nginx + PHP-FPM + Supervisor** for best performance:

1. Configure Nginx to point to `public/index.php`
2. Use PHP-FPM instead of `artisan serve`
3. Use Supervisor for process management (Option 1)

This provides better performance, security, and reliability than the built-in Laravel server.
