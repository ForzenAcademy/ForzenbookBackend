
USE socialmedia;

-- ------------------- USERS

DROP TABLE IF EXISTS users;

CREATE TABLE users
( 
  user_id int PRIMARY KEY AUTO_INCREMENT,
  email varchar(64) NOT NULL,
  birth_date date NOT NULL,
  first_name varchar(20) NOT NULL,
  last_name varchar(20) NOT NULL,
  location varchar(64) NOT NULL,
  profile_image varchar(24) NOT NULL,
  about_me varchar(512) NOT NULL,
  created_at timestamp DEFAULT (CURRENT_TIMESTAMP)
 );

INSERT INTO users 
(email, birth_date, first_name, last_name, location, profile_image, about_me) 
VALUES 
('nic_wilson@live.com', '1990-12-18', 'nic', 'wilson', 'Hell, MI',"upload/pp_default.jpg","ABOUT_ME_DEFAULT_VALUE");

INSERT INTO users 
(email, birth_date, first_name, last_name, location, profile_image, about_me) 
VALUES 
('hamdan.mobeen@gmail.com', '1997-07-09', 'hamdan', 'syed', 'Pooville, CA',"upload/pp_default.jpg","ABOUT_ME_DEFAULT_VALUE");

INSERT INTO users 
(email, birth_date, first_name, last_name, location, profile_image, about_me) 
VALUES 
('axaxotl@gmail.com', '1996-07-22', 'Brandon', 'Ross', "Michael's Dungeon","upload/pp_default.jpg","ABOUT_ME_DEFAULT_VALUE");


-- ------------------- CODES

DROP TABLE IF EXISTS codes;

CREATE TABLE codes
(
  email varchar(30) PRIMARY KEY NOT NULL,
  code int NOT NULL
);

-- ------------------- LOGIN TOKENS

DROP TABLE IF EXISTS auth;

CREATE TABLE auth
(
  user_id int PRIMARY KEY NOT NULL,
  email varchar(30) NOT NULL,
  token varchar(64) NOT NULL
);

-- ------------------- POSTS

DROP TABLE IF EXISTS posts;

CREATE TABLE posts
(
  post_id int PRIMARY KEY AUTO_INCREMENT,
  user_id int NOT NULL,
  body varchar(256) NOT NULL,
  post_type varchar(5) NOT NULL,
  created_at timestamp DEFAULT (CURRENT_TIMESTAMP)
);

