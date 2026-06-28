# Project Files Manifest 📂

This document lists all the files created, used, or modified during the development of the Quran Memorization Backend project.

## Core Application Files

### Controllers (app/Http/Controllers)
- `AuthController.php`: Handles user registration, login, and token management.
- `SessionController.php`: Manages daily memorization sessions and review gating.
- `ProgressController.php`: Handles ayah memorization confirmation and profile updates.
- `QuizController.php`: Generates and handles quizzes with 60/40 ratio logic.
- `ReviewController.php`: Manages spaced repetition feedback and rescheduling.
- `StatsController.php`: Calculates user accuracy, streaks, and difficulty ranking.
- `RecitationController.php`: Generates dynamic MP3 URLs for ayah recitations.

### Models (app/Models)
- `User.php`: Extended with progress fields (streak, level, etc.).
- `Surah.php`: Represents Quranic chapters.
- `Ayah.php`: Represents Quranic verses with Uthmani text.
- `Reciter.php`: Stores information about audio servers and reciter names.
- `UserProgress.php`: tracks the status of each ayah (learning/memorized).
- `ReviewSchedule.php`: Core model for Spaced Repetition (SR) logic.
- `MemorizationSession.php`: Tracks daily sessions.
- `Question.php`: Stores different types of quiz questions.
- `UserAnswer.php`: Logs users' quiz performance.

### Routes (routes)
- `api.php`: Modified to include all project endpoints with Rate Limiting.

---

## Database & Data

### Migrations (database/migrations)
- `0001_01_01_000000_create_users_table.php`: Core user structure.
- `2026_01_04_135287_create_surahs_table.php`: Surah metadata.
- `2026_01_04_135288_create_ayahs_table.php`: Ayah text storage.
- `2026_01_04_135290_create_reciters_table.php`: Reciter server info.
- `2026_01_04_135300_create_memorization_sessions_table.php`: Daily session tracking.
- `2026_01_04_135301_create_user_progress_table.php`: Master progress table.
- `2026_01_04_135302_create_review_schedule_table.php`: SR logic schedule.
- `2026_01_04_135303_create_questions_table.php`: Quiz question definitions.
- `2026_01_04_135304_create_user_answers_table.php`: User performance logging.
- `2026_01_04_144215_add_profile_fields_to_users_table.php`: Progress fields.
- `2026_01_04_153544_add_interval_to_review_schedule_table.php`: SR interval support.

### Seeders (database/seeders)
- `DatabaseSeeder.php`: Main entry point for database seeding.
- `ReciterSeeder.php`: Default reciters list.
- `SurahAyahSeeder.php`: Automation for Quranic text import.

---

## Scripts & DevOps (scripts)
- `import_quran_sql.php`: Processes and imports Uthmani text from SQL.
- `import_reciters.php`: Fetches data from MP3Quran API.
- `backup.php`: Automated database backup script (MySQL/SQLite).
- `verify_import.php`: Checks database integrity after import.
- `verify_sr.php`: Validates Spaced Repetition logic.
- `verify_algo.php`: Tests 60/40 quiz generation.
- `verify_stats.php`: Verifies performance metrics calculation.
- `verify_audio.php`: Validates MP3 URL generation.

---

## Testing (tests)
- `tests/Unit/SpacedRepetitionTest.php`: SR algorithm tests.
- `tests/Feature/QuizAlgorithmTest.php`: 60/40 ratio tests.
- `tests/Feature/GatingTest.php`: Session gating and progression rules tests.

---

## Documentation
- `API_REFERENCE.md`: Comprehensive API guide.
- `Files.md`: This file manifest.
- `walkthrough.md`: Project summary and verification results.
