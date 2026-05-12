<?php
require_once '../includes/auth_check.php';
requireAuth(['adherent', 'bibliothecaire', 'administrateur']);
// Borrowing is handled via modals in list.php and search.php
header('Location: /documents/list.php');
exit;
?>
