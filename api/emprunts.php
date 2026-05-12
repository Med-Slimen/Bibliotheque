<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$conn   = getConnection();

// Auto-update retards
$conn->query("UPDATE emprunt SET statut='en_retard' WHERE statut='en_cours' AND date_retour_prevue < CURDATE()");

// ─── TOUS LES EMPRUNTS (admin / bibliothécaire) ───────────
if ($method === 'GET' && $action === 'all') {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['administrateur', 'bibliothecaire'])) {
        echo json_encode(['error' => 'Accès non autorisé']);
        exit;
    }
    $statut = $_GET['statut'] ?? '';
    $sql = "SELECT e.*,
                d.titre, d.type_doc,
                u.nom AS nom_adherent, u.prenom AS prenom_adherent, u.email
            FROM emprunt e
            JOIN document    d ON e.code_doc    = d.code_doc
            JOIN utilisateur u ON e.id_adherent = u.id
            WHERE 1=1";

    if ($statut) {
        $sql .= " AND e.statut = '" . $conn->real_escape_string($statut) . "'";
    }
    $sql .= " ORDER BY e.date_emprunt DESC";
    echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
    exit;
}

// ─── HISTORIQUE D'UN ADHERENT ─────────────────────────────
if ($method === 'GET' && $action === 'historique') {
    $requestedId = (int)($_GET['id'] ?? $_SESSION['user_id'] ?? 0);
    $role = $_SESSION['user_role'] ?? '';
    // Adherents can only see their own history
    if ($role === 'adherent') {
        $uid = (int)($_SESSION['user_id'] ?? 0);
    } else {
        $uid = $requestedId ?: (int)($_SESSION['user_id'] ?? 0);
    }
    $stmt = $conn->prepare("
        SELECT e.*, d.titre, d.type_doc
        FROM emprunt e
        JOIN document d ON e.code_doc = d.code_doc
        WHERE e.id_adherent = ?
        ORDER BY e.date_emprunt DESC
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    exit;
}

// ─── STATS ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $s = [];
    $s['en_cours']      = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_cours'")->fetch_assoc()['c'];
    $s['en_retard']     = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='en_retard'")->fetch_assoc()['c'];
    $s['retournes_auj'] = $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut='retourne' AND date_retour_effective = CURDATE()")->fetch_assoc()['c'];
    $s['total']         = $conn->query("SELECT COUNT(*) c FROM emprunt")->fetch_assoc()['c'];
    echo json_encode($s);
    exit;
}

// ─── EMPRUNTER ────────────────────────────────────────────
if ($method === 'POST' && $action === 'emprunter') {
    $data        = json_decode(file_get_contents('php://input'), true);
    $id_adherent = (int)($data['id_adherent'] ?? $_SESSION['user_id'] ?? 0);
    $code_doc    = (int)($data['code_doc'] ?? 0);

    if (!$id_adherent || !$code_doc) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit;
    }

    // Vérifier abonnement actif
    $ab = $conn->prepare("
        SELECT id FROM abonnement
        WHERE id_adherent = ?
        AND date_debut <= CURDATE()
        AND date_fin    >= CURDATE()
    ");
    $ab->bind_param("i", $id_adherent);
    $ab->execute();
    if (!$ab->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => "Cet adhérent n'a pas d'abonnement actif"]);
        exit;
    }

    // Vérifier disponibilité
    $dispo = $conn->prepare("
        SELECT (nombre_exemplaires_acquis - nombre_exemplaires_pretes) AS dispo
        FROM document WHERE code_doc = ?
    ");
    $dispo->bind_param("i", $code_doc);
    $dispo->execute();
    $d = $dispo->get_result()->fetch_assoc();
    if (!$d || $d['dispo'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Aucun exemplaire disponible']);
        exit;
    }

    // Vérifier pas déjà emprunté
    $chk = $conn->prepare("
        SELECT id FROM emprunt
        WHERE id_adherent = ? AND code_doc = ?
        AND statut IN('en_cours','en_retard')
    ");
    $chk->bind_param("ii", $id_adherent, $code_doc);
    $chk->execute();
    if ($chk->get_result()->fetch_assoc()) {
        echo json_encode(['success' => false, 'message' => 'Ce document est déjà emprunté par cet adhérent']);
        exit;
    }

    $date_retour = date('Y-m-d', strtotime('+14 days'));
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO emprunt (id_adherent, code_doc, date_emprunt, date_retour_prevue)
            VALUES (?, ?, CURDATE(), ?)
        ");
        $stmt->bind_param("iis", $id_adherent, $code_doc, $date_retour);
        $stmt->execute();

        $conn->query("
            UPDATE document
            SET nombre_exemplaires_pretes = nombre_exemplaires_pretes + 1
            WHERE code_doc = $code_doc
        ");

        $conn->commit();
        echo json_encode(['success' => true, 'date_retour' => $date_retour]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── RETOURNER ────────────────────────────────────────────
if ($method === 'POST' && $action === 'retourner') {
    $data       = json_decode(file_get_contents('php://input'), true);
    $id_emprunt = (int)($data['id_emprunt'] ?? 0);

    if (!$id_emprunt) {
        echo json_encode(['success' => false, 'message' => 'ID emprunt manquant']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Récupérer le code_doc
        $get = $conn->prepare("SELECT code_doc FROM emprunt WHERE id = ?");
        $get->bind_param("i", $id_emprunt);
        $get->execute();
        $row = $get->get_result()->fetch_assoc();
        if (!$row) throw new Exception("Emprunt introuvable");
        $code_doc = $row['code_doc'];

        // Mettre à jour l'emprunt
        $upd = $conn->prepare("
            UPDATE emprunt
            SET date_retour_effective = CURDATE(), statut = 'retourne'
            WHERE id = ?
        ");
        $upd->bind_param("i", $id_emprunt);
        $upd->execute();

        // Décrémenter exemplaires prêtés
        $conn->query("
            UPDATE document
            SET nombre_exemplaires_pretes = GREATEST(0, nombre_exemplaires_pretes - 1)
            WHERE code_doc = $code_doc
        ");

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
$conn->close();
?>