# PHP/MySQL Online Learning Platform (Updated Documentation)

## Overview

This project is a **PHP/MySQL-based online learning platform** supporting multiple user roles (Admin, Instructor, Student). It provides course management, enrollments, lessons, assessments, grades, payments, and a discussion forum. The system is designed for update-centric workflows, with a focus on usability and extensibility.

---

## File Structure

```
/
│── about.php                  # About Us page
│── assessments.php            # Manage/view assessments per course
│── be_assessments_create.php  # Backend: create new assessment
│── be_assessments_update.php  # Backend: update assessment type
│── be_auth.php                # Authentication/session helper functions
│── be_courses_create.php      # Backend: create new course
│── be_courses_update.php      # Backend: update course details
│── be_enrollments_update.php  # Backend: update/enroll/drop enrollments
│── be_grades_update.php       # Backend: update or insert grades
│── be_lesson_material_upload.php # Backend: upload lesson material
│── be_lessons_create.php      # Backend: create new lesson
│── be_login.php               # Backend: login processing
│── be_logoff.php              # Backend: logoff
│── be_payment.php             # Backend: process payment (demo)
│── be_submission_upload.php   # Backend: student uploads assessment submission
│── contact.php                # Contact Us page
│── courses.php                # Course management UI
│── db_connection.php          # Database connection settings
│── enrollments.php            # Enrollments management and student view
│── forum.php                  # Discussion forum (thread list & create)
│── grades.php                 # Grades management and student view
│── hashing.php                # Utility: password hash generator
│── inc_footer.php             # Footer include
│── inc_header_nav.php         # Header + navigation include
│── index.php                  # Login page
│── lessons.php                # Lessons management and student view
│── mark_lesson_complete.php   # Backend: mark lesson as completed
│── new_db.sql                 # Main database schema and seed data
│── new_db_adv_features.sql    # Advanced DB: triggers, procedures, functions
│── pay.php                    # Payment form and processing (demo)
│── payments.php               # Admin view: all payments
│── readme2.md                 # (This file) Updated documentation
│── registration.php           # User registration (student/instructor)
│── style.css                  # Main stylesheet
│── thread.php                 # Forum thread view and reply
```

---

## Setup Instructions

### 1. Import Database

Run the SQL script to set up the database:

```sh
mysql -u root -p < new_db.sql
```

For advanced features (triggers, stored procedures, functions):

```sh
mysql -u root -p < new_db_adv_features.sql
```

### 2. Configure Database Connection

Edit `db_connection.php` with your MySQL credentials:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "online_learning";
$conn = new mysqli($host, $user, $password, $database);
```

### 3. Run the System

Start a local PHP server:

```sh
php -S localhost:8000
```

Open [http://localhost:8000/index.php](http://localhost:8000/index.php) in your browser.

---

## Features

### User Roles

- **Admin:** Full management of courses, lessons, assessments, enrollments, grades, payments, and forum.
- **Instructor:** Manage own courses, lessons, assessments, grades, and participate in forum.
- **Student:** Enroll in courses, view lessons, submit assessments, view grades, mark lesson progress, participate in forum.

### Authentication

- Secure login with password hashing (`password_hash`/`password_verify`).
- Role-based access control for all backend actions.

### Course Management

- Admins and instructors can create, edit, and assign instructors to courses.
- Course fees and instructor assignment supported.

### Enrollment

- Students can enroll in available courses and drop courses (if no grades assigned).
- Admins can update enrollment status (`pending`, `finalized`).

### Lessons

- Lessons are managed per course.
- Admins/instructors can add, edit, and upload materials for lessons.
- Students can view lessons and mark them as completed.
- **Progress bar** shows student lesson completion per course.

### Assessments

- Admins/instructors can add, edit, and view assessments (Quiz, Project, Report).
- Students can upload assignment files for assessments.

### Grades

- Admins/instructors can assign/update grades for each student and assessment.
- Students can view their grades per course.
- Grade legend and color coding included.

### Payments

- Students pay for courses via a simulated payment form (with card encryption for demo).
- Admins can view all payments.
- Payment status is shown in enrollments.

### Forum

- Discussion forum with threads and posts.
- All users can post; admins can delete any post or thread.

### Registration

- Students and instructors can self-register.
- Admins are seeded in the database.

---

## Database Design

- **Relational schema** with foreign keys for integrity.
- **LessonCompletion** table tracks which student completed which lesson.
- **Submission** table tracks assessment submissions and grades.
- **Advanced features**: triggers and stored procedures for enrollment, grade updates, and instructor assignment.

---

## Advanced Features

- **Triggers:** Auto-update enrollment status when a grade is assigned.
- **Stored Procedures:** For enrolling/dropping students, assigning instructors.
- **Stored Functions:** For counting submissions per assessment.

---

## CSS Styling

- All styles in `style.css` using CSS variables for easy color/theme changes.
- Custom progress bar for lesson completion.
- Responsive design for mobile/tablet.

---

## Security & Best Practices

- All forms use HTML5 validation and server-side checks.
- Passwords are hashed.
- Role checks on all backend actions.
- File uploads are stored with unique names and validated.
- No raw SQL in user-facing code (uses prepared statements).

---

## Simplifications

- No delete operations for most entities (except forum posts/threads by admin).
- Payments are simulated (no real payment gateway).
- Only three assessment types.
- All user management (except registration) is via the database or admin.

---

## How to Extend

- Add more assessment types or lesson content types.
- Integrate a real payment gateway (Stripe, PayPal).
- Add dashboards for analytics.
- Add notifications or messaging.

---

## Credits

Developed for ICT214 Advanced Database Assignment 2.

---

## License

MIT License or as specified by your course/assignment.
