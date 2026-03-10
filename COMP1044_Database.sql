-- create table
CREATE TABLE Users(
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,    
    password VARCHAR(255) NOT NULL,          
    role ENUM('Admin', 'Assessor') NOT NULL
);

CREATE TABLE Students (
    student_id VARCHAR(20) PRIMARY KEY,      
    student_name VARCHAR(100) NOT NULL,      
    programme VARCHAR(100) NOT NULL          
);

CREATE TABLE Internships (
    internship_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL,
    assessor_id INT NOT NULL,
    company_name VARCHAR(150) NOT NULL,      
    other_details TEXT,                      
    FOREIGN KEY (student_id) REFERENCES Students(student_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (assessor_id) REFERENCES Users(user_id) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- DECIMAL(5,2),tow decimal places for scores
CREATE TABLE Assessments (
    assessment_id INT AUTO_INCREMENT PRIMARY KEY,
    internship_id INT NOT NULL UNIQUE,       
    task_score DECIMAL(5,2) NOT NULL,             
    health_safety_score DECIMAL(5,2) NOT NULL, 
    connectivity_score DECIMAL(5,2) NOT NULL,
    report_score DECIMAL(5,2) NOT NULL,      
    clarity_score DECIMAL(5,2) NOT NULL,     
    lifelong_score DECIMAL(5,2) NOT NULL,    
    project_mgmt_score DECIMAL(5,2) NOT NULL,
    time_mgmt_score DECIMAL(5,2) NOT NULL,   
    total_score DECIMAL(5,2) NOT NULL,       
    qualitative_comments TEXT NOT NULL,      
    FOREIGN KEY (internship_id) REFERENCES Internships(internship_id) ON DELETE CASCADE ON UPDATE CASCADE
);



-- insert value
-- 1. Insert user data (1 administrator, 2 evaluators)
-- At this point, the auto-increment primary key user_id is automatically assigned: Admin=1, Dr.Smith=2, Prof.Jones=3
INSERT INTO Users (username, password, role) 
VALUES 
('admin_main', 'hashed_pwd_001', 'Admin'),
('Dr_smith', 'hashed_pwd_002', 'Assessor'),
('Prof_jones', 'hashed_pwd_003', 'Assessor');

-- 2.insert students values (5 records)
INSERT INTO Students (student_id, student_name, programme) 
VALUES 
('S2024001', 'Alice Wong', 'Computer Science'),
('S2024002', 'Bob Chen', 'Software Engineering'),
('S2024003', 'Charlie Davis', 'Information Technology'),
('S2024004', 'Diana Lim', 'Computer Science'),
('S2024005', 'Evan Taylor', 'Data Science');

-- 3. Insert internship assignment data (5 records)
-- Assign Alice, Bob, Charlie to Dr. Smith (assessor_id = 2)
-- Assign Diana, Evan to Prof. Jones (assessor_id = 3)
-- At this point, the auto-increment primary key internship_id will be automatically assigned from 1 to 5
INSERT INTO Internships (student_id, assessor_id, company_name, other_details) 
VALUES 
('S2024001', 2, 'Google Malaysia', 'Backend Intern, 12 weeks'),
('S2024002', 2, 'Shopee', 'Frontend Intern, 12 weeks'),
('S2024003', 2, 'Intel', 'System Analyst Intern, 10 weeks'),
('S2024004', 3, 'Microsoft', 'Cloud Architect Intern, 12 weeks'),
('S2024005', 3, 'Grab', 'Data Analyst Intern, 10 weeks');

-- 4. Insert evaluation score data (insert only 3 records, leaving 2 empty)
-- Corresponding to internship_id 1 (Alice), 2 (Bob), 4 (Diana)
-- leaving internship_id 3 (Charlie) and 5 (Evan) ungraded for use in video demonstrations

INSERT INTO Assessments (
    internship_id, task_score, health_safety_score, connectivity_score, 
    report_score, clarity_score, lifelong_score, project_mgmt_score, 
    time_mgmt_score, total_score, qualitative_comments
) 

VALUES 
-- Alice score (total 92.5) - excellent
(1, 9.0, 9.5, 9.0, 14.0, 9.0, 14.0, 14.0, 14.0, 92.5, 'Alice demonstrated outstanding technical skills and adapted perfectly to the company culture. Highly recommended.'),

-- Bob score (total 74.0) - satisfactory
(2, 7.0, 8.0, 7.5, 11.0, 7.0, 10.5, 12.0, 11.0, 74.0, 'Bob completed the tasks adequately, but needs to improve his communication skills and time management.'),

-- Diana score (total 84.5) - good
(4, 8.5, 9.0, 8.0, 13.0, 8.5, 12.0, 13.0, 12.5, 84.5, 'Diana is a fast learner and contributed well to the cloud architecture project. Good overall performance.');