# LokalPH – Philippine small business finder

Vue 3 + Vite + Tailwind + Vue Router + Leaflet (frontend) · PHP 8 MVC REST API · MySQL · PHP sessions.

## Structure
```
lokalph/
├── app/
│   ├── Controllers/   Auth, Public, Owner, Admin
│   ├── Models/        Business, Product (Haversine search)
│   ├── Middleware/    Auth (role checks)
│   ├── Services/      ImageUpload (validated uploads)
│   ├── Core/          Core.php (PDO, router, helpers)
│   └── Views/         app.php (SPA shell)
├── config/            config.php (DB credentials)
├── database/
│   ├── migrations/    001_schema.sql
│   └── seeds/         seed.php (sample data + demo logins)
├── public/            index.php, .htaccess, assets/ (built), uploads/
├── resources/
│   ├── css/           app.css (Tailwind)
│   └── js/
│       ├── main.js, App.vue, router.js, store.js, api.js, utils.js
│       ├── lib/       leaflet.js
│       ├── components/  AppHeader, BottomNav, BusinessCard, ProductCard, MapView, PinMap, modals
│       └── views/       Home, Discover, Products, Business, Auth, Account, owner/*, admin/*
├── routes/api.php     REST routes
├── storage/           logs
├── package.json, vite.config.js, tailwind.config.js, postcss.config.js
└── .htaccess
```

## Setup (XAMPP)
1. Start MySQL. Import `database/migrations/001_schema.sql`.
2. Edit `config/config.php` if needed.
3. `C:\xampp\php\php.exe database\seeds\seed.php`
4. `C:\xampp\php\php.exe -S localhost:8000 -t public public/index.php` → open http://localhost:8000

The built frontend is already in `public/assets`. To change it: `npm install`, then `npm run dev` (hot reload on :5173, proxies /api to :8000) or `npm run build`.
For Apache, point DocumentRoot at `public/`; the app must run at the web root.

