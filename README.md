# 🚀 Setup Guide

## Development Environment Setup

Follow these steps to set up and run the project locally:

### Prerequisites
- PHP 8.1 or higher
- Composer installed
- Database server (MySQL/PostgreSQL/SQLite)

### Installation Steps

1. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

2. **Configure environment variables**:
   - Set your database credentials in `.env`:
     ```ini
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=your_database_name
     DB_USERNAME=your_db_username
     DB_PASSWORD=your_db_password
     ```

3. **Install dependencies**:
   ```bash
   composer install
   ```

4. **Generate application key**:
   ```bash
   php artisan key:generate
   ```

5. **Run database migrations and seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server**:
   ```bash
   php artisan serve
   ```

The application will be available at: [http://localhost:8000](http://localhost:8000)
