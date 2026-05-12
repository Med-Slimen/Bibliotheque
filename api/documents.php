<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../config/database.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$conn   = getConnection();

// Auth check for write operations
if ($method === 'POST' && !isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

// Auto-update retards
$conn->query("UPDATE emprunt SET statut='en_retard' WHERE statut='en_cours' AND date_retour_prevue < CURDATE()");

// ─── LIST / SEARCH ────────────────────────────────────────
if ($method === 'GET' && $action === 'list') {
    $q    = '%' . ($_GET['q'] ?? '') . '%';
    $type = $_GET['type'] ?? '';

    $sql = "SELECT d.*,
                GROUP_CONCAT(DISTINCT CONCAT(a.prenom,' ',a.nom) SEPARATOR ', ') AS auteurs,
                me.raison_social AS editeur,
                l.isbn, l.genre,
                r.periodicite, r.issn, r.montant_abonnement
            FROM document d
            LEFT JOIN document_auteur da ON d.code_doc = da.code_doc
            LEFT JOIN auteur a           ON da.id_auteur = a.id
            LEFT JOIN maison_edition me  ON d.id_edition = me.id_edition
            LEFT JOIN livre l            ON d.code_doc = l.code_doc
            LEFT JOIN revue r            ON d.code_doc = r.code_doc
            WHERE (d.titre LIKE ? OR d.mots_cles LIKE ? OR CONCAT(a.prenom,' ',a.nom) LIKE ?)";

    $params = [$q, $q, $q];
    $types  = "sss";

    if ($type) {
        $sql   .= " AND d.type_doc = ?";
        $params[] = $type;
        $types .= "s";
    }

    $sql .= " GROUP BY d.code_doc ORDER BY d.titre";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    exit;
}

// ─── DETAIL ───────────────────────────────────────────────
if ($method === 'GET' && $action === 'detail') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("
        SELECT d.*,
            GROUP_CONCAT(DISTINCT CONCAT(a.prenom,' ',a.nom) SEPARATOR ', ') AS auteurs,
            GROUP_CONCAT(DISTINCT a.id SEPARATOR ',') AS auteur_ids,
            me.raison_social AS editeur, me.id_edition,
            l.isbn, l.genre,
            r.periodicite, r.issn, r.date_abonnement, r.montant_abonnement
        FROM document d
        LEFT JOIN document_auteur da ON d.code_doc = da.code_doc
        LEFT JOIN auteur a           ON da.id_auteur = a.id
        LEFT JOIN maison_edition me  ON d.id_edition = me.id_edition
        LEFT JOIN livre l            ON d.code_doc = l.code_doc
        LEFT JOIN revue r            ON d.code_doc = r.code_doc
        WHERE d.code_doc = ?
        GROUP BY d.code_doc
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode($stmt->get_result()->fetch_assoc());
    exit;
}

// ─── STATS ────────────────────────────────────────────────
if ($method === 'GET' && $action === 'stats') {
    $stats = [];
    $stats['total']          = $conn->query("SELECT COUNT(*) c FROM document")->fetch_assoc()['c'];
    $stats['livres']         = $conn->query("SELECT COUNT(*) c FROM livre")->fetch_assoc()['c'];
    $stats['revues']         = $conn->query("SELECT COUNT(*) c FROM revue")->fetch_assoc()['c'];
    $stats['emprunts_actifs']= $conn->query("SELECT COUNT(*) c FROM emprunt WHERE statut IN('en_cours','en_retard')")->fetch_assoc()['c'];
    echo json_encode($stats);
    exit;
}

// ─── CREATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents('php://input'), true);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO document (titre, date_parution, nombre_exemplaires_acquis, mots_cles, type_doc, id_edition)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssissi",
            $data['titre'],
            $data['date_parution'],
            $data['nombre_exemplaires_acquis'],
            $data['mots_cles'],
            $data['type_doc'],
            $data['id_edition']
        );
        $stmt->execute();
        $code = $conn->insert_id;

        if ($data['type_doc'] === 'livre') {
            $s2 = $conn->prepare("INSERT INTO livre (code_doc, isbn, genre) VALUES (?, ?, ?)");
            $s2->bind_param("iss", $code, $data['isbn'], $data['genre']);
            $s2->execute();
        } else {
            $s2 = $conn->prepare("INSERT INTO revue (code_doc, periodicite, date_abonnement, montant_abonnement, issn) VALUES (?, ?, ?, ?, ?)");
            $s2->bind_param("issds", $code, $data['periodicite'], $data['date_abonnement'], $data['montant_abonnement'], $data['issn']);
            $s2->execute();
        }

        if (!empty($data['auteur_ids'])) {
            $sa = $conn->prepare("INSERT IGNORE INTO document_auteur (code_doc, id_auteur) VALUES (?, ?)");
            foreach ($data['auteur_ids'] as $aid) {
                $sa->bind_param("ii", $code, $aid);
                $sa->execute();
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'id' => $code]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── UPDATE ───────────────────────────────────────────────
if ($method === 'POST' && $action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            UPDATE document
            SET titre = ?, date_parution = ?, nombre_exemplaires_acquis = ?, mots_cles = ?, id_edition = ?
            WHERE code_doc = ?
        ");
        $stmt->bind_param("ssisii",
            $data['titre'],
            $data['date_parution'],
            $data['nombre_exemplaires_acquis'],
            $data['mots_cles'],
            $data['id_edition'],
            $data['code_doc']
        );
        $stmt->execute();

        if ($data['type_doc'] === 'livre') {
            $s2 = $conn->prepare("UPDATE livre SET isbn = ?, genre = ? WHERE code_doc = ?");
            $s2->bind_param("ssi", $data['isbn'], $data['genre'], $data['code_doc']);
            $s2->execute();
        } else {
            $s2 = $conn->prepare("UPDATE revue SET periodicite = ?, date_abonnement = ?, montant_abonnement = ?, issn = ? WHERE code_doc = ?");
            $s2->bind_param("ssdsi", $data['periodicite'], $data['date_abonnement'], $data['montant_abonnement'], $data['issn'], $data['code_doc']);
            $s2->execute();
        }

        // Mettre à jour les auteurs
        if (isset($data['auteur_ids'])) {
            $conn->query("DELETE FROM document_auteur WHERE code_doc = " . (int)$data['code_doc']);
            $sa = $conn->prepare("INSERT IGNORE INTO document_auteur (code_doc, id_auteur) VALUES (?, ?)");
            foreach ($data['auteur_ids'] as $aid) {
                $sa->bind_param("ii", $data['code_doc'], $aid);
                $sa->execute();
            }
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

    // Vérifier si document emprunté
    $check = $conn->prepare("SELECT COUNT(*) c FROM emprunt WHERE code_doc = ? AND statut IN('en_cours','en_retard')");
    $check->bind_param("i", $id);
    $check->execute();
    $count = $check->get_result()->fetch_assoc()['c'];

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer : document actuellement emprunté']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM document WHERE code_doc = ?");
    $stmt->bind_param("i", $id);
    echo json_encode(['success' => $stmt->execute()]);
    exit;
}

echo json_encode(['error' => 'Action inconnue']);
$conn->close();
?>