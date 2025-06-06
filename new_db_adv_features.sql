
-- ============================================
-- Trigger: Auto-update enrollment status when grade is updated
-- ============================================
DELIMITER $$

DROP TRIGGER IF EXISTS trg_update_enrollment_status$$

CREATE TRIGGER trg_update_enrollment_status
AFTER UPDATE ON Submission
FOR EACH ROW
BEGIN
  IF NEW.grade IS NOT NULL THEN
    UPDATE Enrollment
    SET completion_status = 'Completed'
    WHERE student_id = NEW.student_id AND course_id = (
      SELECT course_id FROM Assessment WHERE assessment_id = NEW.assessment_id
    );
  END IF;
END$$

DELIMITER ;

-- AFTER INSERT trigger
DELIMITER $$

DROP TRIGGER IF EXISTS trg_insert_enrollment_status$$

CREATE TRIGGER trg_insert_enrollment_status
AFTER INSERT ON Submission
FOR EACH ROW
BEGIN
  IF NEW.grade IS NOT NULL THEN
    UPDATE Enrollment
    SET completion_status = 'Completed'
    WHERE student_id = NEW.student_id AND course_id = (
      SELECT course_id FROM Assessment WHERE assessment_id = NEW.assessment_id
    );
  END IF;
END$$

DELIMITER ;

-- ============================================
-- Stored Procedure: Enroll a student into a course
-- ============================================
DELIMITER $$

DROP PROCEDURE IF EXISTS enroll_student$$

CREATE PROCEDURE enroll_student(
  IN p_student_id INT,
  IN p_course_id INT
)
BEGIN
  DECLARE already_enrolled INT;

  SELECT COUNT(*) INTO already_enrolled
  FROM Enrollment
  WHERE student_id = p_student_id AND course_id = p_course_id;

  IF already_enrolled = 0 THEN
    INSERT INTO Enrollment (student_id, course_id, completion_status)
    VALUES (p_student_id, p_course_id, 'In Progress');
  ELSE
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Student already enrolled in this course';
  END IF;
END$$

DELIMITER ;

-- ============================================
-- Stored Procedure: Assign instructor to course
-- ============================================
DELIMITER $$

DROP PROCEDURE IF EXISTS assign_instructor$$

CREATE PROCEDURE assign_instructor(
  IN p_course_id INT,
  IN p_instructor_id INT
)
BEGIN
  UPDATE Course
  SET instructor_id = p_instructor_id
  WHERE course_id = p_course_id;
END$$

DELIMITER ;


-- Stored Function: Count Submission for Assessment

DELIMITER $$
CREATE FUNCTION count_submissions(p_assessment_id INT)
RETURNS INT
DETERMINISTIC
BEGIN
  DECLARE cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO cnt
  FROM Submission
  WHERE assessment_id = p_assessment_id
    AND file_path IS NOT NULL
    AND file_path <> '';
  RETURN cnt;
END$$
DELIMITER ;

-- Stored Procedure: Drop Student from Course, only if they have not receive any grades yet.

DELIMITER $$
CREATE PROCEDURE drop_student_from_course(
  IN p_student_id INT,
  IN p_course_id INT
)
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM Submission s
    JOIN Assessment a ON s.assessment_id = a.assessment_id
    WHERE s.student_id = p_student_id AND a.course_id = p_course_id AND s.grade IS NOT NULL
  ) THEN
    DELETE FROM Enrollment WHERE student_id = p_student_id AND course_id = p_course_id;
  ELSE
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot drop: grades already assigned.';
  END IF;
END$$
DELIMITER ;