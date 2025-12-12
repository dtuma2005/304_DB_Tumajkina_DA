DROP TABLE IF EXISTS performed_services;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;
DROP TABLE IF EXISTS specialties;
DROP TABLE IF EXISTS doctors;
DROP TABLE IF EXISTS patients;

CREATE TABLE specialties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);

CREATE TABLE doctors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    specialty_id INTEGER NOT NULL,
    salary_percentage REAL NOT NULL CHECK(salary_percentage >= 0 AND salary_percentage <= 100),
    hire_date DATE NOT NULL DEFAULT (date('now')),
    dismissal_date DATE,
    phone TEXT,
    email TEXT,
    CHECK(dismissal_date IS NULL OR dismissal_date >= hire_date),
    FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE RESTRICT
);

CREATE TABLE service_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price >= 0),
    category_id INTEGER NOT NULL,
    description TEXT,
    FOREIGN KEY (category_id) REFERENCES service_categories(id) ON DELETE RESTRICT,
    UNIQUE(name, category_id)
);

CREATE TABLE patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    phone TEXT,
    birth_date DATE
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status TEXT NOT NULL DEFAULT 'ожидание' CHECK(status IN ('ожидание', 'завершено', 'отменено')),
    created_at DATETIME NOT NULL DEFAULT (datetime('now')),
    notes TEXT,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE RESTRICT,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE performed_services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER,
    doctor_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    performed_date DATE NOT NULL DEFAULT (date('now')),
    performed_time TIME NOT NULL,
    actual_duration_minutes INTEGER NOT NULL CHECK(actual_duration_minutes > 0),
    actual_price REAL NOT NULL CHECK(actual_price >= 0),
    notes TEXT,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);



INSERT INTO specialties (name) VALUES
('Терапевт'),
('Хирург'),
('Ортодонт');

INSERT INTO doctors (name, specialty_id, salary_percentage, hire_date, dismissal_date, phone, email) VALUES
('Волкова Елена Дмитриевна', 1, 25.0, '2023-03-10', NULL, '+7-916-111-11-11', 'volkova@example.com'),
('Громов Артем Сергеевич', 2, 30.0, '2023-06-15', NULL, '+7-917-222-22-22', 'gromov@example.com'),
('Орлова Виктория Игоревна', 3, 20.0, '2024-01-20', NULL, '+7-918-333-33-33', 'orlova@example.com'),
('Федоров Максим Андреевич', 3, 27.0, '2023-09-05', NULL, '+7-919-444-44-44', 'fedorov@example.com'),
('Семенова Ольга Петровна', 1, 35.0, '2024-02-10', NULL, '+7-920-555-55-55', 'semenova@example.com');

INSERT INTO service_categories (name, description) VALUES
('Терапевтическая стоматология', 'Лечение кариеса, пломбирование'),
('Хирургическая стоматология', 'Удаление зубов, операции'),
('Имплантация', 'Установка зубных имплантов');

INSERT INTO services (name, duration_minutes, price, category_id, description) VALUES
('Пломбирование жевательного зуба', 40, 3000.0, 1, 'Лечение кариеса и установка пломбы на жевательный зуб'),
('Удаление зуба мудрости', 60, 5000.0, 2, 'Хирургическое удаление сложного зуба мудрости'),
('Установка импланта премиум', 120, 25000.0, 3, 'Имплантация зуба с премиум системой');

INSERT INTO patients (name, phone, birth_date) VALUES
('Ковалев Денис Сергеевич', '+7-925-111-22-33', '1990-05-10'),
('Морозова Анастасия Викторовна', '+7-926-222-33-44', '1980-02-21'),
('Тарасов Иван Петрович', '+7-927-333-44-55', '2001-09-09'),
('Белова София Александровна', '+7-928-444-55-66', '2005-04-18'),
('Жуков Павел Олегович', '+7-929-555-66-77', '1999-02-06'),
('Григорьева Мария Дмитриевна', '+7-930-666-77-88', '1985-09-15');

INSERT INTO appointments (patient_id, doctor_id, service_id, appointment_date, appointment_time, status, notes) VALUES
(1, 1, 1, '2024-12-15', '10:00', 'ожидание', 'Пациент просит использовать светоотверждаемую пломбу'),
(2, 2, 2, '2024-12-21', '12:00', 'ожидание', NULL),
(3, 3, 3, '2024-12-06', '11:00', 'ожидание', 'Проверить совместимость с предыдущими имплантами');

INSERT INTO performed_services (appointment_id, doctor_id, service_id, performed_date, performed_time, actual_duration_minutes, actual_price, notes) VALUES
(1, 1, 1, '2024-12-10', '10:30', 45, 3000.0, 'Процедура прошла успешно, установлена качественная пломба'),
(NULL, 2, 2, '2024-12-05', '11:00', 65, 5000.0, 'Без предварительной записи, сложное удаление'),
(3, 3, 3, '2024-11-28', '12:30', 125, 25000.0, 'Имплант установлен, требуется контроль через месяц');