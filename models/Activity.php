<?php
class Activity {
    private $conn;
    private $table_name = "activities";
    
    public $id;
    public $title;
    public $description;
    public $priority;
    public $status;
    public $created_by;
    public $assigned_to;
    public $due_date;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=?, description=?, priority=?, status=?, created_by=?, assigned_to=?, due_date=?";
        
        $stmt = $this->conn->prepare($query);
        
        $result = $stmt->execute([
            $this->title,
            $this->description,
            $this->priority,
            $this->status ?? 'pending',
            $this->created_by,
            $this->assigned_to,
            $this->due_date
        ]);
        if (!$result) {
            error_log("Create activity error: " . implode(" | ", $stmt->errorInfo()));
        }
        return $result;
    }
    
    
    public function readAll() {
        $query = "SELECT a.*,
             u1.name as creator_name,
             u2.name as assignee_name
             FROM activities a 
             LEFT JOIN users u1 ON a.created_by = u1.id 
             LEFT JOIN users u2 ON a.assigned_to = u2.id ORDER BY a.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
        public function readRecent() {
        $query = "SELECT a.*,
             u1.name as creator_name,
             u2.name as assignee_name
             FROM activities a 
             LEFT JOIN users u1 ON a.created_by = u1.id 
             LEFT JOIN users u2 ON a.assigned_to = u2.id ORDER BY a.created_at DESC LIMIT 10";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    
    public function readOne() {
        $query = "SELECT a.*, 
                         u1.name as creator_name, 
                         u2.name as assignee_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN users u1 ON a.created_by = u1.id 
                  LEFT JOIN users u2 ON a.assigned_to = u2.id 
                  WHERE a.id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=?, description=?, priority=?, status=?, assigned_to=?, due_date=? 
                  WHERE id=?";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            $this->title,
            $this->description,
            $this->priority,
            $this->status,
            $this->assigned_to,
            $this->due_date,
            $this->id
        ]);
    }
    
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }
    
    public function getStats() {
        $stats = [];
        
        // Total activities
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetch()['total'];
        
        // Pending activities
        $query = "SELECT COUNT(*) as pending FROM " . $this->table_name . " WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending'] = $stmt->fetch()['pending'];
        
        // Completed activities
        $query = "SELECT COUNT(*) as completed FROM " . $this->table_name . " WHERE status = 'done'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['completed'] = $stmt->fetch()['completed'];
        
        $query = "SELECT COUNT(*) as overdue FROM " . $this->table_name . " 
                  WHERE status = 'pending' AND due_date < NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['overdue'] = $stmt->fetch()['overdue'];
        
        return $stats;
    }
}
?>