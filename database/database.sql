CREATE DATABASE ewu_innovation_hub;

USE ewu_innovation_hub;

CREATE TABLE users (
    user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty') NOT NULL,
    department VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

create Table ideas (
    idea_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT NOT NULL,
    title varchar(255) not null,
    description text not null,
    catagory varchar(100) not null,
    status ENUM('pending', 'approved', 'rejected') default 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(user_id)
);

CREATE TABLE reviews (
    review_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idea_id BIGINT NOT NULL,
    faculty_id BIGINT NOT NULL,
    comment TEXT,
    decision ENUM('approved', 'rejected') NOT NULL,
    reviewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idea_id) REFERENCES ideas(idea_id),
    FOREIGN KEY (faculty_id) REFERENCES users(user_id)
);

CREATE TABLE MENTORSHIP (
    mentorship_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    idea_id BIGINT NOT NULL,
    student_id BIGINT NOT NULL,
    faculty_id BIGINT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    foreign KEY (idea_id) REFERENCES ideas(idea_id),
    foreign key (student_id) REFERENCES users(user_id),
    foreign key (faculty_id) REFERENCES users(user_id)
);