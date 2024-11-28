<?php
namespace App\Models;
use \PDO;

class User extends Model {
    
    public function __construct() {
        parent::__construct('user');  // 'users' is the table name
        $this->primaryKey = 'user_id';

        // Define validation rules
        $this->setRules([
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true, 'minLength' => 6],
        ]);
    }


    // Add specific method for registration (with password hashing)
    public function register() {
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        // Use save method from Model
        return $this->save();
    }

    // Add specific method for login
    public function login() {
        $email = $this->email;
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        $stmt = self::$db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetchObject(self::class); //instantiate new user from result
        if ($user && password_verify($this->password, $user->password)) {
            return $user;
        }

        return null;  // Invalid credentials
    }

    public function setSession() {
        $_SESSION['user'] = [
            'user_id' => $this->user_id,
            'username' => $this->username,
            'email' => $this->email,
            'image_url' => $this->image_url,
        ];
    }

    public static function logout() {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    public static function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }

    
}
