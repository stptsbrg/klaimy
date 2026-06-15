<?php
namespace App\Models;

use App\Core\Model;
use App\Core\App;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function createUser(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        $data['password_hash'] = password_hash($data['password'], PASSWORD_ARGON2ID);
        unset($data['password']);
        return $this->create($data);
    }

    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function incrementLoginAttempts(int $userId): void
    {
        $config = App::getInstance()->config('security');
        $this->db->query(
            "UPDATE users SET login_attempts = login_attempts + 1,
             locked_until = IF(login_attempts + 1 >= ?, DATE_ADD(NOW(), INTERVAL ? SECOND), locked_until)
             WHERE id = ?",
            [$config['max_login_attempts'], $config['lockout_duration'], $userId]
        );
    }

    public function resetLoginAttempts(int $userId): void
    {
        $this->update($userId, [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }
        return strtotime($user['locked_until']) > time();
    }

    public function needsTwoFactor(array $user): bool
    {
        if (empty($user['last_login_at'])) {
            return true;
        }
        $daysSinceLogin = (time() - strtotime($user['last_login_at'])) / 86400;
        return $daysSinceLogin > App::getInstance()->config('security.two_factor_inactivity_days', 7);
    }

    public function generateTwoFactorCode(int $userId): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = App::getInstance()->config('security.two_factor_expiry', 600);
        $this->update($userId, [
            'two_factor_code' => password_hash($code, PASSWORD_ARGON2ID),
            'two_factor_expires_at' => date('Y-m-d H:i:s', time() + $expiry),
        ]);
        return $code;
    }

    public function verifyTwoFactorCode(int $userId, string $code): bool
    {
        $user = $this->find($userId);
        if (!$user || empty($user['two_factor_code'])) {
            return false;
        }
        if (strtotime($user['two_factor_expires_at']) < time()) {
            return false;
        }
        return password_verify($code, $user['two_factor_code']);
    }

    public function generatePasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update($userId, [
            'password_reset_token' => hash('sha256', $token),
            'password_reset_expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        return $token;
    }

    public function findByResetToken(string $token): ?array
    {
        $hash = hash('sha256', $token);
        return $this->db->fetch(
            "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires_at > NOW()",
            [$hash]
        );
    }

    public function resetPassword(int $userId, string $newPassword): void
    {
        $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_ARGON2ID),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function getCompanyUsers(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT id, uuid, first_name, last_name, email, phone, role, is_active, last_login_at, created_at
             FROM users WHERE company_id = ? ORDER BY created_at DESC",
            [$companyId]
        );
    }

    public function updateLastActivity(int $userId): void
    {
        $this->db->query(
            "UPDATE users SET last_activity_at = NOW() WHERE id = ?",
            [$userId]
        );
    }
}
