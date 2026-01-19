.PHONY: start start-back start-front stop help

# Default target
.DEFAULT_GOAL := help

# Colors
GREEN := \033[0;32m
NC := \033[0m # No Color

## Show help
help:
	@echo "${GREEN}Available commands:${NC}"
	@echo "  make install      - Install dependencies, setup database & fixtures"
	@echo "  make start        - Start both backend (API) and frontend (clients) in parallel"
	@echo "  make start-back   - Start only the backend (Symfony API)"
	@echo "  make start-front  - Start only the frontend (Vue.js)"
	@echo "  make stop         - Stop all running services"

## Install project (dependencies + db)
install:
	@echo "${GREEN}🚀 Installing Project...${NC}"
	@$(MAKE) install-back
	@$(MAKE) install-front
	@echo "${GREEN}✅ Installation complete! Run 'make start' to launch.${NC}"

## Install Backend
install-back:
	@echo "${GREEN}🐘 Installing Backend (Composer, DB, Fixtures)...${NC}"
	cd backend && php composer.phar install
	cd backend && php bin/console lexik:jwt:generate-keypair --skip-if-exists
	cd backend && php bin/console doctrine:database:create --if-not-exists
	cd backend && php bin/console doctrine:migrations:migrate --no-interaction
	cd backend && php bin/console doctrine:fixtures:load --no-interaction

## Install Frontend
install-front:
	@echo "${GREEN}⚡ Installing Frontend (NPM)...${NC}"
	cd frontend && npm install

## Start both
start:
	@echo "${GREEN}🚀 Starting Project (API + Frontend)...${NC}"
	@$(MAKE) -j 2 start-back start-front

## Start Backend
start-back:
	@echo "${GREEN}🐘 Starting Symfony API on http://127.0.0.1:8001${NC}"
	cd backend && php -S 127.0.0.1:8001 -t public

## Start Frontend
start-front:
	@echo "${GREEN}⚡ Starting Vue.js Frontend on http://localhost:9999${NC}"
	cd frontend && npm run dev

## Stop services
stop:
	@echo "${GREEN}🛑 Stopping services...${NC}"
	@lsof -ti:8001 | xargs kill -9 2>/dev/null || true
	@lsof -ti:9999 | xargs kill -9 2>/dev/null || true
	@echo "Services stopped."
