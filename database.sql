-- Kreiranje baze podataka
CREATE DATABASE IF NOT EXISTS movie_videoteka;
USE movie_videoteka;

-- Tablica za korisnike
CREATE TABLE IF NOT EXISTS users (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('user', 'admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablica za filmove
CREATE TABLE IF NOT EXISTS films (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  genre VARCHAR(100),
  year INT(4),
  country VARCHAR(100),
  duration INT(11) COMMENT 'Trajanje u minutama',
  description TEXT,
  image_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablica za ocjene filmova
CREATE TABLE IF NOT EXISTS ratings (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NOT NULL,
  film_id INT(11) NOT NULL,
  rating INT(1) NOT NULL CHECK(rating >= 1 AND rating <= 5),
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_film_rating (user_id, film_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablica za korisnikovu videoteku (željeni filmovi)
CREATE TABLE IF NOT EXISTS myvideoteka (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NOT NULL,
  film_id INT(11) NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_film (user_id, film_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablica za logiranje upozorenja (niske ocjene)
CREATE TABLE IF NOT EXISTS warnings (
  id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11) NOT NULL,
  film_id INT(11) NOT NULL,
  warning_message TEXT,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (film_id) REFERENCES films(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inseriranje admin korisnika
-- admin / admin123
-- Hash generirano sa: password_hash("admin123", PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@videoteka.local', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36P4/KFm', 'admin');

-- Inseriranje test podataka
INSERT INTO films (title, genre, year, country, duration, description, image_path) VALUES
('Inception', 'Sci-Fi', 2010, 'USA', 148, 'A thief who steals corporate secrets through dream-sharing technology.', 'https://via.placeholder.com/250x300?text=Inception'),
('The Matrix', 'Sci-Fi', 1999, 'USA', 136, 'A computer programmer discovers that reality as he knows it is a simulation.', 'https://via.placeholder.com/250x300?text=The+Matrix'),
('Pulp Fiction', 'Crime/Drama', 1994, 'USA', 154, 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine.', 'https://via.placeholder.com/250x300?text=Pulp+Fiction'),
('The Shawshank Redemption', 'Drama', 1994, 'USA', 142, 'Two imprisoned men bond over a number of years, finding solace and eventual redemption.', 'https://via.placeholder.com/250x300?text=Shawshank'),
('The Dark Knight', 'Action/Crime', 2008, 'USA', 152, 'When the menace known as The Joker wreaks havoc on Gotham, Batman must face off.', 'https://via.placeholder.com/250x300?text=Dark+Knight'),
('Forrest Gump', 'Drama', 1994, 'USA', 142, 'The presidencies of Kennedy and Johnson unfold through the perspective of an Alabama man.', 'https://via.placeholder.com/250x300?text=Forrest+Gump'),
('Interstellar', 'Sci-Fi', 2014, 'USA', 169, 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity survival.', 'https://via.placeholder.com/250x300?text=Interstellar'),
('The Avengers', 'Action/Adventure', 2012, 'USA', 143, 'Earth mightiest heroes must come together to prevent an alien invasion.', 'https://via.placeholder.com/250x300?text=Avengers');
