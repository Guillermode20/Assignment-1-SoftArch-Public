<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Class Award.
 *
 * Represents an award and provides methods for managing award data in the database.
 */
class Award {
    /**
     * @var PDO The database connection.
     */
    private PDO $db;

    /**
     * Award constructor.
     *
     * Initializes the Award object by establishing a database connection.
     */
    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Retrieves all awards from the database.
     *
     * @return array An array of award data, where each element is an associative array containing 'award_id' and 'name'.
     *
     * @throws Exception If the database query fails.
     */
    public function getAwards(): array {
        try {
            $stmt = $this->db->prepare('SELECT id, name FROM award ORDER BY id');
            $stmt->execute();
            
            $awards = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $awards[] = [
                    'award_id' => (int) $row['id'],
                    'name'     => $row['name']
                ];
            }
            return $awards;
        } catch (PDOException $e) {
            error_log('Failed to get awards: ' . $e->getMessage());
            throw new Exception('Failed to get awards: ' . $e->getMessage());
        }
    }

    /**
     * Creates a new award in the database.
     *
     * @param string $name The name of the award to create.
     *
     * @throws Exception If the database query fails or the award name already exists.
     */
    public function createAward(string $name): void {
        try {
            // First get the current highest ID
            $stmt = $this->db->query('SELECT MAX(id) FROM award');
            $maxId = (int) $stmt->fetchColumn();
            $nextId = $maxId + 1;

            // Now insert with the next sequential ID
            $stmt = $this->db->prepare('INSERT INTO award (id, name) VALUES (?, ?)');
            $stmt->execute([$nextId, $name]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new Exception('Award name already exists');
            }
            error_log('Failed to create award: ' . $e->getMessage());
            throw new Exception('Failed to create award: ' . $e->getMessage());
        }
    }

    /**
     * Updates an existing award in the database.
     *
     * @param int    $award_id The ID of the award to update.
     * @param string $name     The new name for the award.
     *
     * @throws Exception If the database query fails, the award name already exists, or the award is not found.
     */
    public function updateAward(int $award_id, string $name): void {
        try {
            $stmt = $this->db->prepare('UPDATE award SET name = ? WHERE id = ?');
            $stmt->execute([$name, $award_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Award not found');
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                throw new Exception('Award name already exists');
            }
            error_log('Failed to update award: ' . $e->getMessage());
            throw new Exception('Failed to update award: ' . $e->getMessage());
        }
    }

    /**
     * Deletes an award from the database.
     *
     * @param int $award_id The ID of the award to delete.
     *
     * @throws Exception If the database query fails or the award is not found.
     */
    public function deleteAward(int $award_id): void {
        try {
            $stmt = $this->db->prepare('DELETE FROM award WHERE id = ?');
            $stmt->execute([$award_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Award not found');
            }
        } catch (PDOException $e) {
            error_log('Failed to delete award: ' . $e->getMessage());
            throw new Exception('Failed to delete award: ' . $e->getMessage());
        }
    }
}

try {
    $award = new Award();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $awards = $award->getAwards();
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($awards);
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['name'])) {
                throw new Exception('Missing parameter: name');
            }

            $award->createAward($data['name']);
            header('Content-Type: application/json');
            http_response_code(201);
            break;

        case 'PATCH':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['award_id'], $data['name'])) {
                throw new Exception('Missing parameter(s): award_id and/or name');
            }
            if (!is_numeric($data['award_id'])) {
                throw new Exception('Invalid award_id. Must be an integer.');
            }

            $award->updateAward((int)$data['award_id'], $data['name']);
            header('Content-Type: application/json');
            http_response_code(204);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['award_id'])) {
                throw new Exception('Missing parameter: award_id');
            }
            if (!is_numeric($data['award_id'])) {
                throw new Exception('Invalid award_id. Must be an integer.');
            }

            $award->deleteAward((int)$data['award_id']);
            header('Content-Type: application/json');
            http_response_code(204);
            break;

        default:
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            break;
    }
} catch (Exception $e) {
    header('Content-Type: application/json');
    $code = in_array($e->getMessage(), ['Award not found', 'Award name already exists']) ? 404 : 400;
    http_response_code($code);
    echo json_encode(['error' => $e->getMessage()]);
}
?>