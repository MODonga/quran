# Quran Memorization Backend 📖🕋

A robust Laravel-based backend for a Quran memorization and review application. This system implements advanced Spaced Repetition (SR) algorithms, strict progression rules, and daily session management to ensure an effective and disciplined learning experience.

## ✨ Features
- **Daily Sessions**: Mandatory review sessions before unlocking new memorization.
- **Spaced Repetition (SR)**: Intelligent scheduling with intervals of 1, 3, 7, 14, and 30 days.
- **Strict Quiz Ethics**: 60/40 ratio of Review vs. New ayahs to maintain retention.
- **Progress Tracking**: Real-time streaks, accuracy rates, and difficulty analysis (Top 10 most failed ayahs).
- **Audio Integration**: Dynamic MP3 link generation for 234+ reciters (Zero storage cost).
- **Security**: Built-in Rate Limiting and Brute Force protection.

## 🛠 Tech Stack
- **Framework**: Laravel 12.x
- **Authentication**: Laravel Sanctum (Token-based)
- **Database**: MySQL / SQLite support
- **Testing**: PHPUnit (Feature & Unit tests)

## 📄 Documentation
- **[Setup Guide](SETUP.md)**: How to get the project running locally.
- **[API Reference](API_REFERENCE.md)**: Detailed API endpoints and schemas.
- **[File Manifest](Files.md)**: Overview of the project structure.

## 🚀 Getting Started
To get the project up and running in minutes, follow the **[Setup Guide](SETUP.md)**.

```bash
# Quick Start
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---
Developed with ❤️ for Quran Memorization.
