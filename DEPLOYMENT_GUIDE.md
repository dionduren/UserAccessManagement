# Docker Deployment Commands - Quick Reference

## 🚀 Full Deployment (Recommended)

Upload `deploy.sh` to your server, then run:

```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 📋 Manual Step-by-Step Commands

If you prefer manual deployment:

### 1. Pull Latest Code
```bash
git pull origin main
```

### 2. Stop Containers
```bash
docker-compose down
```

### 3. Rebuild Images (if Dockerfile changed)
```bash
docker-compose build --no-cache
```

### 4. Start Containers
```bash
docker-compose up -d
```

### 5. Install Dependencies
```bash
docker-compose exec app composer install --optimize-autoloader --no-dev
```

### 6. Run Migrations
```bash
docker-compose exec app php artisan migrate --force
```

### 7. Clear All Caches
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
docker-compose exec app php artisan cache:clear
```

### 8. Rebuild Caches
```bash
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 9. Optimize Autoload
```bash
docker-compose exec app composer dump-autoload --optimize
```

### 10. Set Permissions
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### 11. Restart Queue Workers (if using queues)
```bash
docker-compose exec app php artisan queue:restart
```

---

## 🔧 Quick Update (No Docker Rebuild)

For code-only changes without Dockerfile modifications:

```bash
git pull origin main
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan queue:restart
docker-compose restart app
```

---

## 🩺 Troubleshooting Commands

### View Logs
```bash
# All containers
docker-compose logs -f

# Specific container
docker-compose logs -f app

# Last 100 lines
docker-compose logs --tail=100 app
```

### Check Container Status
```bash
docker-compose ps
```

### Enter Container Shell
```bash
docker-compose exec app bash
```

### Restart Specific Service
```bash
docker-compose restart app
```

### Full Reset (⚠️ Removes all data)
```bash
docker-compose down -v
docker-compose up -d
docker-compose exec app php artisan migrate:fresh --seed
```

---

## 🔄 Rollback Commands

If deployment fails:

```bash
# Revert git changes
git reset --hard HEAD~1

# Rebuild and restart
docker-compose down
docker-compose up -d --build
```

---

## 📊 Health Checks

```bash
# Check app is responding
curl http://localhost:8000/health

# Check database connection
docker-compose exec app php artisan tinker --execute="DB::connection()->getPdo();"

# Check queue workers
docker-compose exec app php artisan queue:work --once
```

---

## 🎯 Production Best Practices

1. **Always backup database before deployment:**
   ```bash
   docker-compose exec db pg_dump -U username dbname > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Test in staging first:**
   ```bash
   git checkout staging
   ./deploy.sh
   ```

3. **Monitor logs during deployment:**
   ```bash
   docker-compose logs -f app &
   ./deploy.sh
   ```

4. **Enable maintenance mode for major updates:**
   ```bash
   docker-compose exec app php artisan down --retry=60
   ./deploy.sh
   docker-compose exec app php artisan up
   ```

---

## 🚨 Emergency Rollback

```bash
# Quick rollback to previous commit
git log --oneline -5  # Find commit hash
git reset --hard <commit-hash>
docker-compose down
docker-compose up -d --build
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan migrate:rollback --step=1
docker-compose exec app php artisan cache:clear
```
