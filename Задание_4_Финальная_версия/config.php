<?php
declare(strict_types=1);
session_start();

const DB_PATH = __DIR__ . '/database.sqlite';
const ADMIN_LOGIN = 'hotel123';
const ADMIN_PASSWORD = 'adminHotel';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category TEXT NOT NULL,
        title TEXT NOT NULL,
        price INTEGER NOT NULL,
        capacity INTEGER NOT NULL,
        area REAL NOT NULL,
        image TEXT NOT NULL DEFAULT '',
        amenities TEXT NOT NULL DEFAULT ''
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        room_id INTEGER NOT NULL,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        phone TEXT NOT NULL,
        email TEXT NOT NULL,
        check_in TEXT NOT NULL,
        check_out TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'Новая',
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(room_id) REFERENCES rooms(id) ON DELETE CASCADE
    )");
    $count = (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
    if ($count === 0) {
        $seed = [
            ['Стандарт','Стандарт с видом на сад',3200,2,18,'Wi-Fi, телевизор, кондиционер'],
            ['Стандарт','Уютный стандарт',3500,2,20,'Wi-Fi, телевизор, холодильник'],
            ['Студия','Студия с кухонной зоной',5200,3,32,'Wi-Fi, кухня, кондиционер, телевизор'],
            ['Студия','Семейная студия',5900,4,38,'Wi-Fi, холодильник, кондиционер'],
            ['Люкс','Люкс с панорамными окнами',8900,2,45,'Wi-Fi, джакузи, мини-бар, телевизор'],
            ['Люкс','Двухкомнатный люкс',10500,4,62,'Wi-Fi, гостиная, холодильник, кондиционер'],
            ['Стандарт','Стандарт комфорт',3900,2,22,'Wi-Fi, телевизор, кондиционер'],
            ['Студия','Студия делюкс',6500,3,40,'Wi-Fi, кухня, телевизор, холодильник'],
        ];
        $stmt = $pdo->prepare('INSERT INTO rooms(category,title,price,capacity,area,amenities) VALUES(?,?,?,?,?,?)');
        foreach ($seed as $room) $stmt->execute($room);
    }
    return $pdo;
}
