# Proceso de implementación propuesto
1. Copiar la migración a database/migrations/
2. Copiar los seeders a database/seeders/
3. Separar CatSeedersGroup.php en 4 archivos individuales
   - (uno por clase) o mantenerlo así si tu autoloader lo permite

## Ejecutar la migración
```php
php artisan migrate
```

## Ejecutar solo los seeders del módulo
```php
php artisan db:seed --class=SanctionsModuleSeeder
```
O agregar al DatabaseSeeder.php principal:
```php
$this->call(SanctionsModuleSeeder::class);
```