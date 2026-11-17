<?php
require_once 'functions.php';
if (isAdmin()) redirect('admin.php');
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verifyCsrf();
    $login=$_POST['login']??''; $password=$_POST['password']??'';
    if (hash_equals(ADMIN_LOGIN,$login) && hash_equals(ADMIN_PASSWORD,$password)) {
        session_regenerate_id(true); $_SESSION['is_admin']=true; flash('Вы вошли в панель управления.'); redirect('admin.php');
    }
    $error='Неверный логин или пароль.';
}
renderHeader('Вход администратора');
?>
<div class="login-wrap"><form class="form-card login-card" method="post"><span class="eyebrow">ЗАКРЫТЫЙ РАЗДЕЛ</span><h1>Вход администратора</h1><?php if($error): ?><div class="alert error"><?=e($error)?></div><?php endif; ?><input type="hidden" name="csrf" value="<?=e(csrfToken())?>"><label>Логин<input name="login" required autocomplete="username"></label><label>Пароль<input type="password" name="password" required autocomplete="current-password"></label><button class="btn btn-block">Войти</button><p class="hint">Доступ только для администратора гостиницы.</p></form></div>
<?php renderFooter(); ?>
