-- MySQL 8.0 schema (для развёртывания на MySQL вместо автоматической SQLite-БД)
CREATE DATABASE IF NOT EXISTS sweet_dreams CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sweet_dreams;

CREATE TABLE rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category ENUM('Стандарт','Студия','Люкс') NOT NULL,
  title VARCHAR(150) NOT NULL,
  price INT UNSIGNED NOT NULL,
  capacity TINYINT UNSIGNED NOT NULL,
  area DECIMAL(6,2) NOT NULL,
  image VARCHAR(255) NOT NULL DEFAULT '',
  amenities TEXT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id INT UNSIGNED NOT NULL,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(190) NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  status ENUM('Новая','Одобрена','Отклонена') NOT NULL DEFAULT 'Новая',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_booking_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  INDEX idx_booking_room_dates (room_id, check_in, check_out, status)
) ENGINE=InnoDB;

INSERT INTO rooms(category,title,price,capacity,area,amenities) VALUES
('Стандарт','Стандарт с видом на сад',3200,2,18,'Wi-Fi, телевизор, кондиционер'),
('Стандарт','Уютный стандарт',3500,2,20,'Wi-Fi, телевизор, холодильник'),
('Студия','Студия с кухонной зоной',5200,3,32,'Wi-Fi, кухня, кондиционер, телевизор'),
('Студия','Семейная студия',5900,4,38,'Wi-Fi, холодильник, кондиционер'),
('Люкс','Люкс с панорамными окнами',8900,2,45,'Wi-Fi, джакузи, мини-бар, телевизор'),
('Люкс','Двухкомнатный люкс',10500,4,62,'Wi-Fi, гостиная, холодильник, кондиционер');
