<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent', 'bibliothecaire', 'administrateur']);
$id = (int)($_GET['id'] ?? 0);
// Detail is shown via modals - redirect to catalogue
header('Location: /documents/list.php');
exit;
?>
