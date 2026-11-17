<?php
require_once 'functions.php';
$roomId=(int)($_GET['room'] ?? $_POST['room_id'] ?? 0);
$stmt=db()->prepare('SELECT * FROM rooms WHERE id=?'); $stmt->execute([$roomId]); $room=$stmt->fetch();
if (!$room) { http_response_code(404); renderHeader('Номер не найден'); echo '<div class="empty">Номер не найден. <a href="catalog.php">Вернуться в каталог</a></div>'; renderFooter(); exit; }
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $first=trim($_POST['first_name'] ?? ''); $last=trim($_POST['last_name'] ?? '');
    $phone=trim($_POST['phone'] ?? ''); $email=trim($_POST['email'] ?? '');
    $in=trim($_POST['check_in'] ?? ''); $out=trim($_POST['check_out'] ?? '');
    if (!validPersonName($first)) $errors[]='Имя обязательно: используйте буквы, пробелы, точки или дефисы.';
    if (!validPersonName($last)) $errors[]='Фамилия обязательна: используйте буквы, пробелы, точки или дефисы.';
    if (!validPhone($phone)) $errors[]='Телефон должен соответствовать формату +7(ХХХ)ХХХ-ХХ-ХХ.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Укажите корректный e-mail.';
    $today=date('Y-m-d');
    $validIn=DateTime::createFromFormat('!Y-m-d',$in);
    $validOut=DateTime::createFromFormat('!Y-m-d',$out);
    if (!$validIn || $validIn->format('Y-m-d')!==$in || $in<$today) $errors[]='Дата заезда должна быть не раньше текущей даты.';
    if (!$validOut || $validOut->format('Y-m-d')!==$out || !$validIn || $out<=$in) $errors[]='Дата выезда должна быть позже даты заезда.';
    if (!$errors) {
        foreach (approvedPeriods($roomId) as $period) {
            if (datesOverlap($in,$out,$period['check_in'],$period['check_out'])) {
                $errors[]='Выбранный период пересекается с уже подтверждённой бронью. Выберите другие даты.'; break;
            }
        }
    }
    if (!$errors) {
        $ins=db()->prepare("INSERT INTO bookings(room_id,first_name,last_name,phone,email,check_in,check_out,status) VALUES(?,?,?,?,?,?,?,'Новая')");
        $ins->execute([$roomId,$first,$last,$phone,$email,$in,$out]);
        flash('Заявка успешно отправлена! Администратор рассмотрит её и свяжется с вами.');
        redirect('catalog.php');
    }
}
$periods=approvedPeriods($roomId);
renderHeader('Бронирование номера');
?>
<div class="page-heading"><span class="eyebrow">ВАШ ОТДЫХ БЛИЖЕ</span><h1>Бронирование номера</h1><p><?=e($room['title'])?> · <?=number_format((int)$room['price'],0,',',' ')?> ₽ за сутки</p></div>
<div class="booking-layout"><aside class="booking-summary"><div class="room-visual large"><span><?=e($room['category'])?></span><div class="room-symbol">✦</div></div><h2><?=e($room['title'])?></h2><p><?=e($room['amenities'])?></p><p>Количество мест: <?=$room['capacity']?> · Площадь: <?=$room['area']?> м²</p></aside>
<form class="form-card" method="post" novalidate>
<input type="hidden" name="csrf" value="<?=e(csrfToken())?>"><input type="hidden" name="room_id" value="<?=$roomId?>">
<?php if ($errors): ?><div class="alert error"><b>Проверьте данные:</b><ul><?php foreach($errors as $error): ?><li><?=e($error)?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="form-row"><label>Имя<input required name="first_name" value="<?=e($_POST['first_name']??'')?>" autocomplete="given-name"></label><label>Фамилия<input required name="last_name" value="<?=e($_POST['last_name']??'')?>" autocomplete="family-name"></label></div>
<div class="form-row"><label>Телефон<input required name="phone" placeholder="+7(900)123-45-67" value="<?=e($_POST['phone']??'')?>"></label><label>E-mail<input required type="email" name="email" value="<?=e($_POST['email']??'')?>"></label></div>
<div class="form-row"><label>Дата заезда<input required type="date" id="check_in" name="check_in" min="<?=date('Y-m-d')?>" value="<?=e($_POST['check_in']??'')?>"></label><label>Дата выезда<input required type="date" id="check_out" name="check_out" min="<?=date('Y-m-d',strtotime('+1 day'))?>" value="<?=e($_POST['check_out']??'')?>"></label></div>
<p class="hint">Периоды подтверждённых бронирований недоступны. Даты дополнительно проверяются на сервере.</p>
<button class="btn btn-block" type="submit">Отправить заявку</button><a class="back-link" href="catalog.php">← Вернуться в каталог</a>
</form></div>
<script>window.approvedPeriods = <?=json_encode($periods,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)?>;</script>
<?php renderFooter(); ?>
