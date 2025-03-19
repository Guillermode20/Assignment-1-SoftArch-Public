<?php

declare(strict_types=1);

if (!function_exists('getDbConnection')) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed.']);
    exit();
}


error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Class Content.
 *
 * Provides methods for retrieving content data from the database.
 */
class Content {
    /**
     * @var PDO The database connection.
     */
    private PDO $db;

    /**
     * Content constructor.
     *
     * Initializes the Content object with a database connection.
     */
    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Retrieves content from the database based on specified filters.
     *
     * @param int|null    $content_id (Optional) The ID of the content to retrieve.
     * @param int|null    $author_id  (Optional) The ID of the author to retrieve content for.
     * @param string|null $search     (Optional) A search term to filter content by title or abstract.
     * @param int|null    $page       (Optional) The page number for pagination.
     *
     * @return array An array of content data, where each element is an associative array containing content details.
     *
     * @throws Exception If a database query fails or content is not found.
     */
    public function getContent(?int $content_id = null, ?int $author_id = null, ?string $search = null, ?int $page = null): array {
        $params = [];

        try {
            $sql = 'SELECT DISTINCT
                    c.id AS content_id,
                    c.title,
                    c.abstract,
                    c.doi_link,
                    c.preview_video,
                    t.name AS type,
                    a.name AS award
                FROM content c
                INNER JOIN type t ON c.type = t.id
                LEFT JOIN content_has_award cha ON c.id = cha.content
                LEFT JOIN award a ON cha.award = a.id';

            $where_clauses = [];

            if ($content_id !== null) {
                $where_clauses[] = 'c.id = :content_id';
                $params[':content_id'] = $content_id;
            }

            if ($author_id !== null) {
                $sql .= ' INNER JOIN content_has_author ca ON c.id = ca.content';
                $where_clauses[] = 'ca.author = :author_id';
                $params[':author_id'] = $author_id;
            }

            if ($search !== null) {
                $where_clauses[] = '(LOWER(c.title) LIKE LOWER(:search) OR LOWER(c.abstract) LIKE LOWER(:search))';
                $params[':search'] = '%' . $search . '%';
            }

            if (!empty($where_clauses)) {
                $sql .= ' WHERE ' . implode(' AND ', $where_clauses);
            }

            $sql .= ' ORDER BY c.title';

            if ($page !== null) {
                $offset = ($page - 1) * 10;
                $sql .= ' LIMIT 10 OFFSET :offset';
                $params[':offset'] = $offset;
            } else {
                $sql .= ' LIMIT 10';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($content_id !== null && empty($result)) {
                throw new Exception('Content not found');
            }

            $content = [];
            foreach ($result as $row) {
                $content[] = [
                    'content_id' => (int) $row['content_id'],
                    'title' => $row['title'],
                    'abstract' => $row['abstract'] ?: null,
                    'doi_link' => $row['doi_link'] ?: null,
                    'preview_video' => $row['preview_video'] ?: null,
                    'type' => $row['type'],
                    'award' => $row['award'] ?: null,
                ];
            }

            return $content;
        } catch (PDOException $e) {
            error_log('Database query failed: ' . $e->getMessage());
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }
}

try {
    $content_id = isset($_GET['content_id']) ? (int) $_GET['content_id'] : null;
    $author_id = isset($_GET['author_id']) ? (int) $_GET['author_id'] : null;
    $search = $_GET['search'] ?? null;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : null;

    if (isset($_GET['content_id']) && !is_numeric($_GET['content_id'])) {
        throw new Exception('Invalid content_id. Must be an integer.');
    }

    if (isset($_GET['author_id']) && !is_numeric($_GET['author_id'])) {
        throw new Exception('Invalid author_id. Must be an integer.');
    }

    if ($page !== null && $page < 1) {
        throw new Exception('Invalid page. Must be a positive integer.');
    }

    $content = new Content();
    $result = $content->getContent($content_id, $author_id, $search, $page);

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($result);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>