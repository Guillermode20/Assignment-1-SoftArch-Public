<?php
/**
 * Main entry point for the API.
 *
 * This file handles routing and request processing for the API. It defines
 * constants, establishes database connections, registers an exception handler,
 * and includes the appropriate API endpoint files based on the request URI.
 *
 * @package Assignment1
 */

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

/**
 * API Key used for authentication.
 *
 * @const string
 */
define('API_KEY', 'w23003084apikey');

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * Establishes a database connection to the SQLite database.
 *
 * @return PDO The database connection object.
 *
 * @throws PDOException If the database connection fails.
 */
function getDbConnection(): PDO
{
    $db_file = __DIR__ . '/chi2023.sqlite';
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit;
    }
}

/**
 * Global exception handler.
 *
 * Converts any unhandled exception into a JSON response with a 500 status code.
 *
 * @param Exception $e The unhandled exception.
 */
set_exception_handler(function ($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Unhandled Exception: ' . $e->getMessage()]);
});

/**
 * Class RequestHandler.
 *
 * Handles incoming requests, authenticates them, sanitizes input, and routes
 * them to the appropriate API endpoint.
 */
class RequestHandler
{
    /**
     * @var string The request URI.
     */
    private string $request_uri;

    /**
     * RequestHandler constructor.
     *
     * @param string $request_uri The URI of the request.
     */
    public function __construct(string $request_uri)
    {
        $this->request_uri = $request_uri;
    }

    /**
     * Sanitizes input data.
     *
     * @param array $data The input data to sanitize.
     *
     * @return array The sanitized data.
     */
    private function sanitizeInput(array $data): array
    {
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            $sanitizedData[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        return $sanitizedData;
    }

    /**
     * Handles the incoming request.
     *
     * Authenticates the request, sanitizes input, and includes the
     * appropriate API endpoint file.
     */
    public function handleRequest(): void
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        if ($apiKey !== API_KEY) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized: Invalid API Key']);
            exit();
        }

        $_GET = $this->sanitizeInput($_GET);

        $request_uri = rtrim(strtolower($this->request_uri), '/');
        if ($request_uri === '') {
            $request_uri = '/';
        }

        if ($request_uri === '/' || $request_uri === '/as1') {
            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode(['message' => 'Hello, World!']);
            exit();
        }

        if ($request_uri === '/api/developer') {
            include __DIR__ . '/api/developer.php';
            exit();
        }

        if ($request_uri === '/api/author') {
            include __DIR__ . '/api/author.php';
            exit();
        }

        if ($request_uri === '/api/content') {
            include __DIR__ . '/api/content.php';
            exit();
        }

        if ($request_uri === '/api/award') {
            include __DIR__ . '/api/award.php';
            exit();
        }

        if ($request_uri === '/api/manage_awards') {
            $requestMethod = $_SERVER['REQUEST_METHOD'];
            if ($requestMethod === 'POST' || $requestMethod === 'PUT' || $requestMethod === 'DELETE') {
                $data = json_decode(file_get_contents('php://input'), true) ?? [];
                $_POST = $this->sanitizeInput($data);
            }
            include __DIR__ . '/api/manage_awards.php';
            exit();
        }

        header('Content-Type: application/json');
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }
}

$request_uri = str_replace('/as1', '', $_SERVER['REQUEST_URI']);
$request_uri = $_SERVER['PATH_INFO'] ?? parse_url($request_uri, PHP_URL_PATH);

$handler = new RequestHandler($request_uri);
$handler->handleRequest();