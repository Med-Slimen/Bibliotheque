<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$conn   = getConnection();

// ─── LIST ─────────────────────────────────────────────────
if ($method === 'GET' && $action === 'list') {
    $sql = "SELECT ab.*,
                u.nom, u.prenom, u.email,
                CASE
                    WHEN ab.date_fin >= CURDATE() THEN 'actif'
                    ELSE 'expiré'
                END AS etat
            FROM abonnement ab
            JOIN utilisateur u ON ab.id_adherent = u.id
            ORDER BY ab.date_fin DESC";
    echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
    exit;
}

// ─── MON ABONNEMENT (adhérent connecté) ───────────────────
if ($method === 'GET' && $action === 'mon') {
    $uid  = (int)($_SESSION['user_id'] ?? 0);
    $stmt = $conn->prepare("
        SELECT ab.*,
            CASE
                WHEN ab.date_fin >= CURDATE() THEN 'actif'
                ELSE 'expiré'
            END AS etat,
            DATEDIFF(ab.date_fin, CURDATE()) AS jours_restants
        FROM abonnement ab
        WHERE ab.id_adherent = ?
        ORDER BY ab.date_fin DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// ─── STATS ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $s = [];
    $s['actifs']  = $conn->query("SELECT COUNT(*) c FROM abonnement WHERE date_fin >= CURDATE()")->fetch_assoc()['c'];
    $s['expires'] = $conn->query("SELECT COUNT(*) c FROM abonnement WHERE date_fin < CURDATE()")->fetch_assoc()['c'];
    $s['total']   = $conn->query("SELECT COUNT(*) c FROM abonnement")->fetch_assoc()['c'];
    $s['revenus'] = $conn->query("SELECT COALESCE(SUM(montant), 0) c FROM abonnement")->fetch_assoc()['c'];
    $s['revenus_annee'] = $conn->query("
        SELECT COALESCE(SUM(montant), 0) c FROM abonnement
        WHERE YEAR(date_debut) = YEAR(CURDATE())
    ")->fetch_assoc()['c'];
    echo json_encode($s);
    exit;
}

// ─── CREATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id_adherent']) || empty($data['date_debut']) || empty($data['date_fin']) || empty($data['montant'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
        exit;
    }

    // Vérifier que date_fin > date_debut
    if ($data['date_fin'] <= $data['date_debut']) {
        echo json_encode(['success' => false, 'message' => 'La date de fin doit être après la date de début']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO abonnement (id_adherent, date_debut, date_fin, montant)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("issd",
            $data['id_adherent'],
            $data['date_debut'],
            $data['date_fin'],
            $data['montant']
        );
        $stmt->execute();
        $newId = $conn->insert_id;

        // Activer l'adhérent automatiquement
        $conn->query("
            UPDATE adherent SET status = 'actif'
            WHERE id_utilisateur = " . (int)$data['id_adherent']
        );

        $conn->commit();
        echo json_encode(['success' => true, 'id' => $newId]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── UPDATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);

    if ($data['date_fin'] <= $data['date_debut']) {
        echo json_encode(['success' => false, 'message' => 'La date de fin doit être après la date de début']);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE abonnement
        SET date_debut = ?, date_fin = ?, montant = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssdi",
        $data['date_debut'],
        $data['date_fin'],
        $data['montant'],
        $data['id']
    );
    echo json_encode(['success' => $stmt->execute()]);
    exit;
}

// ─── DELETE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'delete') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM abonnement WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo json_encode(['success' => $stmt->execute()]);
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
$conn->close();
?>