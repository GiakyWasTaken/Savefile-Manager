# Savefile Manager

**Savefile Manager** is a RESTful API designed to handle savefiles from a variety of gaming consoles. Developed from scratch in under a month during an internship in Maribor, Slovenia — with zero prior Laravel experience — this project showcases fast learning and practical implementation of a modern PHP backend stack.

## 🚀 Features

* **Savefile Management**: Upload, update, retrieve, and delete game savefiles.
* **Console Integration**: Link savefiles to specific gaming consoles.
* **Backup System**: Automatically back up savefiles on every update.
* **Authentication**: OAuth2-secured endpoints via Laravel Passport.
* **Database Handling**: Efficient Eloquent-based operations for both savefiles and consoles.
* **Logging**: Full operational logging for traceability and debugging.
* **Health Check Endpoint**: Dedicated endpoint to monitor system health (server, DB, filesystem).

## 💠 Tech Stack

* **Laravel**: PHP web framework.
* **MySQL**: Relational database for persistent data.
* **REST API**: JSON-based API for easy client integration.
* **Faker**: Mock data generation for testing and development.
* **Passport**: Laravel OAuth2 authentication for secure access.

## ⚙️ Getting Started

### 📋 Prerequisites

* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)

### 🛠 Installation Steps

1. **Clone the repo**

   ```bash
   git clone https://github.com/GiakyWasTaken/Savefile-Manager
   cd Savefile-Manager
   ```

2. **Copy and configure `.env`**

   ```bash
   cp .env.example .env
   ```

   * Then edit `.env` as needed (especially DB credentials).

3. **Start Laravel Sail**

   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Install dependencies**

   ```bash
   ./vendor/bin/sail composer install
   ```

5. **Run database migrations and seeders**

   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```

6. **Serve the app**

   ```bash
   ./vendor/bin/sail artisan serve
   ```

---

## 🔌 API Reference

### 🔐 Authentication

* `POST /api/register` — Register new user
* `POST /api/login` — Login and receive access token
* `GET /api/logout` — Logout the current user

### 💾 Savefile Management

* `GET /api/savefile` — List all savefiles
* `GET /api/savefile/{id}` — Get a single savefile
* `POST /api/savefile` — Upload a new savefile
* `PUT /api/savefile/{id}` — Update an existing savefile
* `DELETE /api/savefile/{id}` — Delete a savefile

### 🎮 Console Management

* `GET /api/console` — List all consoles
* `GET /api/console/{id}` — Get a specific console
* `POST /api/console` — Add a new console
* `PUT /api/console/{id}` — Update console info
* `DELETE /api/console/{id}` — Remove a console

### 🩺 Health Check

* `GET /api/health` — Returns server, database, and filesystem status, plus current timestamp and overall health.

This endpoint logs all health check calls and gracefully handles DB and storage issues. Returns HTTP 200 if everything is fine, 500 otherwise.

---

## 🧪 Client Scripts

Looking for scripts to interact with this API? Check out the companion repo:

🔗 [Savefile Manager Scripts](https://github.com/GiakyWasTaken/Savefile-Manager-Scripts)

---
