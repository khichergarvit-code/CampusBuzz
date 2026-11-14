# 🎓 CampusBuzz

**Know what's happening on campus.**

A web platform where students can discover campus events, get notified about new ones, and register with one click, while the admin manages events and tracks registrations.

Built as a lab project using **PHP, MySQL, JavaScript, AJAX, HTML and CSS**.

---

## ✨ Features

### For Students
- **Sign up & log in** securely (passwords are hashed)
- **Browse events** as cards showing date, time, venue and seats left
- **Filter by category**: Tech, Cultural, Sports, Workshop
- **One-click registration with AJAX**: the button and seat count update instantly without reloading the page
- **Seat limit**: registration closes automatically when an event is full
- **Status badges**: Upcoming / Today / Completed, worked out from the event date
- **Event posters** on each card, with a coloured category placeholder when there's no image
- **🔔 Notifications** for events added in the last 3 days
- **My Events** page listing everything the student has registered for
- **Cancel registration** from My Events (the seat becomes free again)

### For Admin
- **Dashboard stats**: total events, students and registrations
- **Add new events** with a form, including an optional poster image link
- **Edit events**: change any detail (seats can't go below the number already registered)
- **Delete events** (their registrations are removed automatically)
- **View registrations**: see which students signed up for each event

---

## 🛠️ Tech Stack

| Layer | Technology | Used for |
|---|---|---|
| Frontend | HTML, CSS | Page structure and design (Grid, Flexbox, CSS variables, responsive layout) |
| Frontend logic | JavaScript | Register button, popup messages |
| Communication | AJAX (`fetch()` + JSON) | Registering without reloading the page |
| Backend | PHP | Login, sessions, form handling, database queries |
| Database | MySQL | Storing users, events and registrations |

---

## 🗄️ Database Design

Three tables:

- **users**: `id, name, email, password (hashed), role (student/admin), created_at`
- **events**: `id, title, description, category, venue, event_date, event_time, max_seats, image_url, created_at`
- **registrations**: `id, user_id → users.id, event_id → events.id, registered_at`

`registrations` links students and events (a **many-to-many** relationship).
A `UNIQUE (user_id, event_id)` rule stops duplicate registrations, and `ON DELETE CASCADE` removes registrations when an event is deleted.

**Seats left** is calculated, not stored: `max_seats − number of registrations`.

---

## 🔒 Security

- **Password hashing** with `password_hash()` / `password_verify()`
- **Prepared statements** on every query to prevent SQL injection
- **Sessions** for login, with **role checks** on every admin page
- Students can only cancel **their own** registrations (the query matches the logged-in user's id)
- Indian Standard Time set in both PHP and MySQL, so date checks are always correct
- **`htmlspecialchars()`** when displaying user input, to prevent script injection
- Server-side validation on all forms

---

## 📁 Project Structure

```
campus_events/
├── db.php                   # Database connection + timezone (included by every page)
├── index.php                # Redirects to login
├── signup.php               # Create a student account
├── login.php                # Log in (admin → admin panel, student → dashboard)
├── logout.php               # End the session
├── dashboard.php            # Event list, filters, notifications
├── register_event.php       # AJAX endpoint: registers a student, returns JSON
├── my_events.php            # Student's registered events (with Cancel button)
├── cancel_registration.php  # Student: cancel own registration
├── admin.php                # Admin dashboard: stats + event table
├── add_event.php            # Admin: add an event
├── edit_event.php           # Admin: edit an event
├── delete_event.php         # Admin: delete an event
├── view_registrations.php   # Admin: students registered for an event
├── script.js                # AJAX registration + popup messages
├── style.css                # All styling
└── database.sql             # Creates the database, tables and sample events
```

---

## 🚀 How to Run Locally

1. Install **MAMP** (macOS) or **XAMPP** (Windows) and start Apache + MySQL.
2. Copy this folder into `htdocs` and name it `campus_events`.
3. Open **phpMyAdmin**, go to the **Import** tab and import `database.sql`.
4. Check the settings in `db.php`:
   - **MAMP:** port `8889`, password `"root"` (default in this repo)
   - **XAMPP:** port `3306`, password `""` (empty)
5. Open the site:
   - **MAMP:** `http://localhost:8888/campus_events`
   - **XAMPP:** `http://localhost/campus_events`

### Already created the database before poster images were added?
Run this once in phpMyAdmin's SQL tab:
```sql
ALTER TABLE events ADD image_url VARCHAR(500) DEFAULT NULL;
```

### Making an admin account
Sign up normally, then in phpMyAdmin open the `users` table, edit that user and change `role` to `admin`.

---

## 👥 Team
Shivam Rawtani
Shivam Meena 
Garvit Khicher
Sardius Namchu
