<?php
/**
 * Isolated PHP Request Executor for Functional Testing
 * Simulates a full web server request environment in an isolated CLI process.
 */

// Force SQLite for instant local execution
putenv('DB_CONNECTION=sqlite');
$_ENV['DB_CONNECTION'] = 'sqlite';

// Read config from stdin or command argument file
$options = getopt('', ['spec:']);
$specFile = $options['spec'] ?? null;

if (!$specFile || !file_exists($specFile)) {
    echo json_encode(['error' => 'Missing or invalid spec file']);
    exit(1);
}

$spec = json_decode(file_get_contents($specFile), true);
if (!$spec) {
    echo json_encode(['error' => 'Invalid JSON in spec file']);
    exit(1);
}

// Setup environment
$method = strtoupper($spec['method'] ?? 'GET');
$script = $spec['script'];
$server = $spec['server'] ?? [];
$get = $spec['get'] ?? [];
$post = $spec['post'] ?? [];
$input = $spec['input'] ?? null;
$files = $spec['files'] ?? [];
$session = $spec['session'] ?? [];

$_SERVER = array_merge([
    'REQUEST_METHOD' => $method,
    'REQUEST_URI'    => $spec['uri'] ?? ('/' . $script),
    'SERVER_NAME'    => 'localhost',
    'HTTP_HOST'      => 'localhost',
    'REMOTE_ADDR'    => '127.0.0.1',
    'SERVER_PORT'    => '80',
    'HTTPS'          => 'off',
    'SCRIPT_FILENAME'=> realpath($script) ?: $script,
    'SCRIPT_NAME'    => '/' . $script,
    'PHP_SELF'       => '/' . $script,
], $server);

$_GET = $get;
$_POST = $post;
$_FILES = $files;
if (!empty($session)) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION = $session;
    session_write_close();
}

// Mock php://input if input is provided
if ($input !== null) {
    class TestPhpStream {
        public $context;
        public static $buffer = '';
        private $pos = 0;
        public function stream_open($path, $mode, $options, &$opened_path) {
            $this->pos = 0;
            return true;
        }
        public function stream_read($count) {
            $chunk = substr(self::$buffer, $this->pos, $count);
            $this->pos += strlen($chunk);
            return $chunk;
        }
        public function stream_eof() {
            return $this->pos >= strlen(self::$buffer);
        }
        public function stream_stat() {
            return [];
        }
    }
    TestPhpStream::$buffer = is_string($input) ? $input : json_encode($input);
    @stream_wrapper_unregister('php');
    @stream_wrapper_register('php', 'TestPhpStream');
}

// Capture response headers and output
ob_start();
http_response_code(200);

// Register shutdown function to capture output when exit is called
register_shutdown_function(function() use ($specFile) {
    $output = ob_get_clean();
    $statusCode = http_response_code() ?: 200;
    $headers = headers_list();

    $jsonDecoded = json_decode($output, true);

    $result = [
        'statusCode' => $statusCode,
        'headers'    => $headers,
        'body'       => $output,
        'json'       => $jsonDecoded
    ];

    $outFile = $specFile . '.out.json';
    file_put_contents($outFile, json_encode($result, JSON_UNESCAPED_UNICODE));
});

// Execute the target script
chdir(dirname(__DIR__));
require $script;
