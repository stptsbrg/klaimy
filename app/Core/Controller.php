<?php
namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$view}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Check if layout is specified
        if (isset($layout)) {
            $layoutPath = APP_PATH . '/Views/layouts/' . $layout . '.php';
            if (file_exists($layoutPath)) {
                require $layoutPath;
                return;
            }
        }

        echo $content;
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function input(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    protected function hasInput(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            $label = ucfirst(str_replace('_', ' ', $field));

            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                switch ($rule) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $errors[$field] = "{$label} est requis.";
                        }
                        break;
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "{$label} doit être un email valide.";
                        }
                        break;
                    case 'min':
                        if (!empty($value) && strlen($value) < (int)$params[0]) {
                            $errors[$field] = "{$label} doit contenir au moins {$params[0]} caractères.";
                        }
                        break;
                    case 'max':
                        if (!empty($value) && strlen($value) > (int)$params[0]) {
                            $errors[$field] = "{$label} ne doit pas dépasser {$params[0]} caractères.";
                        }
                        break;
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field] = "{$label} doit être un nombre.";
                        }
                        break;
                    case 'confirmed':
                        $confirmation = $data[$field . '_confirmation'] ?? null;
                        if ($value !== $confirmation) {
                            $errors[$field] = "La confirmation de {$label} ne correspond pas.";
                        }
                        break;
                }
            }
        }
        return $errors;
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    protected function currentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        return $_SESSION['user'] ?? null;
    }

    protected function currentCompany(): ?array
    {
        return $_SESSION['company'] ?? null;
    }

    protected function companyId(): ?int
    {
        return $_SESSION['company']['id'] ?? null;
    }

    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $tokenTime = $_SESSION['csrf_token_time'] ?? 0;
        $lifetime = App::getInstance()->config('security.csrf_token_lifetime', 3600);

        if (empty($token) || !hash_equals($sessionToken, $token)) {
            return false;
        }

        if (time() - $tokenTime > $lifetime) {
            return false;
        }

        // Regenerate token after verification
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();

        return true;
    }
}
