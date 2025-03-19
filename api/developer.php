<?php
declare(strict_types=1);

/**
 * Class Developer.
 *
 * Represents a developer and provides methods to retrieve developer information.
 */
class Developer {
    /**
     * @var string The student ID of the developer.
     */
    private string $student_id = 'W23003084';
    /**
     * @var string The name of the developer.
     */
    private string $name       = 'Will Hick';

    /**
     * Retrieves the developer's information.
     *
     * @return array An array containing the developer's student ID and name.
     *
     * @throws Exception If an error occurs during retrieval.
     */
    public function getResponse(): array {
        try {
            return [
                'student_id' => $this->student_id,
                'name'       => $this->name
            ];
        } catch (Exception $e) {
            error_log('Failed to get developer info: ' . $e->getMessage());
            throw new Exception('Failed to get developer info: ' . $e->getMessage());
        }
    }
}

try {
    $developer = new Developer();
    $response = $developer->getResponse();

    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($response);
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>