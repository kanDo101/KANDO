-- User table
CREATE TABLE user (
  id int(11) NOT NULL AUTO_INCREMENT,
  fullName varchar(255) NOT NULL,
  username varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  profession varchar(255) DEFAULT NULL,
  email varchar(100) DEFAULT NULL,
  photo varchar(255) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY username (username),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Project table
CREATE TABLE project (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description text DEFAULT NULL,
  userId int(11) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  dueDate date DEFAULT NULL,
  PRIMARY KEY (id),
  KEY userId (userId),
  CONSTRAINT project_user_fk FOREIGN KEY (userId) REFERENCES user (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Task table
CREATE TABLE task (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  description text DEFAULT NULL,
  state enum('Pending','inprogress','Completed') DEFAULT 'Pending',
  userId int(11) DEFAULT NULL,
  projectId int(11) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  assigned_to int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY projectId (projectId),
  KEY userId (userId),
  KEY assigned_to (assigned_to),
  CONSTRAINT task_project_fk FOREIGN KEY (projectId) REFERENCES project (id) ON DELETE CASCADE,
  CONSTRAINT task_user_fk FOREIGN KEY (userId) REFERENCES user (id) ON DELETE SET NULL,
  CONSTRAINT task_assigned_user_fk FOREIGN KEY (assigned_to) REFERENCES user (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Appartenir (User-Project relationship) table
CREATE TABLE appartenir (
  userId int(11) NOT NULL,
  projectId int(11) NOT NULL,
  PRIMARY KEY (userId, projectId),
  KEY projectId (projectId),
  CONSTRAINT appartenir_user_fk FOREIGN KEY (userId) REFERENCES user (id) ON DELETE CASCADE,
  CONSTRAINT appartenir_project_fk FOREIGN KEY (projectId) REFERENCES project (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Message table (inferred from code)
CREATE TABLE message (
  id int(11) NOT NULL AUTO_INCREMENT,
  projectId int(11) NOT NULL,
  senderId int(11) NOT NULL,
  content text NOT NULL,
  is_read tinyint(1) DEFAULT 0,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY projectId (projectId),
  KEY senderId (senderId),
  CONSTRAINT message_project_fk FOREIGN KEY (projectId) REFERENCES project (id) ON DELETE CASCADE,
  CONSTRAINT message_user_fk FOREIGN KEY (senderId) REFERENCES user (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
