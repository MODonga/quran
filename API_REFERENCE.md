# Quran Memorization API Reference 📖

This document provides a detailed overview of all available endpoints in the Quran Memorization Backend.

## Authentication
All protected routes require a Bearer Token in the `Authorization` header.
`Authorization: Bearer <your_token>`

---

## 1. Auth & Profile
### Register
- **Endpoint**: `POST /api/register`
- **Body**: `name, email, password, password_confirmation`
- **Response**: `201 Created` with token.

### Login
- **Endpoint**: `POST /api/login`
- **Body**: `email, password`
- **Response**: `200 OK` with token.

### Get Profile
- **Endpoint**: `GET /api/profile` (Protected)
- **Response**: Returns current user details, `streak`, `total_days`, and `last_ayah_id`.

---

## 2. Memorization Flow
### Start Daily Session
- **Endpoint**: `POST /api/session/start` (Protected)
- **Response**:
  - `status: "review_pending"`: Must complete reviews first.
  - `status: "new_ayah_unlocked"`: Provides `next_ayah` details.

### Generate Quiz
- **Endpoint**: `GET /api/quiz/generate?limit=10` (Protected)
- **Parameters**: `limit` (default 10).
- **Behavior**: Returns a 60/40 mix of Old/New ayahs.

### Submit Quiz Answer
- **Endpoint**: `POST /api/quiz/submit` (Protected)
- **Body**: `question_id, answer`
- **Response**: Returns whether the answer was correct.

### Confirm Ayah Memorization
- **Endpoint**: `POST /api/ayah/confirm` (Protected)
- **Body**: `ayah_id, quiz_session_id`
- **Condition**: Requires >= 80% success in the related quiz session.

---

## 3. Spaced Repetition (SR)
### Submit Review Result
- **Endpoint**: `POST /api/review/submit` (Protected)
- **Body**: `ayah_id, is_correct (boolean)`
- **Behavior**: Reschedules the ayah based on the SR algorithm (1, 3, 7, 14, 30 days).

---

## 4. Audio & Stats
### Get Recitation URL
- **Endpoint**: `GET /api/recitation` (Protected)
- **Parameters**: `surah_id, ayah_number (optional), reciter_id (optional)`
- **Response**: Direct MP3 link (e.g., `https://.../001_005.mp3`).

### Get User Stats
- **Endpoint**: `GET /api/stats` (Protected)
- **Response**: `memorized_ayahs, streak, total_days, accuracy, most_failed_ayahs`.

---

## Rate Limiting
- All protected routes are limited to **60 requests per minute** per IP.
- Exceeding this limit returns `429 Too Many Requests`.
