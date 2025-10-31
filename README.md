# Gigflow (Formerly KwikEvent) Event Booking System

## Author
**Keith Hards**  
[keithhards@outlook.com](mailto:keithhards@outlook.com)  
(c) 2020 - 2025

---
Last update March 2022
---

## Project Overview

This project is a work in progress developed as a training ground and to test out new technologies such as Livewire.

- Many older files in `App/Booking` need refactoring into appropriate folders (for example, `App/Booking/Database` should be moved).  
  New files are located in `App/Domain`.
- There are feature/integration tests in `test/Feature`. These provide good coverage, but could benefit from refactoring.
- The current focus is on Payments. Once finished, the next step is refactoring before extracting `OrderWidget` into a WordPress plugin for bookings on [keithhards.co.uk](https://keithhards.co.uk).

---

## Project Goals

- Replace the WordPress plugin currently in use on [keithhards.co.uk](https://keithhards.co.uk), which provides backend booking management and a user booking form.
- Provide a multi-user and multi-business booking management system.
- Offer a free booking system for the DJ community, especially for those without a website (just Facebook pages).
- Enable easy calendar integration and scheduling around part-time DJs' work and holiday schedules.
- Allow anyone to take bookings:
  - DJs and event providers can sign up, manage calendars and bookings, and handle phone enquiries.

---

## Phase 2 - Search for a DJ

A single, simple website for finding entertainers available on a specified date and time in your area. This will allow anyone to book a DJ online with instant pricing.

---

## Development Environment Setup

> **TODO:** Dockerise

- Ubuntu 22.04
- php8.1
- php-mysql
- php-mbstring
- php-sqlite3
- php-curl
- php-gd
- php-xml
- php-zip
- php-intl
- composer

### Backend Installation

```bash
git clone https://github.com/khards/gigflow.git
git checkout main
composer install
php artisan passport:keys
```

### Frontend Build (Ubuntu)

```bash
sudo apt-get install npm
npm i
npm run dev
```

### Run Unit Tests

```bash
phpunit
```

### WSL Debug Setup

```bash
export PHP_IDE_CONFIG="serverName=wsl"
```

---

## API Setup for Frontend JS (resources/js/Widget/Order.vue)

1. **Create user token**
   ```bash
   php artisan passport:install
   ```
   (Record the client ID/secret output)
2. **Create API access token for an admin user**
   ```php
   use App\Domains\Auth\Models\User;
   User::where('type', 'admin')->first()->createToken('API Client Test')->accessToken;
   ```
   (Use the access token output for API requests)

---

## Deployment

```bash
rsync -crahvP --exclude='.git' --exclude='.idea' --exclude="vendor" --exclude="node_modules" --exclude="storage" --exclude="build" . my-server.net:/var/www/gigflow
```

---

## Cron Setup

Edit your crontab (`crontab -e`) and add:

```bash
* * * * * cd /home/khards/web/elitebookingsystem.com/public_html/gigflow && php artisan schedule:run >> /dev/null 2>&1
```

---

## Supervisor Setup

(Configure Supervisor to run jobs as required)

---

## Public Folders Setup

### File Manager

```bash
mkdir public/storage
cd public/storage
ln -s ../../storage/app/public/files
ln -s ../../storage/app/public/products/
```

### Product Images

```bash
cd public
mkdir products
cd products
ln -s ../../storage/app/public/products
```
