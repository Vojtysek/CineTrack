-- Tabulka uživatelů
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- hashované heslo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabulka filmových studií
CREATE TABLE studios (
    studio_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Tabulka filmů
CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    director_name VARCHAR(100),
    year YEAR,
    description TEXT,
    poster_url VARCHAR(255),
    studio_id INT,
    FOREIGN KEY (studio_id) REFERENCES studios(studio_id)
        ON DELETE SET NULL
);

-- Tabulka žánrů
CREATE TABLE genres (
    genre_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Tabulka herců
CREATE TABLE actors (
    actor_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100)
);

-- Vazba film ↔ žánr
CREATE TABLE movie_genre (
    movie_id INT,
    genre_id INT,
    PRIMARY KEY (movie_id, genre_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
);

-- Vazba film ↔ herec
CREATE TABLE movie_actor (
    movie_id INT,
    actor_id INT,
    PRIMARY KEY (movie_id, actor_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES actors(actor_id) ON DELETE CASCADE
);

-- Vazba uživatel ↔ film (uživatelský deník)
CREATE TABLE user_movie (
    user_movie_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    status ENUM('seen', 'want_to_see') NOT NULL,
    rating TINYINT CHECK (rating BETWEEN 1 AND 10),
    comment TEXT,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

-- Vazba uživatel ↔ oblíbený žánr
CREATE TABLE user_genre_favorite (
    user_id INT,
    genre_id INT,
    PRIMARY KEY (user_id, genre_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(genre_id) ON DELETE CASCADE
);
