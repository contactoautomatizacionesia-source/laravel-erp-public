# 🚀 Guía de Configuración: Entorno de Generación PDF (Browsershot + Chrome)
Este documento detalla los pasos técnicos necesarios para configurar un servidor Ubuntu Linux (22.04 LTS o superior) para la generación de PDFs utilizando la librería spatie/browsershot en un entorno Laravel.

## 1. Instalación de Dependencias del Sistema
Browsershot requiere que el servidor tenga instaladas las librerías compartidas necesarias para ejecutar un navegador Chromium en modo headless.

```
sudo apt-get update
sudo apt-get install -y \
    libnss3 \
    libatk1.0-0 \
    libatk-bridge2.0-0 \
    libcups2 \
    libdrm2 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libasound2 \
    libpangocairo-1.0-0 \
    libxshmfence1 \
    libx11-xcb1 \
    unzip
```
##  🌐 Paso 2: Instalación de Google Chrome (Binario Oficial)
No se recomienda usar la instalación automática de Puppeteer en servidores de producción. Es preferible usar el binario oficial de Google Chrome para garantizar estabilidad con el Kernel de Linux.

```
# Descargar el instalador oficial
wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb

# Instalar el paquete (esto también instala dependencias faltantes)
sudo apt install ./google-chrome-stable_current_amd64.deb -y

# Confirmar la ruta del binario (Usualmente /usr/bin/google-chrome)
which google-chrome
```

##  🔑 Paso 3: Configuración de Directorios y Permisos
Chrome necesita un entorno de usuario (HOME) y una carpeta temporal para perfiles de ejecución. Si el usuario del servidor web (www o www-data) no tiene estos permisos, el proceso lanzará un Trace/breakpoint trap.
```
# 1. Crear el HOME para el usuario del servidor web
sudo mkdir -p /home/www
sudo chown -R www:www /home/www
sudo chmod -R 755 /home/www

# 2. Crear la carpeta de perfiles en el storage del proyecto
mkdir -p storage/puppeteer_profile
sudo chown -R www:www storage/puppeteer_profile
sudo chmod -R 775 storage/puppeteer_profile
```

## 📦 Paso 4: Node.js y Puppeteer
Instalar Node.js y las dependencias dentro del proyecto

```
# Instalación de Node.js 20 (Recomendado)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Instalar dependencias en la raíz del proyecto
npm install
# Asegurarse de que Puppeteer esté instalado
npm install puppeteer --save-dev
```

## 💻 Paso 5: Implementación en PHP (Service Layer)
El siguiente método es la configuración "blindada" que resuelve los problemas de PATH y permisos de ejecución encontrados en entornos de producción.
```
private function getBrowsershotInstance(string $html): Browsershot
{
    $userDataDir = storage_path('puppeteer_profile');

    return Browsershot::html($html)
        ->setChromePath('/usr/bin/google-chrome') // Binario oficial
        ->setNodeBinary('/usr/bin/node')
        ->setNpmBinary('/usr/bin/npm')
        ->setEnvVars([
            'HOME' => '/home/www', // Home configurado en el Paso 3
            'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin', // Path completo del sistema
            'NODE_PATH' => base_path('node_modules'),
            'CHROME_CRASH_REPORTER_DISABLED' => '1',
        ])
        ->addArgs([
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
            '--disable-breakpad',
            '--no-zygote',
            '--user-data-dir=' . $userDataDir,
        ])
        ->paperSize(215.9, 279.4) // Tamaño Carta
        ->margins(10, 12, 12, 12)
        ->showBackground();
}
```

## 🔄 Paso 6: Despliegue y Reinicio
Cada vez que se despliegue código o se realicen cambios en el servidor, es obligatorio limpiar la caché y reiniciar PHP-FPM para vaciar el OPcache de los scripts PHP.
```
# 1. Limpiar caché de Laravel
php artisan optimize:clear

# 2. Reiniciar PHP-FPM (Ajustar a la versión instalada, ej: 8.2)
sudo /etc/init.d/php-fpm-82 restart
```

## ⚠️ Diagnóstico (Troubleshooting)
Si la generación sigue fallando, ejecute el siguiente comando manual como el usuario www para ver el error real de salida de Chrome:
```
sudo -u www HOME=/home/www /usr/bin/google-chrome --headless --no-sandbox --disable-gpu --dump-dom https://google.com
```

- Error 127: Chrome no encuentra herramientas básicas. Revisa el PATH en el código PHP.
- Trace/breakpoint trap: Incompatibilidad de binarios o falta de permisos en el HOME.
- Permission Denied: Verifica los permisos de las carpetas /home/www y storage/.
