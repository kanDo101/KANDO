-- Seed data for the KANDO project database
-- This file contains sample data for all tables: users, projects, tasks, appartenir, and messages

-- Clear existing data (if any)
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM message;
DELETE FROM task;
DELETE FROM appartenir;
DELETE FROM project;
DELETE FROM user;
ALTER TABLE user AUTO_INCREMENT = 1;
ALTER TABLE project AUTO_INCREMENT = 1;
ALTER TABLE task AUTO_INCREMENT = 1;
ALTER TABLE message AUTO_INCREMENT = 1;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert sample users
INSERT INTO user (id, fullName, username, password, profession, email, photo, created_at) VALUES
(1, 'John Doe', 'johndoe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Project Manager', 'john@example.com', 'https://randomuser.me/api/portraits/men/1.jpg', '2023-01-15 10:00:00'),
(2, 'Jane Smith', 'janesmith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Developer', 'jane@example.com', 'https://randomuser.me/api/portraits/women/2.jpg', '2023-01-16 11:30:00'),
(3, 'Michael Johnson', 'michaelj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Designer', 'michael@example.com', 'https://randomuser.me/api/portraits/men/3.jpg', '2023-01-17 09:15:00'),
(4, 'Emily Davis', 'emilyd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'QA Engineer', 'emily@example.com', 'https://randomuser.me/api/portraits/women/4.jpg', '2023-01-18 14:20:00'),
(5, 'Robert Wilson', 'robertw', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Product Owner', 'robert@example.com', 'https://randomuser.me/api/portraits/men/5.jpg', '2023-01-19 16:45:00');

-- Insert sample projects
INSERT INTO project (id, name, description, userId, created_at, dueDate) VALUES
(1, 'Website Redesign', 'Complete overhaul of the company website with modern design and improved UX', 1, '2023-02-01 09:00:00', '2023-05-30'),
(2, 'Mobile App Development', 'Create a new mobile application for both iOS and Android platforms', 2, '2023-02-10 10:30:00', '2023-06-15'),
(3, 'Database Migration', 'Migrate the existing database to a new cloud-based solution', 3, '2023-02-15 11:45:00', '2023-04-30'),
(4, 'Marketing Campaign', 'Plan and execute Q2 marketing campaign across all channels', 4, '2023-02-20 13:15:00', '2023-07-31'),
(5, 'Product Launch', 'Prepare and coordinate the launch of our new flagship product', 5, '2023-02-25 15:30:00', '2023-08-15');

-- Insert project memberships (appartenir)
INSERT INTO appartenir (userId, projectId) VALUES
-- Project 1 members
(1, 1), -- Owner
(2, 1),
(3, 1),
-- Project 2 members
(2, 2), -- Owner
(3, 2),
(4, 2),
-- Project 3 members
(3, 3), -- Owner
(1, 3),
(5, 3),
-- Project 4 members
(4, 4), -- Owner
(1, 4),
(5, 4),
-- Project 5 members
(5, 5), -- Owner
(2, 5),
(4, 5);

-- Insert tasks for Project 1: Website Redesign
INSERT INTO task (id, name, description, state, userId, projectId, assigned_to, created_at) VALUES
(1, 'Wireframe Design', 'Create wireframes for all main pages of the website', 'Completed', 1, 1, 3, '2023-02-02 10:00:00'),
(2, 'Content Audit', 'Review and catalog all existing website content', 'Completed', 1, 1, 2, '2023-02-03 11:30:00'),
(3, 'Frontend Development', 'Implement the new design using HTML, CSS, and JavaScript', 'inprogress', 1, 1, 2, '2023-02-05 09:15:00'),
(4, 'Backend Integration', 'Connect the frontend to the backend systems', 'Pending', 1, 1, 2, '2023-02-07 14:20:00'),
(5, 'User Testing', 'Conduct user testing sessions with the new design', 'Pending', 1, 1, 1, '2023-02-09 16:45:00');

-- Insert tasks for Project 2: Mobile App Development
INSERT INTO task (id, name, description, state, userId, projectId, assigned_to, created_at) VALUES
(6, 'App Architecture', 'Design the overall architecture of the mobile application', 'Completed', 2, 2, 2, '2023-02-11 10:00:00'),
(7, 'UI Design', 'Create UI designs for all app screens', 'Completed', 2, 2, 3, '2023-02-12 11:30:00'),
(8, 'iOS Development', 'Develop the iOS version of the application', 'inprogress', 2, 2, 2, '2023-02-14 09:15:00'),
(9, 'Android Development', 'Develop the Android version of the application', 'Pending', 2, 2, 4, '2023-02-16 14:20:00'),
(10, 'API Integration', 'Integrate the app with backend APIs', 'Pending', 2, 2, 2, '2023-02-18 16:45:00');

-- Insert tasks for Project 3: Database Migration
INSERT INTO task (id, name, description, state, userId, projectId, assigned_to, created_at) VALUES
(11, 'Data Audit', 'Audit existing database structure and data', 'Completed', 3, 3, 3, '2023-02-16 10:00:00'),
(12, 'Migration Plan', 'Create a detailed migration plan and timeline', 'Completed', 3, 3, 1, '2023-02-17 11:30:00'),
(13, 'Schema Conversion', 'Convert the existing schema to the new database format', 'inprogress', 3, 3, 3, '2023-02-19 09:15:00'),
(14, 'Data Transfer', 'Transfer data from old to new database', 'Pending', 3, 3, 5, '2023-02-21 14:20:00'),
(15, 'Validation & Testing', 'Validate and test the migrated database', 'Pending', 3, 3, 1, '2023-02-23 16:45:00');

-- Insert tasks for Project 4: Marketing Campaign
INSERT INTO task (id, name, description, state, userId, projectId, assigned_to, created_at) VALUES
(16, 'Campaign Strategy', 'Develop overall marketing campaign strategy', 'Completed', 4, 4, 4, '2023-02-21 10:00:00'),
(17, 'Content Creation', 'Create content for all marketing channels', 'inprogress', 4, 4, 1, '2023-02-22 11:30:00'),
(18, 'Social Media Plan', 'Plan social media posts and schedule', 'inprogress', 4, 4, 4, '2023-02-24 09:15:00'),
(19, 'Email Marketing', 'Design and set up email marketing sequences', 'Pending', 4, 4, 5, '2023-02-26 14:20:00'),
(20, 'Analytics Setup', 'Set up analytics to track campaign performance', 'Pending', 4, 4, 4, '2023-02-28 16:45:00');

-- Insert tasks for Project 5: Product Launch
INSERT INTO task (id, name, description, state, userId, projectId, assigned_to, created_at) VALUES
(21, 'Launch Plan', 'Create detailed product launch plan', 'Completed', 5, 5, 5, '2023-02-26 10:00:00'),
(22, 'Press Kit', 'Prepare press kit and media materials', 'inprogress', 5, 5, 2, '2023-02-27 11:30:00'),
(23, 'Event Planning', 'Plan launch event details and logistics', 'inprogress', 5, 5, 4, '2023-03-01 09:15:00'),
(24, 'Customer Communication', 'Prepare communication for existing customers', 'Pending', 5, 5, 5, '2023-03-03 14:20:00'),
(25, 'Sales Training', 'Conduct training sessions for the sales team', 'Pending', 5, 5, 5, '2023-03-05 16:45:00');

-- Insert messages for Project 1
INSERT INTO message (projectId, senderId, content, is_read, created_at) VALUES
(1, 1, 'Welcome to the Website Redesign project! Let''s make this website amazing.', 1, '2023-02-01 09:30:00'),
(1, 2, 'Thanks for adding me to the team. I''ve already started reviewing the current site.', 1, '2023-02-01 10:15:00'),
(1, 3, 'I''ll have the wireframes ready by the end of the week.', 1, '2023-02-01 11:00:00'),
(1, 1, 'Great! Looking forward to seeing them.', 1, '2023-02-01 11:30:00'),
(1, 2, 'Should we schedule a weekly meeting to discuss progress?', 0, '2023-02-01 14:45:00');

-- Insert messages for Project 2
INSERT INTO message (projectId, senderId, content, is_read, created_at) VALUES
(2, 2, 'Mobile App Development project is now set up. Let''s get started!', 1, '2023-02-10 11:00:00'),
(2, 3, 'I''ll start working on the UI designs tomorrow.', 1, '2023-02-10 13:20:00'),
(2, 4, 'Do we have the API documentation ready?', 1, '2023-02-11 09:10:00'),
(2, 2, 'Yes, I''ll share it with everyone today.', 1, '2023-02-11 10:30:00'),
(2, 3, 'Just uploaded the first batch of UI designs for review.', 0, '2023-02-12 16:15:00');

-- Insert messages for Project 3
INSERT INTO message (projectId, senderId, content, is_read, created_at) VALUES
(3, 3, 'Database Migration project is now active. This is a critical project for our infrastructure.', 1, '2023-02-15 12:00:00'),
(3, 1, 'I''ve prepared a preliminary timeline. Let''s review it tomorrow.', 1, '2023-02-15 14:30:00'),
(3, 5, 'Do we have a backup strategy in place before we start?', 1, '2023-02-16 09:45:00'),
(3, 3, 'Yes, we''ll create full backups before any migration steps.', 1, '2023-02-16 10:20:00'),
(3, 1, 'I''ve scheduled a meeting with the infrastructure team for next Monday.', 0, '2023-02-17 15:00:00');

-- Insert messages for Project 4
INSERT INTO message (projectId, senderId, content, is_read, created_at) VALUES
(4, 4, 'Marketing Campaign project is ready. Q2 is going to be our biggest quarter yet!', 1, '2023-02-20 13:45:00'),
(4, 1, 'I''ve shared some content ideas in the shared folder.', 1, '2023-02-20 15:30:00'),
(4, 5, 'What''s our budget for this campaign?', 1, '2023-02-21 10:15:00'),
(4, 4, 'Budget details are now available in the project documents.', 1, '2023-02-21 11:00:00'),
(4, 1, 'I''ve finished the first draft of the main campaign copy.', 0, '2023-02-22 16:30:00');

-- Insert messages for Project 5
INSERT INTO message (projectId, senderId, content, is_read, created_at) VALUES
(5, 5, 'Product Launch project is now set up. This is going to be our biggest launch ever!', 1, '2023-02-25 16:00:00'),
(5, 2, 'I''ve started working on the press materials.', 1, '2023-02-26 09:30:00'),
(5, 4, 'When is the target launch date?', 1, '2023-02-26 11:45:00'),
(5, 5, 'We''re aiming for August 15th, but we should have everything ready by August 1st.', 1, '2023-02-26 13:20:00'),
(5, 2, 'Just uploaded the first draft of the press release for review.', 0, '2023-02-27 15:45:00');
