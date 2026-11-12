-- =============================================
-- Campus Event Management - Database Setup
-- Run this in phpMyAdmin > SQL tab
-- =============================================

CREATE DATABASE IF NOT EXISTS campus_events;
USE campus_events;

-- 1. USERS: everyone who can log in (students + admin)
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('student', 'admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. EVENTS: every event the admin creates
CREATE TABLE events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    category    VARCHAR(50) NOT NULL,
    venue       VARCHAR(100) NOT NULL,
    event_date  DATE NOT NULL,
    event_time  TIME NOT NULL,
    max_seats   INT NOT NULL,
    image_url   VARCHAR(500) DEFAULT NULL,   -- link to the event poster (optional)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. REGISTRATIONS: connects a student to an event
CREATE TABLE registrations (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    event_id      INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE (user_id, event_id)   -- a student can't register twice for the same event
);

-- Sample events so the site isn't empty during testing
INSERT INTO events (title, description, category, venue, event_date, event_time, max_seats) VALUES
('Code Sprint Hackathon', '24-hour coding challenge. Teams of 2-4.', 'Tech', 'Computer Lab 3', '2026-11-20', '09:00:00', 50),
('Annual Cultural Night', 'Music, dance and drama performances.', 'Cultural', 'Main Auditorium', '2026-11-22', '18:00:00', 200),
('Inter-Department Cricket', 'T10 cricket tournament.', 'Sports', 'College Ground', '2026-11-25', '08:00:00', 30),
('Web Dev Workshop', 'Hands-on intro to building websites.', 'Workshop', 'Seminar Hall', '2026-11-18', '14:00:00', 40);
