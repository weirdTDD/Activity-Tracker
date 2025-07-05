<?php
session_start();
$page_title = "Dashboard - Activity Tracker";

require_once "config/db.php";
require_once "models/Activity.php";

$database = new Database();
$db = $database->getConnection();
$activity = new Activity($db);

$stats = $activity->getStats();
$recent_activities = $activity->readRecent();

include "includes/header.php";
?>

<div class="dashboard">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p class="text-muted">Overview of your activities</p>
    </div>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Activities</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?php echo $stats['pending']; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?php echo $stats['completed']; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-card-body">
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-content">
                        <h3><?php echo $stats['overdue']; ?></h3>
                        <p>Overdue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Activities</h5>
            <a href="activities.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New Activity
            </a>
        </div>
        <div class="card-body">
            <?php if($recent_activities): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Due Date</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 0;
                            foreach($recent_activities as $row):
                                $count++;   
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                                    <?php if($row['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $row['priority'] == 'High' ? 'danger' : 
                                            ($row['priority'] == 'Medium' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo $row['priority']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $row['status'] == 'done' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['assignee_name'] ?? 'Unassigned'); ?></td>
                                <td>
                                    <?php if($row['due_date']): ?>
                                        <?php 
                                        $due_date = new DateTime($row['due_date']);
                                        $now = new DateTime();
                                        $is_overdue = $due_date < $now && $row['status'] == 'pending';
                                        ?>
                                        <span class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                            <?php echo $due_date->format('M j, Y g:i A'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">No due date</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $created = new DateTime($row['created_at']);
                                    echo $created->format('M j, Y');
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h5>No activities yet</h5>
                    <p class="text-muted">Create your first activity to get started</p>
                    <a href="activities.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create Activity
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
