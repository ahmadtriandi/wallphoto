#!/bin/bash
# ═══════════════════════════════════════════════════════════
#  install.sh — Setup otomatis Laravel Photo Gallery
#  Jalankan: bash install.sh
# ═══════════════════════════════════════════════════════════

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}"
echo "╔══════════════════════════════════════╗"
echo "║   Laravel Photo Gallery — Setup      ║"
echo "╚══════════════════════════════════════╝"
echo -e "${NC}"

# 1. Copy .env
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✓ File .env dibuat${NC}"
fi

# 2. Install PHP dependencies
echo -e "\n${YELLOW}[1/6] Menginstall PHP dependencies...${NC}"
composer install --no-interaction --prefer-dist

# 3. Generate app key
echo -e "\n${YELLOW}[2/6] Generate application key...${NC}"
php artisan key:generate

# 4. Install dan setup Laravel Reverb
echo -e "\n${YELLOW}[3/6] Setup Laravel Reverb (WebSocket)...${NC}"
php artisan reverb:install --no-interaction || true

# 5. Migrate database
echo -e "\n${YELLOW}[4/6] Migrasi database...${NC}"
echo -e "${CYAN}Pastikan database '${DB_DATABASE:-gallery_db}' sudah dibuat di MySQL!${NC}"
php artisan migrate --force

# 6. Install JS dependencies
echo -e "\n${YELLOW}[5/6] Menginstall JS dependencies...${NC}"
npm install

# 7. Build assets
echo -e "\n${YELLOW}[6/6] Build frontend assets...${NC}"
npm run build

echo -e "\n${GREEN}"
echo "╔══════════════════════════════════════════════════════╗"
echo "║   ✓ Instalasi selesai!                               ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║                                                      ║"
echo "║   Jalankan 3 perintah ini di terminal terpisah:      ║"
echo "║                                                      ║"
echo "║   Terminal 1: php artisan serve                      ║"
echo "║   Terminal 2: php artisan reverb:start               ║"
echo "║   Terminal 3: php artisan queue:work                 ║"
echo "║                                                      ║"
echo "║   Lalu buka:                                         ║"
echo "║   Gallery : http://localhost:8000/gallery            ║"
echo "║   Upload  : http://[IP-LAPTOP]:8000/upload          ║"
echo "║                                                      ║"
echo "╚══════════════════════════════════════════════════════╝"
echo -e "${NC}"

echo -e "${YELLOW}PENTING: Jangan lupa isi .env dengan:${NC}"
echo "  - Credentials MySQL (DB_DATABASE, DB_USERNAME, DB_PASSWORD)"
echo "  - Credentials Cloudinary (CLOUDINARY_URL, CLOUDINARY_CLOUD_NAME, dll)"
