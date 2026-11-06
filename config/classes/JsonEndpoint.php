<?php
/**
 * For ajax endpoints that must always answer with JSON (the upload pages read the reply with JSON.parse).
 * Without this, a PHP fatal error, an uncaught database exception or a stray echo (e.g. DB.php's "Connection failed")
 * sends HTML or nothing, and the page can only say "Network or server error".
 *
 *   JsonEndpoint::start();            first thing in the endpoint
 *   JsonEndpoint::reply([...]);       sends the JSON and stops
 * Errors are written to the PHP error log and returned as {ok: false, message: "..."}.
 */
class JsonEndpoint
{
    private static $done = false;

    public static function start()
    {
        ob_start();
        set_exception_handler(function ($e) {
            error_log(basename($_SERVER['SCRIPT_NAME']) . ': ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            self::reply(['ok' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        });
        register_shutdown_function(function () {
            if (self::$done) {
                return;
            }
            $err = error_get_last();
            $stray = trim(strip_tags((string) ob_get_contents()));
            $why = $err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR], true)
                ? $err['message'] . ' (' . basename($err['file']) . ' line ' . $err['line'] . ')'
                : ($stray !== '' ? $stray : 'the script stopped without a reply');
            error_log(basename($_SERVER['SCRIPT_NAME']) . ': ' . $why);
            self::reply(['ok' => false, 'message' => 'Server error: ' . mb_substr($why, 0, 400)]);
        });
    }

    public static function reply($data)
    {
        self::$done = true;
        $stray = ob_get_level() ? ob_get_clean() : '';
        if (trim((string) $stray) !== '') {
            error_log(basename($_SERVER['SCRIPT_NAME']) . ': unexpected output: ' . mb_substr(trim(strip_tags($stray)), 0, 300));
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        // Names with characters that are not valid UTF-8 must not make the whole reply fail
        $flags = defined('JSON_INVALID_UTF8_SUBSTITUTE') ? JSON_INVALID_UTF8_SUBSTITUTE : 0;
        $json = json_encode($data, $flags | JSON_PARTIAL_OUTPUT_ON_ERROR);
        echo $json !== false ? $json : json_encode(['ok' => false, 'message' => 'Server error: the reply could not be encoded (' . json_last_error_msg() . ').']);
        exit();
    }

    // Upload errors in words (e.g. the server's upload_max_filesize is smaller than the file)
    public static function uploadError($file)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES) && empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            return 'The file is larger than this server accepts (post_max_size ' . ini_get('post_max_size') . ').';
        }
        if (!$file || !isset($file['error'])) {
            return 'Choose the CSV file first.';
        }
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                return is_uploaded_file($file['tmp_name']) ? null : 'The upload failed. Please try again.';
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The file is larger than this server accepts (upload_max_filesize ' . ini_get('upload_max_filesize') . ').';
            case UPLOAD_ERR_PARTIAL:
                return 'The file was only partly uploaded. Please try again.';
            case UPLOAD_ERR_NO_FILE:
                return 'Choose the CSV file first.';
            case UPLOAD_ERR_NO_TMP_DIR:
            case UPLOAD_ERR_CANT_WRITE:
                return 'The server could not store the upload (temporary folder missing or not writable). Ask the hosting provider.';
            default:
                return 'The upload was blocked by the server (error ' . (int) $file['error'] . ').';
        }
    }

    // Stops with a clear message when a database change the code needs has not been run yet
    public static function requireColumn($table, $column, $migration)
    {
        $con = (new DB())->getConnection();
        if (!$con) {
            self::reply(['ok' => false, 'message' => 'Server error: could not connect to the database.']);
        }
        $stmt = $con->prepare("SHOW COLUMNS FROM `$table` LIKE :c");
        $stmt->execute([':c' => $column]);
        if (!$stmt->fetch()) {
            self::reply(['ok' => false, 'message' => "The database is not up to date: $table.$column is missing. Run $migration on the live database (phpMyAdmin), then try again."]);
        }
    }
}
