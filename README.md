# Activity Tracker

A simple PHP-based activity/task tracking system with user authentication, activity assignment, and status management.

## Features

- User authentication (session-based)
- Create, read, update, and delete activities
- Assign activities to users
- Track activity status (pending, done, overdue)
- View activity statistics
- RESTful API endpoints (JSON responses)
- MySQL database backend

## Project Structure

```
activity.tracker/
├── api/
│   ├── create-activity.php
│   ├── update-activity.php
│   └── ... (other API endpoints)
├── config/
│   └── db.php
├── models/
│   └── Activity.php
├── public/
│   └── ... (frontend files, if any)
└── README.md
```

## Requirements

- PHP 7.4+
- MySQL
- Composer (optional, if you use dependencies)

## Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/activity.tracker.git
   cd activity.tracker
   ```

2. **Configure the database**
   - Create a MySQL database and user.
   - Import the provided SQL schema (if available).
   - Update `config/db.php` with your database credentials.

3. **Start your PHP server**
   ```bash
   php -S localhost:8000 -t public
   ```
   Or configure with Apache/Nginx as needed.

4. **Session Setup**
   - Make sure PHP sessions are enabled and writable.

## API Endpoints

### Create Activity

- **POST** `/api/create-activity.php`
- **Body (JSON):**
  ```json
  {
    "title": "Task title",
    "description": "Task details",
    "priority": "high",
    "assigned_to": 2,
    "due_date": "2025-07-10"
  }
  ```
- **Headers:**  
  `Content-Type: application/json`  
  (Requires user to be authenticated via session)

### Update Activity

- **POST** `/api/update-activity.php`
- **Body (JSON):**
  ```json
  {
    "activity_id": 1,
    "status": "done"
  }
  ```

### Other Endpoints

- `GET /api/list-activities.php` — List all activities
- `GET /api/activity-stats.php` — Get activity statistics

## Models

### Activity Model

Located at `models/Activity.php`.  
Handles all database operations for activities (create, read, update, delete, stats).

## Security

- All database queries use prepared statements.
- API endpoints require user authentication via PHP sessions.

## License

MIT

---

**Contributions and issues are welcome**