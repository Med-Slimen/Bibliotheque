<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$conn   = getConnection();

// ─── LIST ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'list') {
    $role = $_GET['role'] ?? '';
    $sql  = "SELECT u.*,
                a.telephone,
                a.status AS adherent_status,
                (SELECT COUNT(*) FROM emprunt e
                 WHERE e.id_adherent = u.id
                 AND e.statut IN('en_cours','en_retard')) AS emprunts_actifs,
                (SELECT COUNT(*) FROM abonnement ab
                 WHERE ab.id_adherent = u.id
                 AND ab.date_fin >= CURDATE()) AS abonnement_actif
             FROM utilisateur u
             LEFT JOIN adherent a ON u.id = a.id_utilisateur
             WHERE 1=1";

    if ($role) {
        $sql .= " AND u.role = '" . $conn->real_escape_string($role) . "'";
    }
    $sql .= " ORDER BY u.created_at DESC";
    echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
    exit;
}

// ─── STATS ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $s = [];
    $s['total']           = $conn->query("SELECT COUNT(*) c FROM utilisateur")->fetch_assoc()['c'];
    $s['adherents']       = $conn->query("SELECT COUNT(*) c FROM adherent WHERE status='actif'")->fetch_assoc()['c'];
    $s['bibliothecaires'] = $conn->query("SELECT COUNT(*) c FROM utilisateur WHERE role='bibliothecaire'")->fetch_assoc()['c'];
    $s['admins']          = $conn->query("SELECT COUNT(*) c FROM utilisateur WHERE role='administrateur'")->fetch_assoc()['c'];
    echo json_encode($s);
    exit;
}

// ─── DETAIL ───────────────────────────────────────────────
if ($method === 'GET' && $action === 'detail') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("
        SELECT u.*, a.telephone, a.status AS adherent_status
        FROM utilisateur u
        LEFT JOIN adherent a ON u.id = a.id_utilisateur
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// ─── CREATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['nom']) || empty($data['prenom']) || empty($data['email']) || empty($data['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être remplis']);
        exit;
    }

    $hash = password_hash($data['mot_de_passe'], PASSWORD_DEFAULT);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss",
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $hash,
            $data['role']
        );
        $stmt->execute();
        $uid = $conn->insert_id;

        if ($data['role'] === 'adherent') {
            $tel = $data['telephone'] ?? '';
            $s2  = $conn->prepare("INSERT INTO adherent (id_utilisateur, telephone, status) VALUES (?, ?, 'actif')");
            $s2->bind_param("is", $uid, $tel);
            $s2->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'id' => $uid]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
    }
    exit;
}

// ─── UPDATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            UPDATE utilisateur
            SET nom = ?, prenom = ?, email = ?, role = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssi",
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['role'],
            $data['id']
        );
        $stmt->execute();

        if ($data['role'] === 'adherent') {
            $tel    = $data['telephone'] ?? '';
            $status = $data['status']    ?? 'actif';
            $s2 = $conn->prepare("
                INSERT INTO adherent (id_utilisateur, telephone, status)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE telephone = ?, status = ?
            ");
            $s2->bind_param("issss", $data['id'], $tel, $status, $tel, $status);
            $s2->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── DELETE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);

    // Ne pas supprimer son propre compte
    if ($id === (int)$_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte']);
        exit;
    }

    // Vérifier si l'utilisateur a des emprunts actifs
    $chk = $conn->prepare("
        SELECT COUNT(*) c FROM emprunt
        WHERE id_adherent = ? AND statut IN('en_cours','en_retard')
    ");
    $chk->bind_param("i", $id);
    $chk->execute();
    $count = $chk->get_result()->fetch_assoc()['c'];

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Impossible : cet utilisateur a des emprunts en cours']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo json_encode(['success' => $stmt->execute()]);
    exit;
}

// ─── CHANGE PASSWORD ──────────────────────────────────────
if ($method === 'POST' && $action === 'password') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $id      = (int)($data['id'] ?? $_SESSION['user_id'] ?? 0);
    $newpass = password_hash($data['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
    $stmt->bind_param("si", $newpass, $id);
    echo json_encode(['success' => $stmt->execute()]);
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
$conn->close();
?>