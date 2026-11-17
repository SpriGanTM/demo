<?php
require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}
function flash(string $message, string $type='success'): void {
    $_SESSION['flash'] = ['message'=>$message, 'type'=>$type];
}
function getFlash(): ?array {
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verifyCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Ошибка безопасности формы. Обновите страницу и попробуйте снова.');
    }
}
function isAdmin(): bool {
    return !empty($_SESSION['is_admin']);
}
function requireAdmin(): void {
    if (!isAdmin()) {
        flash('Сначала войдите в панель администратора.', 'error');
        redirect('login.php');
    }
}
function validPersonName(string $name): bool {
    return (bool)preg_match('/^[А-Яа-яЁёA-Za-z][А-Яа-яЁёA-Za-z .\\-]{0,59}$/u', $name);
}
function validPhone(string $phone): bool {
    return (bool)preg_match('/^\\+7\\(\\d{3}\\)\\d{3}-\\d{2}-\\d{2}$/', $phone);
}
function approvedPeriods(int $roomId): array {
    $stmt = db()->prepare("SELECT check_in, check_out FROM bookings WHERE room_id=? AND status='Одобрена'");
    $stmt->execute([$roomId]);
    return $stmt->fetchAll();
}
function datesOverlap(string $in, string $out, string $oldIn, string $oldOut): bool {
    return $in < $oldOut && $out > $oldIn;
}
function renderHeader(string $title): void {
    $flash = getFlash();
    echo '<!doctype html><html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>'.e($title).' — Сладкие сны</title><link rel="stylesheet" href="assets/style.css"></head><body>';
    echo '<header class="site-header"><div class="container nav"><a class="brand" href="index.php">Сладкие сны<span>Отдых, который начинается с улыбки</span></a><nav><a href="index.php">Главная</a><a href="catalog.php">Каталог</a><a href="login.php">Для администратора</a></nav></div></header><main class="container">';
    if ($flash) echo '<div class="alert '.e($flash['type']).'">'.e($flash['message']).'</div>';
}
function renderFooter(): void {
    echo '</main><footer><div class="container footer-grid"><div><b>Сладкие сны</b><p>Ваш уютный отдых начинается здесь.</p></div><div><b>Контакты</b><p>Адрес: г. Москва, ул. Центральная, 10<br>Режим работы: ежедневно, круглосуточно<br>Телефон: +7(900)123-45-67<br>E-mail: hello@sweetdreams.example</p></div></div><div class="copyright">© '.date('Y').' Гостиница «Сладкие сны»</div></footer><script src="assets/app.js"></script></body></html>';
}
