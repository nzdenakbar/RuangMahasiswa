<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('dashboard.php');
verify_csrf();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($id){
 $stmt=$conn->prepare("SELECT image FROM news WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();$item=$stmt->get_result()->fetch_assoc();
 if($item){$stmt=$conn->prepare("DELETE FROM news WHERE id=?");$stmt->bind_param('i',$id);$stmt->execute();if($item['image']&&is_file(__DIR__.'/../uploads/'.$item['image']))unlink(__DIR__.'/../uploads/'.$item['image']);$_SESSION['flash']='Informasi berhasil dihapus.';}
}
redirect('dashboard.php');
