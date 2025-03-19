<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Class AwardManager.
 *
 * Manages the assignment and removal of awards to content items.
 */
class AwardManager {
    /**
     * @var PDO The database connection.
     */
    private PDO $db;

    /**
     * AwardManager constructor.
     *
     * Initializes the AwardManager object with a database connection.
     */
    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Assigns an award to a content item.
     *
     * @param int $content_id The ID of the content item.
     * @param int $award_id   The ID of the award to assign.
     *
     * @throws Exception If the database query fails or if the content already has an award assigned.
     */
    public function assignAward(int $content_id, int $award_id): void {
        try {
            // Check if content already has an award
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM content_has_award WHERE content = ?');
            $stmt->execute([$content_id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                throw new Exception('Content already has an award assigned.');
            }

            $stmt = $this->db->prepare('INSERT INTO content_has_award (content, award) VALUES (?, ?)');
            $stmt->execute([$content_id, $award_id]);
        } catch (PDOException $e) {
            error_log('Failed to assign award: ' . $e->getMessage());
            throw new Exception('Failed to assign award: ' . $e->getMessage());
        }
    }

    /**
     * Removes an award from a content item.
     *
     * @param int $content_id The ID of the content item from which to remove the award.
     *
     * @throws Exception If the database query fails or if no award is found for the given content ID.
     */
    public function removeAward(int $content_id): void {
        try {
            $stmt = $this->db->prepare('DELETE FROM content_has_award WHERE content = ?');
            $stmt->execute([$content_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('No award found for the given content_id');
            }
        } catch (PDOException $e) {
            error_log('Failed to remove award: ' . $e->getMessage());
            throw new Exception('Failed to remove award: ' . $e->getMessage());
        }
    }
}

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $awardManager = new AwardManager();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'POST':
            if (!isset($data['content_id'], $data['award_id'])) {
                throw new Exception('Missing parameter: content_id and/or award_id');
            }
            if (!is_numeric($data['content_id']) || !is_numeric($data['award_id'])) {
                throw new Exception('Invalid parameter: content_id and award_id must be integers');
            }
            
            $awardManager->assignAward((int)$data['content_id'], (int)$data['award_id']);
            http_response_code(201);
            echo json_encode(['message' => 'Award assigned successfully']);
            break;

        case 'DELETE':
            if (!isset($data['content_id'])) {
                throw new Exception('Missing parameter: content_id');
            }
            if (!is_numeric($data['content_id'])) {
                throw new Exception('Invalid parameter: content_id must be an integer');
            }

            $awardManager->removeAward((int)$data['content_id']);
            http_response_code(200);
            echo json_encode(['message' => 'Award removed successfully']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            break;
    }
} catch (Exception $e) {
    $code = $e->getMessage() === 'No award found for the given content_id' ? 404 : 400;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
?>