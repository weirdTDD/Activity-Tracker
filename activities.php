<?php
session_start();
$page_title = "Activities - Activity Tracker";

require_once "config/db.php";
require_once "models/Activity.php";
require_once "models/User.php";

$database = new Database();
$db = $database->getConnection();
$activity = new Activity($db);
$user = new User($db);

$activities = $activity->readAll();
$users = $user->getAllUsers();

include "includes/header.php";
?>

<div class="activities-page">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1>Activities</h1>
                <p class="text-muted">Manage your tasks and activities</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newActivityModal">
                <i class="fas fa-plus me-2"></i>New Activity
            </button>
        </div>
    </div>
    <div class="row">
        <?php if($activities): ?>
            <?php foreach($activities as $row): ?>
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="activity-card">
                    <div class="activity-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 class="activity-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <button class="dropdown-item status-update-btn" 
                                                data-activity-id="<?php echo $row['id']; ?>"
                                                data-current-status="<?php echo $row['status']; ?>">
                                            <i class="fas fa-edit me-2"></i>Update Status
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger" 
                                                onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash me-2"></i>Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="activity-meta">
                            <span class="badge bg-<?php 
                                echo $row['priority'] == 'High' ? 'danger' : 
                                    ($row['priority'] == 'Medium' ? 'warning' : 'info'); 
                            ?> me-2">
                                <?php echo $row['priority']; ?>
                            </span>
                            <span class="badge bg-<?php echo $row['status'] == 'done' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </div>
                    </div>
                                        <div class="activity-body">
                        <?php if($row['description']): ?>
                            <p class="activity-description"><?php echo htmlspecialchars($row['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="activity-details">
                            <div class="detail-item">
                                <i class="fas fa-user text-muted me-2"></i>
                                <span><?php echo htmlspecialchars($row['assignee_name'] ?? 'Unassigned'); ?></span>
                            </div>
                            
                            <?php if($row['due_date']): ?>
                            <div class="detail-item">
                                <i class="fas fa-calendar text-muted me-2"></i>
                                <?php 
                                $due_date = new DateTime($row['due_date']);
                                $now = new DateTime();
                                $is_overdue = $due_date < $now && $row['status'] == 'pending';
                                ?>
                                <span class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                    <?php echo $due_date->format('M j, Y g:i A'); ?>
                                    <?php if($is_overdue): ?>
                                        <small>(Overdue)</small>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="detail-item">
                                <i class="fas fa-clock text-muted me-2"></i>
                                <small class="text-muted">
                                    Created <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <form id="delete-form-<?php echo $row['id']; ?>" method="POST" action="api/delete-activity.php" style="display: none;">
                        <input type="hidden" name="activity_id" value="<?php echo $row['id']; ?>">
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-tasks fa-4x text-muted mb-4"></i>
                    <h3>No activities found</h3>
                    <p class="text-muted mb-4">Create your first activity to get started</p>
                    <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#newActivityModal">
                        <i class="fas fa-plus me-2"></i>Create Activity
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="newActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="api/create-activity.php">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" required 
                                   placeholder="Enter activity title">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" name="priority">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Enter activity description"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign To</label>
                            <select class="form-select" name="assigned_to">
                                <option value="">Select user...</option>
                                <?php foreach($users as $u): ?>
                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" class="form-control" name="due_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Activity
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
