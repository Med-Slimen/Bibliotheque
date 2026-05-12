<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ─── LOGIN ───────────────────────────────────────────────
if ($action === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Champs requis manquants']);
        exit;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, nom, prenom, role, mot_de_passe FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['success' => true, 'role' => $user['role']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email ou mot de passe incorrect']);
    }
    $conn->close();
    exit;
}

// ─── LOGOUT ──────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    header('Location: /index.php');
    exit;
}

// ─── REGISTER ────────────────────────────────────────────
if ($action === 'register') {
    $nom    = trim($_POST['nom']      ?? '');
    $prenom = trim($_POST['prenom']   ?? '');
    $email  = trim($_POST['email']    ?? '');
    $pass   = $_POST['password']      ?? '';
    $tel    = trim($_POST['telephone'] ?? '');

    if (!$nom || !$prenom || !$email || !$pass) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        exit;
    }
    if (strlen($pass) < 6) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe trop court (min. 6 caractères)']);
        exit;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $conn = getConnection();
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, 'adherent')");
        $stmt->bind_param("ssss", $nom, $prenom, $email, $hash);
        $stmt->execute();
        $uid = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO adherent (id_utilisateur, telephone, status) VALUES (?, ?, 'actif')");
        $stmt2->bind_param("is", $uid, $tel);
        $stmt2->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
    }
    $conn->close();
    exit;
}

// ─── ME (vérifier session) ───────────────────────────────
if ($action === 'me') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['logged' => false]);
    } else {
        echo json_encode([
            'logged' => true,
            'id'     => $_SESSION['user_id'],
            'name'   => $_SESSION['user_name'],
            'role'   => $_SESSION['user_role'],
        ]);
    }
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
?>