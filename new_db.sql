CREATE DATABASE online_learning;
USE online_learning;

CREATE TABLE Instructor (
  instructor_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Student (
  student_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Course (
  course_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  instructor_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (instructor_id) REFERENCES Instructor(instructor_id)
);

CREATE TABLE Lesson (
  lesson_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  content TEXT,
  video_url VARCHAR(255),
  order_number INT,
  FOREIGN KEY (course_id) REFERENCES Course(course_id),
  material_path VARCHAR(255)
);

CREATE TABLE Enrollment (
  enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  enrollment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  completion_status VARCHAR(50),
  FOREIGN KEY (student_id) REFERENCES Student(student_id),
  FOREIGN KEY (course_id) REFERENCES Course(course_id)
);

CREATE TABLE Assessment (
  assessment_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  title VARCHAR(200) NOT NULL,
  type ENUM('Quiz', 'Project', 'Report') NOT NULL,
  due_date DATETIME,
  FOREIGN KEY (course_id) REFERENCES Course(course_id)
);

CREATE TABLE Submission (
  submission_id INT AUTO_INCREMENT PRIMARY KEY,
  assessment_id INT NOT NULL,
  student_id INT NOT NULL,
  submission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  grade ENUM('HD', 'D', 'C', 'P', 'F', 'N'),
  feedback TEXT,
  FOREIGN KEY (assessment_id) REFERENCES Assessment(assessment_id),
  FOREIGN KEY (student_id) REFERENCES Student(student_id),
  file_path VARCHAR(255)
);

CREATE TABLE Payment (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  course_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  invoice_number VARCHAR(50) UNIQUE NOT NULL,
  FOREIGN KEY (student_id) REFERENCES Student(student_id),
  FOREIGN KEY (course_id) REFERENCES Course(course_id)
);

CREATE TABLE Admin (
  admin_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ForumThread (
    thread_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_role ENUM('student', 'instructor', 'admin') NOT NULL,
    title VARCHAR(200) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    -- Optionally, add FOREIGN KEY (user_id) REFERENCES Student/Instructor/Admin
);

CREATE TABLE ForumPost (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    user_role ENUM('student', 'instructor', 'admin') NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES ForumThread(thread_id)
    -- Optionally, add FOREIGN KEY (user_id) REFERENCES Student/Instructor/Admin
);

CREATE TABLE PaymentCard (
    payment_card_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    card_last4 VARCHAR(4),
    card_encrypted TEXT,
    expiry_encrypted TEXT,
    name_encrypted TEXT,
    FOREIGN KEY (payment_id) REFERENCES Payment(payment_id)
);

CREATE TABLE LessonCompletion (
    lesson_completion_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES Student(student_id),
    FOREIGN KEY (lesson_id) REFERENCES Lesson(lesson_id)
);


INSERT INTO Instructor (name, email, password_hash) VALUES ('Alice Smith', 'alice@example.com', 'hashed_pw1');
INSERT INTO Instructor (name, email, password_hash) VALUES ('Bob Johnson', 'bob@example.com', 'hashed_pw2');
INSERT INTO Instructor (name, email, password_hash) VALUES ('Cathy Lee', 'cathy@example.com', 'hashed_pw3');
INSERT INTO Instructor (name, email, password_hash) VALUES ('David Kim', 'david@example.com', 'hashed_pw4');
INSERT INTO Instructor (name, email, password_hash) VALUES ('Eva Green', 'eva@example.com', 'hashed_pw5');
INSERT INTO Student (name, email, password_hash) VALUES ('John Doe', 'john@example.com', 'hashed_pw6');
INSERT INTO Student (name, email, password_hash) VALUES ('Jane Roe', 'jane@example.com', 'hashed_pw7');
INSERT INTO Student (name, email, password_hash) VALUES ('Mike Chan', 'mike@example.com', 'hashed_pw8');
INSERT INTO Student (name, email, password_hash) VALUES ('Lina Xu', 'lina@example.com', 'hashed_pw9');
INSERT INTO Student (name, email, password_hash) VALUES ('Carlos Diaz', 'carlos@example.com', 'hashed_pw10');
INSERT INTO Course (title, description, price, instructor_id) VALUES ('Intro to SQL', 'Learn SQL basics', 49.99, 1);
INSERT INTO Course (title, description, price, instructor_id) VALUES ('Web Dev with PHP', 'Fullstack with PHP', 79.99, 2);
INSERT INTO Course (title, description, price, instructor_id) VALUES ('Data Analysis', 'Work with real datasets', 99.99, 3);
INSERT INTO Course (title, description, price, instructor_id) VALUES ('Python Basics', 'Introductory Python', 59.99, 4);
INSERT INTO Course (title, description, price, instructor_id) VALUES ('Cybersecurity 101', 'Securing systems', 89.99, 5);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (1, 'Lesson 1', 'Content for Lesson 1', 'http://example.com/video1.mp4', 1);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (1, 'Lesson 2', 'Content for Lesson 2', 'http://example.com/video2.mp4', 2);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (1, 'Lesson 3', 'Content for Lesson 3', 'http://example.com/video3.mp4', 3);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (1, 'Lesson 4', 'Content for Lesson 4', 'http://example.com/video4.mp4', 4);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (1, 'Lesson 5', 'Content for Lesson 5', 'http://example.com/video5.mp4', 5);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (2, 'Lesson 1', 'Content for Lesson 1', 'http://example.com/video1.mp4', 1);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (2, 'Lesson 2', 'Content for Lesson 2', 'http://example.com/video2.mp4', 2);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (2, 'Lesson 3', 'Content for Lesson 3', 'http://example.com/video3.mp4', 3);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (2, 'Lesson 4', 'Content for Lesson 4', 'http://example.com/video4.mp4', 4);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (2, 'Lesson 5', 'Content for Lesson 5', 'http://example.com/video5.mp4', 5);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (3, 'Lesson 1', 'Content for Lesson 1', 'http://example.com/video1.mp4', 1);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (3, 'Lesson 2', 'Content for Lesson 2', 'http://example.com/video2.mp4', 2);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (3, 'Lesson 3', 'Content for Lesson 3', 'http://example.com/video3.mp4', 3);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (3, 'Lesson 4', 'Content for Lesson 4', 'http://example.com/video4.mp4', 4);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (3, 'Lesson 5', 'Content for Lesson 5', 'http://example.com/video5.mp4', 5);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (4, 'Lesson 1', 'Content for Lesson 1', 'http://example.com/video1.mp4', 1);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (4, 'Lesson 2', 'Content for Lesson 2', 'http://example.com/video2.mp4', 2);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (4, 'Lesson 3', 'Content for Lesson 3', 'http://example.com/video3.mp4', 3);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (4, 'Lesson 4', 'Content for Lesson 4', 'http://example.com/video4.mp4', 4);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (4, 'Lesson 5', 'Content for Lesson 5', 'http://example.com/video5.mp4', 5);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (5, 'Lesson 1', 'Content for Lesson 1', 'http://example.com/video1.mp4', 1);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (5, 'Lesson 2', 'Content for Lesson 2', 'http://example.com/video2.mp4', 2);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (5, 'Lesson 3', 'Content for Lesson 3', 'http://example.com/video3.mp4', 3);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (5, 'Lesson 4', 'Content for Lesson 4', 'http://example.com/video4.mp4', 4);
INSERT INTO Lesson (course_id, title, content, video_url, order_number) VALUES (5, 'Lesson 5', 'Content for Lesson 5', 'http://example.com/video5.mp4', 5);
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (1, 1, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (1, 2, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (1, 3, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (1, 4, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (1, 5, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (2, 1, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (2, 2, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (2, 3, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (2, 4, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (2, 5, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (3, 1, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (3, 2, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (3, 3, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (3, 4, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (3, 5, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (4, 1, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (4, 2, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (4, 3, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (4, 4, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (4, 5, 'Completed');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (5, 1, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (5, 2, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (5, 3, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (5, 4, 'In Progress');
INSERT INTO Enrollment (student_id, course_id, completion_status) VALUES (5, 5, 'In Progress');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (1, 'Assessment 1', 'Quiz', '2025-06-30');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (1, 'Assessment 2', 'Project', '2025-07-07');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (1, 'Assessment 3', 'Report', '2025-07-14');

INSERT INTO Assessment (course_id, title, type, due_date) VALUES (2, 'Assessment 1', 'Quiz', '2025-06-30');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (2, 'Assessment 2', 'Project', '2025-07-07');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (2, 'Assessment 3', 'Report', '2025-07-14');

INSERT INTO Assessment (course_id, title, type, due_date) VALUES (3, 'Assessment 1', 'Quiz', '2025-06-30');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (3, 'Assessment 2', 'Project', '2025-07-07');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (3, 'Assessment 3', 'Report', '2025-07-14');

INSERT INTO Assessment (course_id, title, type, due_date) VALUES (4, 'Assessment 1', 'Quiz', '2025-06-30');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (4, 'Assessment 2', 'Project', '2025-07-07');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (4, 'Assessment 3', 'Report', '2025-07-14');

INSERT INTO Assessment (course_id, title, type, due_date) VALUES (5, 'Assessment 1', 'Quiz', '2025-06-30');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (5, 'Assessment 2', 'Project', '2025-07-07');
INSERT INTO Assessment (course_id, title, type, due_date) VALUES (5, 'Assessment 3', 'Report', '2025-07-14');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (1, 1, 'C', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (1, 2, 'D', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (1, 3, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (1, 4, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (1, 5, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (2, 1, 'C', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (2, 2, 'D', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (2, 3, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (2, 4, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (2, 5, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (3, 1, 'C', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (3, 2, 'D', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (3, 3, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (3, 4, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (3, 5, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (4, 1, 'C', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (4, 2, 'D', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (4, 3, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (4, 4, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (4, 5, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (5, 1, 'C', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (5, 2, 'D', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (5, 3, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (5, 4, 'HD', 'Good job');
INSERT INTO Submission (assessment_id, student_id, grade, feedback) VALUES (5, 5, 'HD', 'Good job');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (1, 1, 59.99, 'INV11X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (1, 2, 69.99000000000001, 'INV12X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (1, 3, 79.99000000000001, 'INV13X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (1, 4, 89.99000000000001, 'INV14X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (1, 5, 99.99000000000001, 'INV15X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (2, 1, 59.99, 'INV21X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (2, 2, 69.99000000000001, 'INV22X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (2, 3, 79.99000000000001, 'INV23X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (2, 4, 89.99000000000001, 'INV24X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (2, 5, 99.99000000000001, 'INV25X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (3, 1, 59.99, 'INV31X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (3, 2, 69.99000000000001, 'INV32X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (3, 3, 79.99000000000001, 'INV33X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (3, 4, 89.99000000000001, 'INV34X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (3, 5, 99.99000000000001, 'INV35X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (4, 1, 59.99, 'INV41X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (4, 2, 69.99000000000001, 'INV42X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (4, 3, 79.99000000000001, 'INV43X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (4, 4, 89.99000000000001, 'INV44X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (4, 5, 99.99000000000001, 'INV45X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (5, 1, 59.99, 'INV51X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (5, 2, 69.99000000000001, 'INV52X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (5, 3, 79.99000000000001, 'INV53X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (5, 4, 89.99000000000001, 'INV54X');
INSERT INTO Payment (student_id, course_id, amount, invoice_number) VALUES (5, 5, 99.99000000000001, 'INV55X');
INSERT INTO `admin`(`admin_id`, `name`, `email`, `password_hash`, `created_at`) VALUES ('1','admin','admin@gmail.com','$2y$10$yr7XqkkBfLr1qSlUg5O/8ODTSqrnwEOu6Js7U.hocQe1wfHu9TbEK','[value-5]');