<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Class Author.
 *
 * Represents an author and provides methods for retrieving author data from the database.
 */
class Author {
    /**
     * @var PDO The database connection.
     */
    private PDO $db;

    /**
     * Author constructor.
     *
     * Initializes the Author object by establishing a database connection.
     */
    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Retrieves authors from the database based on specified filters.
     *
     * @param int|null    $author_id  (Optional) The ID of the author to retrieve.
     * @param int|null    $content_id (Optional) The ID of the content to retrieve authors for.
     * @param string|null $search     (Optional) A search term to filter authors by name.
     * @param int|null    $page       (Optional) The page number for pagination.
     *
     * @return array An array of author data, where each element is an associative array containing 'author_id' and 'name'.
     *
     * @throws Exception If a database query fails.
     */
    public function getAuthors(?int $author_id = null, ?int $content_id = null, ?string $search = null, ?int $page = null): array {
        $authors = [];
        $params  = [];

        try {
            $sql = 'SELECT DISTINCT a.id, a.name FROM author a';
            $where_clauses = [];

            if ($author_id !== null) {
                $where_clauses[] = 'a.id = :author_id';
                $params[':author_id'] = $author_id;
            }

            if ($content_id !== null) {
                $sql .= ' INNER JOIN content_has_author cha ON a.id = cha.author';
                $where_clauses[] = 'cha.content = :content_id';
                $params[':content_id'] = $content_id;
            }

            if ($search !== null) {
                $where_clauses[] = 'LOWER(a.name) LIKE LOWER(:search)';
                $params[':search'] = '%' . strtolower($search) . '%';
            }

            if (!empty($where_clauses)) {
                $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
            }

            $sql .= ' ORDER BY a.name';

            if ($page !== null) {
                $offset = ($page - 1) * 10;
                $sql   .= ' LIMIT 10 OFFSET :offset';
                $params[':offset'] = $offset;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $authors[] = [
                    'author_id' => (int) $row['id'],
                    'name'      => $row['name']
                ];
            }
            return $authors;
        } catch (PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage());
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }
}

try {
    $author_id  = isset($_GET['author_id'])  ? (int) $_GET['author_id'] : null;
    $content_id = isset($_GET['content_id']) ? (int) $_GET['content_id'] : null;
    $search     = $_GET['search'] ?? null;
    $page       = isset($_GET['page']) ? (int) $_GET['page'] : null;

    if (isset($_GET['author_id']) && !is_numeric((string) $_GET['author_id'])) {
        throw new Exception('Invalid author_id. Must be an integer.');
    }

    if (isset($_GET['content_id']) && !is_numeric((string) $_GET['content_id'])) {
        throw new Exception('Invalid content_id. Must be an integer.');
    }

    if ($page !== null && $page < 1) {
        throw new Exception('Invalid page. Must be a positive integer.');
    }

    $author = new Author();
    $authors = $author->getAuthors($author_id, $content_id, $search, $page);

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($authors);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>