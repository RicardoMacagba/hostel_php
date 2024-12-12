<?php

namespace App\Models;

use \PDO;

class User extends Model
{

    public function __construct()
    {
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
    public function login()
    {
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

    public function setSession()
    {
        $_SESSION['user'] = [
            'user_id' => $this->user_id,
            'username' => $this->username,
            'email' => $this->email,
            'image_url' => $this->image_url,
        ];
    }

    public static function logout()
    {
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    public static function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Find one user by specific criteria.
     *
     * @param array $conditions Array of conditions for querying the user.
     * @return User|null Returns the user object if found, null otherwise.
     */
    public static function findOne($conditions)
    {
        $query = "SELECT * FROM users WHERE ";

        // Dynamically generate query conditions
        $params = [];
        foreach ($conditions as $key => $value) {
            $query .= "$key = :$key AND ";
            $params[$key] = $value;
        }

        // Remove trailing ' AND '
        $query = rtrim($query, ' AND ');

        // Execute the query
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);

        // Fetch the result
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? new self($data) : null;
    }

    // public function findByEmail($email)
    // {
    //     // $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    //     // $stmt->execute([':email' => $email]);

    //     $query = "INSERT INTO {$this->table} (username, email, password) VALUES (:username, :email, :password)";
    //     $stmt = self::$db->prepare($query);  // Correct use of self::$db


    //     $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch the result

    //     if (!$user) {
    //         // Optional: Log or output when no user is found
    //         error_log("No user found with email: " . $email);
    //         return;  // Return null or handle this case accordingly
    //     }

    //     return $user;  // Return the user row if found
    // }



    // public function validate()
    // {
    //     if (empty($this->username) || empty($this->email) || empty($this->password)) {
    //         return false;  // Validation failed
    //     }

    //     // Add any other custom validation rules here
    //     return true;  // Validation successful
    // }

    // public function save()
    // {
    //     if (!$this->validate()) {
    //         error_log('Validation failed for user: ' . print_r($this, true));
    //         return false;  // Abort saving if validation fails
    //     }

    //     // Proceed with saving the user if validation passes
    //     try {
    //         $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    //         $stmt->bindParam(':username', $this->username);
    //         $stmt->bindParam(':email', $this->email);
    //         $stmt->bindParam(':password', $this->password);
    //         $stmt->execute();

    //         $this->user_id = $this->db->lastInsertId();
    //         return $this;
    //     } catch (PDO $errors) {
    //         error_log('Error during save: ' . $errors());
    //         return false;
    //     }
    // }


    // // Helper function to check if the email or username already exists
    // public function findByEmailOrUsername($email, $username)
    // {
    //     $query = "SELECT * FROM {$this->table} WHERE email = :email OR username = :username";
    //     $stmt = self::$db->prepare($query);
    //     $stmt->bindParam(':email', $email);
    //     $stmt->bindParam(':username', $username);
    //     $stmt->execute();
    //     return $stmt->fetchObject(self::class);  // Return the user object if exists, or null
    // }

    // public function findById($userId)
    // {
    //     $query = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :user_id";
    //     $stmt = self::$db->prepare($query);
    //     $stmt->bindParam(':user_id', $userId);
    //     $stmt->execute();
    //     return $stmt->fetchObject(self::class);  // Return the user object
    // }
}
