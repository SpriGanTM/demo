<?php
require_once 'functions.php'; requireAdmin();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf(); $id=(int)($_POST['booking_id']??0); $action=$_POST['action']??'';
    if ($action==='approve' || $action==='reject') {
        $q=db()->prepare('SELECT * FROM bookings WHERE id=?'); $q->execute([$id]); $booking=$q->fetch();
        if ($booking) {
            if ($action==='approve') {
                $conf=db()->prepare("SELECT check_in,check_out FROM bookings WHERE room_id=? AND status='Одобрена' AND id<>?");
                $conf->execute([$booking['room_id'],$id]); $conflicts=false;
                foreach($conf->fetchAll() as $p) if(datesOverlap($booking['check_in'],$booking['check_out'],$p['check_in'],$p['check_out'])) $conflicts=true;
                if ($conflicts) flash('Нельзя одобрить заявку: даты пересекаются с другой подтверждённой бронью.','error');
                else { db()->prepare("UPDATE bookings SET status='Одобрена' WHERE id=?")->execute([$id]); flash('Заявка одобрена.'); }
            } else { db()->prepare("UPDATE bookings SET status='Отклонена' WHERE id=?")->execute([$id]); flash('Заявка отклонена.'); }
        }
    } elseif ($action==='delete') {
        db()->prepare('DELETE FROM bookings WHERE id=?')->execute([$id]); flash('Заявка удалена.');
    }
    redirect('admin.php');
}
$bookings=db()->query('SELECT b.*,r.title AS room_title,r.category FROM bookings b JOIN rooms r ON r.id=b.room_id ORDER BY b.created_at DESC,b.id DESC')->fetchAll();
renderHeader('Панель управления');
?>
<div class="admin-heading"><div><span class="eyebrow">УПРАВЛЕНИЕ ГОСТИНИЦЕЙ</span><h1>Заявки на бронирование</h1><p>Всего заявок: <?=count($bookings)?></p></div><a class="btn btn-light" href="logout.php">Выйти</a></div>
<div class="table-wrap"><table><thead><tr><th>№</th><th>Гость</th><th>Контакты</th><th>Номер</th><th>Даты</th><th>Статус</th><th>Действия</th></tr></thead><tbody>
<?php foreach($bookings as $b): ?><tr><td>#<?=$b['id']?></td><td><?=e($b['last_name'].' '.$b['first_name'])?></td><td><?=e($b['phone'])?><br><?=e($b['email'])?></td><td><?=e($b['room_title'])?></td><td><?=date('d.m.Y',strtotime($b['check_in']))?> — <?=date('d.m.Y',strtotime($b['check_out']))?></td><td><span class="status"><?=e($b['status'])?></span></td><td><div class="actions">
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrfToken())?>"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><button name="action" value="approve" class="mini approve" <?= $b['status']==='Одобрена'?'disabled':'' ?>>Одобрить</button></form>
<form method="post"><input type="hidden" name="csrf" value="<?=e(csrfToken())?>"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><button name="action" value="reject" class="mini" >Отклонить</button></form>
<form method="post" onsubmit="return confirm('Удалить заявку #<?=$b['id']?>?')"><input type="hidden" name="csrf" value="<?=e(csrfToken())?>"><input type="hidden" name="booking_id" value="<?=$b['id']?>"><button name="action" value="delete" class="mini danger">Удалить</button></form>
</div></td></tr><?php endforeach; ?>
<?php if(!$bookings): ?><tr><td colspan="7" class="empty">Пока заявок нет.</td></tr><?php endif; ?>
</tbody></table></div>
<?php renderFooter(); ?>
