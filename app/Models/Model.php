<?php
namespace App\Models;

use App\Helpers\Database;
use PDO;

class Model
{
    protected static $db = null;
    protected $table;
    protected $attributes = [];
    protected $primaryKey;
    protected $rules = [];  // Store field validation rules
    protected $errors = [];  // Store validation errors


    public function __construct($table = null)
    {
        // Use the static $db if it's already initialized, otherwise, initialize it
        if (self::$db === null) {
            self::$db = (new Database())->getConnection();
        }
        $this->table = $table ?: strtolower(get_class($this)); // Default table name is the class name in lowercase
        $this->primaryKey = $table . '_id';
    }

    // Magic getter to access dynamic properties
    public function __get($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    // Magic setter to set dynamic properties
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    // Define rules for validation
    public function setRules($rules)
    {
        $this->rules = $rules;
    }

    // Save the model (Insert or Update)
    public function save()
    {
        // Get only the actual database columns
        $filteredAttributes = $this->filterDatabaseColumns();

        if (isset($filteredAttributes[$this->primaryKey])) {
            // If the model already has an ID, perform an UPDATE
            return $this->update($filteredAttributes[$this->primaryKey], $filteredAttributes);
        } else {
            // Otherwise, perform an INSERT
            return $this->insert($filteredAttributes);
        }
    }

    // Insert a new record into the table
    private function insert($data)
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $query = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = self::$db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }

