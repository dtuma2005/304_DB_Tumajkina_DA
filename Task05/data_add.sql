INSERT OR IGNORE INTO users (name, email, gender, register_date, occupation_id)
VALUES
('Сергеева Ольга Денисовна', 'sergeevan@gmail.com', 'female', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Тараканова Вероника Юрьевна', 'tarakanova@gmail.com', 'female', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
 ('Тумайкина Дарья Александровна', 'tumaykina@gmail.com', 'female', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Шагилов Кирилл Дмитриевич', 'shagilov@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1)),
('Шеволаев Илья Вячеславович', 'Shevolaev@gmail.com', 'male', date('now'),
 (SELECT id FROM occupations ORDER BY id LIMIT 1));



INSERT OR IGNORE INTO movies (title, year)
VALUES
('Бегущий по лезвию 2049 (2017)', 2017),
('Форма голоса (2016)', 2016),
('Милые кости (2009)', 2009);

INSERT OR IGNORE INTO genres (name) VALUES ('Thriller');
INSERT OR IGNORE INTO genres (name) VALUES ('Sci-Fi');
INSERT OR IGNORE INTO genres (name) VALUES ('Action');
INSERT OR IGNORE INTO genres (name) VALUES ('Anime');
INSERT OR IGNORE INTO genres (name) VALUES ('Drama');

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Sci-Fi'
WHERE m.title = 'Бегущий по лезвию 2049 (2017)';

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Anime'
WHERE m.title = 'Форма голоса (2016)';

INSERT OR IGNORE INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id FROM movies m JOIN genres g ON g.name = 'Drama'
WHERE m.title = 'Милые кости (2009)';


INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.8, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Бегущий по лезвию 2049 (2017)'
WHERE u.email = 'tumaykina@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 5.0, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Форма голоса (2016)'
WHERE u.email = 'tumaykina@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT u.id, m.id, 4.9, strftime('%s','now')
FROM users u JOIN movies m ON m.title = 'Милые кости (2009)'
WHERE u.email = 'tumaykina@gmail.com'
AND NOT EXISTS (
    SELECT 1 FROM ratings r WHERE r.user_id = u.id AND r.movie_id = m.id
);