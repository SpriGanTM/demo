<?php
require_once 'functions.php';
$category = trim($_GET['category'] ?? '');
$min = trim($_GET['min_price'] ?? '');
$max = trim($_GET['max_price'] ?? '');
$capacity = trim($_GET['capacity'] ?? '');
$sql = 'SELECT * FROM rooms WHERE 1=1'; $params=[];
if ($category !== '' && in_array($category, ['Стандарт','Студия','Люкс'], true)) { $sql.=' AND category=?'; $params[]=$category; }
if ($min !== '' && ctype_digit($min)) { $sql.=' AND price>=?'; $params[]=(int)$min; }
if ($max !== '' && ctype_digit($max)) { $sql.=' AND price<=?'; $params[]=(int)$max; }
if ($capacity !== '' && ctype_digit($capacity)) { $sql.=' AND capacity>=?'; $params[]=(int)$capacity; }
$sql .= ' ORDER BY RANDOM() LIMIT 6';
$stmt=db()->prepare($sql); $stmt->execute($params); $rooms=$stmt->fetchAll();
renderHeader('Каталог номеров');
?>
<div class="page-heading"><span class="eyebrow">РАЗМЕЩЕНИЕ</span><h1>Наши номера</h1><p>Найдите пространство, в котором вам будет хорошо.</p></div>
<form class="filters" method="get">
  <label>Категория<select name="category"><option value="">Все категории</option><?php foreach(['Стандарт','Студия','Люкс'] as $c): ?><option value="<?=e($c)?>" <?=$category===$c?'selected':''?>><?=e($c)?></option><?php endforeach; ?></select></label>
  <label>Цена от, ₽<input type="number" min="0" name="min_price" value="<?=e($min)?>" placeholder="0"></label>
  <label>Цена до, ₽<input type="number" min="0" name="max_price" value="<?=e($max)?>" placeholder="Без лимита"></label>
  <label>Количество мест<select name="capacity"><option value="">Любое</option><?php foreach([1,2,3,4] as $n): ?><option value="<?=$n?>" <?=$capacity==(string)$n?'selected':''?>>От <?=$n?></option><?php endforeach; ?></select></label>
  <div class="filter-actions"><button class="btn" type="submit">Применить</button><a class="btn btn-light" href="catalog.php">Сбросить</a></div>
</form>
<div class="room-grid">
<?php foreach($rooms as $room): ?>
<article class="room-card"><div class="room-visual"><span><?=e($room['category'])?></span><div class="room-symbol">✦</div></div><div class="room-body"><div class="room-title"><h2><?=e($room['title'])?></h2><strong><?=number_format((int)$room['price'],0,',',' ')?> ₽<small>/ сутки</small></strong></div><p><?=e($room['amenities'])?></p><div class="room-meta"><span>♧ <?=$room['capacity']?> места</span><span>▱ <?=$room['area']?> м²</span></div><a class="btn btn-block" href="booking.php?room=<?=$room['id']?>">Забронировать</a></div></article>
<?php endforeach; ?>
</div>
<?php if (!$rooms): ?><div class="empty">По выбранным условиям номера не найдены. Измените фильтры.</div><?php endif; ?>
<?php renderFooter(); ?>
