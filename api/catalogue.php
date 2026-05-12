<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$entity = $_GET['entity'] ?? 'auteur';
$conn   = getConnection();

// ══════════════════════════════════════════
//  AUTEURS
// ══════════════════════════════════════════
if ($entity === 'auteur') {

    // ─── LIST ─────────────────────────────
    if ($method === 'GET' && $action === 'list') {
        $sql = "SELECT a.*,
                    COUNT(da.code_doc) AS nb_documents
                FROM auteur a
                LEFT JOIN document_auteur da ON a.id = da.id_auteur
                GROUP BY a.id
                ORDER BY a.nom";
        echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
        exit;
    }

    // ─── DETAIL ───────────────────────────
    if ($method === 'GET' && $action === 'detail') {
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM auteur WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());
        exit;
    }

    // ─── CREATE ───────────────────────────
    if ($method === 'POST' && $action === 'create') {
        $d = json_decode(file_get_contents('php://input'), true);

        if (empty($d['nom']) || empty($d['prenom'])) {
            echo json_encode(['success' => false, 'message' => 'Nom et prénom sont requis']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO auteur
                (nom, prenom, adresse_travail, origine, centre_interet1, centre_interet2, centre_interet3)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss",
            $d['nom'],
            $d['prenom'],
            $d['adresse_travail']  ?? '',
            $d['origine']          ?? '',
            $d['centre_interet1']  ?? '',
            $d['centre_interet2']  ?? '',
            $d['centre_interet3']  ?? ''
        );
        echo json_encode(['success' => $stmt->execute(), 'id' => $conn->insert_id]);
        exit;
    }

    // ─── UPDATE ───────────────────────────
    if ($method === 'POST' && $action === 'update') {
        $d    = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("
            UPDATE auteur
            SET nom = ?, prenom = ?, adresse_travail = ?,
                origine = ?, centre_interet1 = ?,
                centre_interet2 = ?, centre_interet3 = ?
            WHERE id = ?
        ");
        $stmt->bind_param("sssssssi",
            $d['nom'],
            $d['prenom'],
            $d['adresse_travail']  ?? '',
            $d['origine']          ?? '',
            $d['centre_interet1']  ?? '',
            $d['centre_interet2']  ?? '',
            $d['centre_interet3']  ?? '',
            $d['id']
        );
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }

    // ─── DELETE ───────────────────────────
    if ($method === 'POST' && $action === 'delete') {
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = (int)($d['id'] ?? 0);

        // Vérifier si l'auteur a des documents
        $chk = $conn->prepare("SELECT COUNT(*) c FROM document_auteur WHERE id_auteur = ?");
        $chk->bind_param("i", $id);
        $chk->execute();
        $count = $chk->get_result()->fetch_assoc()['c'];

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Impossible : cet auteur est lié à des documents']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM auteur WHERE id = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
}

// ══════════════════════════════════════════
//  MAISONS D'EDITION
// ══════════════════════════════════════════
if ($entity === 'edition') {

    // ─── LIST ─────────────────────────────
    if ($method === 'GET' && $action === 'list') {
        $sql = "SELECT me.*,
                    COUNT(d.code_doc) AS nb_documents
                FROM maison_edition me
                LEFT JOIN document d ON me.id_edition = d.id_edition
                GROUP BY me.id_edition
                ORDER BY me.raison_social";
        echo json_encode($conn->query($sql)->fetch_all(MYSQLI_ASSOC));
        exit;
    }

    // ─── DETAIL ───────────────────────────
    if ($method === 'GET' && $action === 'detail') {
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM maison_edition WHERE id_edition = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode($stmt->get_result()->fetch_assoc());
        exit;
    }

    // ─── CREATE ───────────────────────────
    if ($method === 'POST' && $action === 'create') {
        $d = json_decode(file_get_contents('php://input'), true);

        if (empty($d['raison_social'])) {
            echo json_encode(['success' => false, 'message' => 'La raison sociale est requise']);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO maison_edition (raison_social, adresse, nom_directeur, nom_responsable)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss",
            $d['raison_social'],
            $d['adresse']         ?? '',
            $d['nom_directeur']   ?? '',
            $d['nom_responsable'] ?? ''
        );
        echo json_encode(['success' => $stmt->execute(), 'id' => $conn->insert_id]);
        exit;
    }

    // ─── UPDATE ───────────────────────────
    if ($method === 'POST' && $action === 'update') {
        $d    = json_decode(file_get_contents('php://input'), true);
        $stmt = $conn->prepare("
            UPDATE maison_edition
            SET raison_social = ?, adresse = ?,
                nom_directeur = ?, nom_responsable = ?
            WHERE id_edition = ?
        ");
        $stmt->bind_param("ssssi",
            $d['raison_social'],
            $d['adresse']         ?? '',
            $d['nom_directeur']   ?? '',
            $d['nom_responsable'] ?? '',
            $d['id']
        );
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }

    // ─── DELETE ───────────────────────────
    if ($method === 'POST' && $action === 'delete') {
        $d  = json_decode(file_get_contents('php://input'), true);
        $id = (int)($d['id'] ?? 0);

        // Vérifier si la maison a des documents
        $chk = $conn->prepare("SELECT COUNT(*) c FROM document WHERE id_edition = ?");
        $chk->bind_param("i", $id);
        $chk->execute();
        $count = $chk->get_result()->fetch_assoc()['c'];

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => "Impossible : cette maison d'édition a des documents liés"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM maison_edition WHERE id_edition = ?");
        $stmt->bind_param("i", $id);
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }
}

echo json_encode(['error' => 'Action inconnue']);
$conn->close();
?>