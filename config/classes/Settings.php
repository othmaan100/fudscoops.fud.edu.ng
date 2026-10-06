<?php
require_once('DB.php');

// Key/value cooperative settings stored in fudscoops_settings (see db/2026-10-06_fudscoops_settings.sql)
class Settings{

    private static $cache = [];

    public static function get($key, $default = null) {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $value = $default;
        try {
            $con = (new DB())->getConnection();
            $stmt = $con->prepare("SELECT setting_value FROM fudscoops_settings WHERE setting_key = :setting_key");
            $stmt->bindParam(':setting_key', $key, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $value = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Table missing or database error: fall back to the default
            error_log("Settings::get($key): " . $e->getMessage());
        }

        self::$cache[$key] = $value;
        return $value;
    }

    public static function set($key, $value, $updatedBy) {
        try {
            $con = (new DB())->getConnection();
            $stmt = $con->prepare("
                INSERT INTO fudscoops_settings (setting_key, setting_value, updated_by, updated_at)
                VALUES (:setting_key, :setting_value, :updated_by, NOW())
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value),
                    updated_by = VALUES(updated_by), updated_at = VALUES(updated_at)
            ");
            $stmt->bindParam(':setting_key', $key, PDO::PARAM_STR);
            $stmt->bindParam(':setting_value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':updated_by', $updatedBy, PDO::PARAM_STR);
            $stmt->execute();
            self::$cache[$key] = (string) $value;
            return true;
        } catch (Exception $e) {
            error_log("Settings::set($key): " . $e->getMessage());
            return false;
        }
    }

    // Who last changed a setting and when, e.g. ['updated_by' => 'SP/RA/2965', 'updated_at' => '2026-10-06 10:00:00']
    public static function lastUpdate($key) {
        try {
            $con = (new DB())->getConnection();
            $stmt = $con->prepare("SELECT updated_by, updated_at FROM fudscoops_settings WHERE setting_key = :setting_key");
            $stmt->bindParam(':setting_key', $key, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