        if ($stmt->execute()) {
            // After insertion, set the ID if it's auto-generated
            $this->attributes[$this->primaryKey] = self::$db->lastInsertId();
            return true;
        }
        return false;
    }

    // Update an existing record by its ID
    private function update($id, $data)
    {
        $setClause = "";
        foreach ($data as $key => $value) {
            $setClause .= "$key = :$key, ";
        }
        $setClause = rtrim($setClause, ", ");

        $query = "UPDATE {$this->table} SET $setClause WHERE {$this->primaryKey} = :id";
        $stmt = self::$db->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    // Delete the object and also the record
    public function delete()
    {
        // Get the current instance's ID
        $id = $this->{$this->primaryKey};

        // Prepare the DELETE query
        $query = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindParam(':id', $id);

        // Execute the delete statement
        if ($stmt->execute()) {
            return true; // Successfully deleted
        }

        return false; // Deletion failed
    }

    // Helper method to fetch all columns from the database table
    private function getDatabaseColumns()
    {
        $query = "DESCRIBE {$this->table}";
        $stmt = self::$db->prepare($query);
        $stmt->execute();

        // Return an array of column names (without additional metadata like type, etc.)
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Field');
    }

    // Filter the model attributes to only include actual database columns
    private function filterDatabaseColumns()
    {
        $dbColumns = $this->getDatabaseColumns();  // Get the actual table columns
        return array_filter(
            $this->attributes,
            function ($key) use ($dbColumns) {
                return in_array($key, $dbColumns);  // Only keep attributes that match the actual db columns
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    // Fetch all records from the model's table
    public static function findAll()
    {
        $instance = new static(); // Get instance of the calling class
        $query = "SELECT * FROM {$instance->table}"; // Query to fetch all records
        $stmt = self::$db->prepare($query);
        $stmt->execute();

        // Fetch all rows from the result set
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optionally map results to model objects
        $records = [];
        foreach ($results as $result) {
            $record = new static(); // Create an instance of the calling class
            $record->mapColumnsToAttributes($result); // Map the columns to attributes
            $records[] = $record; // Add the object to the result array
        }

        return $records; // Return an array of model instances
    }

    // Find a record by a given ID
    public static function find($id)
    {
        $instance = new static(); // Get instance of the calling class
        $query = "SELECT * FROM {$instance->table} WHERE {$instance->primaryKey} = :id";
        $stmt = self::$db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $instance->mapColumnsToAttributes($result);
            return $instance;
        }
        return null;
    }

    // Find a user by a specific field and value (e.g., email or username)
    public static function findByField($field, $value)
    {
        $instance = new static(); // Get instance of the calling class
        // Assuming you have a database query method to fetch a user by a specific field
        // You would need to sanitize the input and prevent SQL injection here
        $stmt = self::$db->prepare("SELECT * FROM {$instance->table} WHERE $field = :value LIMIT 1");
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        return $stmt->fetchObject(self::class);  // Return the user object if found, otherwise null
    }


    public static function filter($conditions = [])
    {
        $instance = new static(); // Get instance of the calling class

        // Start building the base query
        $query = "SELECT * FROM {$instance->table}";

        // Prepare the conditions for WHERE clause dynamically
        $whereClauses = [];
        $params = [];
        $paramCounter = 1;
        foreach ($conditions as $column => $value) {
            $whereClauses[] = "{$column} ?";
            $params[$paramCounter] = $value;
            $paramCounter++;
        }

        // If there are any conditions, append the WHERE clause
        if (count($whereClauses) > 0) {
            $query .= " WHERE " . implode(" ", $whereClauses);
        }

        // Prepare the query and bind parameters
        $stmt = self::$db->prepare($query);

        // Bind values
        foreach($params as $key=>$value) {
            $stmt->bindValue($key, $value);
        }

        // Execute the query
        $stmt->execute();

        // Fetch all results as associative array
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the results as an array of instances
        $instances = [];
        foreach ($results as $result) {
            $instanceCopy = new static();
            $instanceCopy->mapColumnsToAttributes($result); // Assuming this method maps database columns to object properties
            $instances[] = $instanceCopy;
        }

        return $instances;
    }


    // Helper method to map column data to object properties
    private function mapColumnsToAttributes($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    // General validate method to validate the model's fields based on rules
    public function validate()
    {
        $this->errors = [];  // Clear previous errors

        foreach ($this->rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule => $ruleValue) {
                $fieldValue = isset($this->attributes[$field]) ? $this->attributes[$field] : null;

                // Apply the validation rule
                if (!$this->applyRule($field, $fieldValue, $rule, $ruleValue)) {
                    $this->errors[$field][] = $this->getErrorMessage($rule, $field, $ruleValue);
                }
            }
        }

        return empty($this->errors);  // Return true if no errors, false otherwise
    }

    // Apply validation rule for a specific field
    protected function applyRule($field, $value, $rule, $ruleValue)
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'minLength':
                return strlen($value) >= $ruleValue;
            case 'maxLength':
                return strlen($value) <= $ruleValue;
            case 'numeric':
                return is_numeric($value);
            case 'minValue':
                return $value >= $ruleValue;
            case 'maxValue':
                return $value <= $ruleValue;
            case 'confirmPassword':  // Dynamic rule for password confirmation
                // Check if password matches confirm password
                return $value === $this->attributes['password'];
            case 'unique':
                // Use isUnique to check if the field value is unique in the database
                return $this->isUnique($field, $value);
            default:
                return true;
        }
    }

    // Check if a given field is unique (not already in the database)
    protected function isUnique($field, $value)
    {
        // Assuming you have a method that can search for any field in the database
        $user = self::findByField($field, $value);
        return $user === false; // Return true if no record is found with the same value
    }

    // Get the error message for a failed validation rule
    protected function getErrorMessage($rule, $field, $ruleValue = null)
    {
        $field = ucfirst(htmlspecialchars($field, ENT_QUOTES, 'UTF-8'));
        switch ($rule) {
            case 'required':
                return "{$field} is required.";
            case 'email':
                return "{$field} must be a valid email address.";
            case 'minLength':
                return "{$field} must be at least {$ruleValue} characters long.";
            case 'maxLength':
                return "{$field} must be at most {$ruleValue} characters long.";
            case 'numeric':
                return "{$field} must be a numeric value.";
            case 'minValue':
                return "{$field} must be greater than or equal to {$ruleValue}.";
            case 'maxValue':
                return "{$field} must be less than or equal to {$ruleValue}.";
            case "confirmPassword":
                return "Confirm Password did not match.";
            case "unique":
                return "{$field} already taken.";
            default:
                return "Invalid value for {$field}.";
        }
    }

    // Get the validation errors
    public function getErrors()
    {
        return $this->errors;
    }

    // Conver this object to associative array
    function toArray()
    {
        return (array) $this;
    }
}
