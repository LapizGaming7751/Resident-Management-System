<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'resident') {
    header('Location: index.php');
    exit();
}

$current_page = 'create_request';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    
    // Validation
    if (empty($type) || empty($title) || empty($description)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!in_array($type, ['request', 'complaint'])) {
        $error_message = 'Invalid request type. Must be "request" or "complaint".';
    } elseif (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
        $error_message = 'Invalid priority level. Must be "low", "medium", "high", or "urgent".';
    } elseif (strlen($title) < 5) {
        $error_message = 'Title must be at least 5 characters long.';
    } elseif (strlen($title) > 255) {
        $error_message = 'Title must be less than 255 characters.';
    } elseif (strlen($description) < 20) {
        $error_message = 'Description must be at least 20 characters long.';
    } elseif (strlen($description) > 65535) {
        $error_message = 'Description is too long.';
    } else {
        // Direct database insertion instead of API call
        try {
            // Database connection
            $host = 'localhost';
            $db = 'resident_management_system';
            $db_user = 'root';
            $db_pass = '';
            
            $conn = new mysqli($host, $db_user, $db_pass, $db);
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            // Insert the request/complaint
            $sql = "INSERT INTO requests_complaints (resident_id, type, title, description, priority, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param('issss', $_SESSION['id'], $type, $title, $description, $priority);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create request: " . $stmt->error);
            }
            
            $request_id = $conn->insert_id;
            $stmt->close();
            
            // Add status history entry
            $history_sql = "INSERT INTO request_status_history (request_id, new_status, changed_by, changed_by_type, notes, changed_at) VALUES (?, 'pending', ?, 'resident', 'Request created by resident', NOW())";
            $history_stmt = $conn->prepare($history_sql);
            if (!$history_stmt) {
                throw new Exception("History prepare failed: " . $conn->error);
            }
            
            $history_stmt->bind_param('ii', $request_id, $_SESSION['id']);
            
            if (!$history_stmt->execute()) {
                throw new Exception("Failed to create status history: " . $history_stmt->error);
            }
            
            $history_stmt->close();
            $conn->commit();
            
            $success_message = "Your " . $type . " has been submitted successfully!";
            // Clear form data
            $type = $title = $description = '';
            $priority = 'medium';
            
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollback();
            }
            $error_message = "Failed to submit request: " . $e->getMessage();
            error_log("Request creation error: " . $e->getMessage());
        } finally {
            if (isset($conn)) {
                $conn->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Create Request/Complaint - Resident Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        .form-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex-grow: 1;
        }
        .priority-indicator {
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .character-count {
            font-size: 0.8rem;
            color: #6c757d;
            font-weight: 500;
        }
        .required-field::after {
            content: " *";
            color: #dc3545;
            font-weight: bold;
        }
        .form-card {
            border: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 1rem;
        }
        .guidelines-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
        }
        .recent-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 0.75rem;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
            font-weight: 600;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }
        .btn-outline-gradient {
            border: 2px solid #007bff;
            color: #007bff;
            font-weight: 600;
        }
        .btn-outline-gradient:hover {
            background: #007bff;
            color: white;
            transform: translateY(-1px);
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .text-gradient {
            background: linear-gradient(135deg, #007bff, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .icon-lg {
            font-size: 2.5rem;
        }
        .section-title {
            color: #495057;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
        .form-check-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .priority-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .priority-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .priority-card .form-check-input:checked + .form-check-label {
            color: #007bff;
        }
        .priority-card:has(.form-check-input:checked) {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }
        
        /* Minimal custom CSS using Bootstrap classes */
        .form-check-input {
            position: absolute;
            top: 1rem;
            left: 1rem;
            z-index: 2;
        }
        
        .form-check-label {
            padding-left: 2.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <?php include '../topbar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
                <div class="mb-3 mb-md-0">
                    <h2 class="mb-1 text-gradient fw-bold">Create Request/Complaint</h2>
                    <p class="text-muted mb-0 fs-6">Submit a new request or complaint to management</p>
                </div>
                <a href="requests_complaints.php" class="btn btn-outline-gradient btn-lg px-4 py-2">
                    <i class="bi bi-arrow-left me-2"></i>Back to Requests
                </a>
            </div>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Success!</h6>
                            <p class="mb-0"><?= htmlspecialchars($success_message) ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Error!</h6>
                            <p class="mb-0"><?= htmlspecialchars($error_message) ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form -->
                    <div class="card form-card h-100">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-plus-circle-fill me-2 text-primary"></i>New Request/Complaint
                            </h5>
                        </div>
                        <div class="card-body p-4 d-flex flex-column">
                            <form method="POST" action="create_request.php" id="requestForm" class="d-flex flex-column flex-grow-1">
                                <!-- Request Type -->
                                <div class="form-section">
                                    <h6 class="section-title">
                                        <i class="bi bi-tag me-2">Request Type</i>
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <div class="form-check p-4 border rounded-3 h-100 bg-light">
                                                <input class="form-check-input" type="radio" name="type" id="typeRequest" 
                                                       value="request" <?= ($type ?? '') === 'request' ? 'checked' : '' ?> required>
                                                <label class="form-check-label w-100" for="typeRequest">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-hand-thumbs-up text-primary me-2 fs-5"></i>
                                                        <strong class="fs-6">Request</strong>
                                                    </div>
                                                    <small class="text-muted">Ask for something (maintenance, services, etc.)</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-check p-4 border rounded-3 h-100 bg-light">
                                                <input class="form-check-input" type="radio" name="type" id="typeComplaint" 
                                                       value="complaint" <?= ($type ?? '') === 'complaint' ? 'checked' : '' ?> required>
                                                <label class="form-check-label w-100" for="typeComplaint">
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="bi bi-exclamation-triangle text-danger me-2 fs-5"></i>
                                                        <strong class="fs-6">Complaint</strong>
                                                    </div>
                                                    <small class="text-muted">Report an issue or problem</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Title -->
                                <div class="form-section">
                                    <h6 class="section-title">
                                        <i class="bi bi-pencil-square me-2"></i>Title
                                    </h6>
                                    <div class="mb-3">
                                        <label for="title" class="form-label required-field fw-semibold">Brief Title</label>
                                        <input type="text" class="form-control form-control-lg" id="title" name="title" 
                                               value="<?= htmlspecialchars($title ?? '') ?>" 
                                               placeholder="Enter a clear, descriptive title" 
                                               maxlength="255" required>
                                        <div class="form-text fw-medium">Keep it concise but descriptive</div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="form-section">
                                    <h6 class="section-title">
                                        <i class="bi bi-file-text me-2"></i>Description
                                    </h6>
                                    <div class="mb-3">
                                        <label for="description" class="form-label required-field fw-semibold">Detailed Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="6" 
                                                  placeholder="Provide detailed information about your request or complaint..." 
                                                  maxlength="2000" required><?= htmlspecialchars($description ?? '') ?></textarea>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <div class="form-text fw-medium">Be as specific as possible to help us understand your needs</div>
                                            <div class="character-count">
                                                <span id="charCount">0</span>/2000 characters
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Priority -->
                                <div class="form-section">
                                    <h6 class="section-title">
                                        <i class="bi bi-flag me-2"></i>Priority Level
                                    </h6>
                                    <div class="g-3">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-6">
                                                <div class="form-check p-4 border rounded-3 h-100 priority-card bg-light">
                                                    <input class="form-check-input" type="radio" name="priority" id="priorityLow" 
                                                           value="low" <?= ($priority ?? '') === 'low' ? 'checked' : '' ?>>
                                                    <label class="form-check-label w-100" for="priorityLow">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-success me-2">Low</span>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <small class="text-muted">Non-urgent matters</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check p-4 border rounded-3 h-100 priority-card bg-light">
                                                    <input class="form-check-input" type="radio" name="priority" id="priorityMedium" 
                                                           value="medium" <?= ($priority ?? '') === 'medium' || empty($priority) ? 'checked' : '' ?>>
                                                    <label class="form-check-label w-100" for="priorityMedium">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-warning text-dark me-2">Medium</span>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <small class="text-muted">Normal priority</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-check p-4 border rounded-3 h-100 priority-card bg-light">
                                                    <input class="form-check-input" type="radio" name="priority" id="priorityHigh" 
                                                           value="high" <?= ($priority ?? '') === 'high' ? 'checked' : '' ?>>
                                                    <label class="form-check-label w-100" for="priorityHigh">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-danger me-2">High</span>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <small class="text-muted">Important matters</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check p-4 border rounded-3 h-100 priority-card bg-light">
                                                    <input class="form-check-input" type="radio" name="priority" id="priorityUrgent" 
                                                           value="urgent" <?= ($priority ?? '') === 'urgent' ? 'checked' : '' ?>>
                                                    <label class="form-check-label w-100" for="priorityUrgent">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <span class="badge bg-dark me-2">Urgent</span>
                                                        </div>
                                                        <div class="text-wrap">
                                                            <small class="text-muted">Emergency situations</small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mt-auto">
                                    <a href="requests_complaints.php" class="btn btn-outline-secondary btn-lg px-4 py-2">
                                        <i class="bi bi-arrow-left me-2"></i>Cancel
                                    </a>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-gradient btn-lg px-4 py-2" onclick="previewRequest()">
                                            <i class="bi bi-eye me-2"></i>Preview
                                        </button>
                                        <button type="submit" class="btn btn-gradient btn-lg px-4 py-2">
                                            <i class="bi bi-send me-2"></i>Submit Request
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Guidelines Card -->
                    <div class="card guidelines-card mb-4">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-info-circle-fill me-2 text-primary"></i>Guidelines
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h6 class="text-primary fw-bold mb-3">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>For Requests:
                                </h6>
                                <ul class="small mb-0">
                                    <li class="mb-2">Be specific about what you need</li>
                                    <li class="mb-2">Include relevant details (room number, dates, etc.)</li>
                                    <li class="mb-0">Mention any previous attempts to resolve</li>
                                </ul>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="text-danger fw-bold mb-3">
                                    <i class="bi bi-exclamation-triangle me-2"></i>For Complaints:
                                </h6>
                                <ul class="small mb-0">
                                    <li class="mb-2">Describe the issue clearly</li>
                                    <li class="mb-2">Include when and where it occurred</li>
                                    <li class="mb-0">Mention any witnesses or evidence</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h6 class="text-warning fw-bold mb-3">
                                    <i class="bi bi-flag me-2"></i>Priority Guidelines:
                                </h6>
                                <ul class="small mb-0">
                                    <li class="mb-2"><strong class="text-danger">Urgent:</strong> Safety hazards, security issues</li>
                                    <li class="mb-2"><strong class="text-warning">High:</strong> Major inconveniences, urgent repairs</li>
                                    <li class="mb-2"><strong class="text-info">Medium:</strong> General maintenance, requests</li>
                                    <li class="mb-2"><strong class="text-success">Low:</strong> Minor issues, suggestions</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Requests -->
                    <div class="card recent-card">
                        <div class="card-header bg-transparent border-0 pb-0">
                            <h6 class="mb-0 fw-bold text-dark">
                                <i class="bi bi-clock-history me-2 text-primary"></i>Your Recent Requests
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <?php
                            // Get recent requests directly from database
                            $recent_requests = [];
                            try {
                                $host = 'localhost';
                                $db = 'resident_management_system';
                                $db_user = 'root';
                                $db_pass = '';
                                
                                $conn = new mysqli($host, $db_user, $db_pass, $db);
                                if (!$conn->connect_error) {
                                    $sql = "SELECT * FROM requests_complaints WHERE resident_id = ? ORDER BY created_at DESC LIMIT 5";
                                    $stmt = $conn->prepare($sql);
                                    if ($stmt) {
                                        $stmt->bind_param('i', $_SESSION['id']);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        while ($row = $result->fetch_assoc()) {
                                            $recent_requests[] = $row;
                                        }
                                        $stmt->close();
                                    }
                                    $conn->close();
                                }
                            } catch (Exception $e) {
                                error_log("Error fetching recent requests: " . $e->getMessage());
                            }
                            
                            if (empty($recent_requests)):
                            ?>
                                <div class="text-center py-3">
                                    <i class="bi bi-inbox text-muted fs-1 mb-2"></i>
                                    <p class="text-muted small mb-0">No recent requests found.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_requests as $req): ?>
                                    <div class="d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                        <div class="flex-grow-1">
                                            <div class="small fw-bold text-dark"><?= htmlspecialchars($req['title']) ?></div>
                                            <div class="small text-muted">
                                                <i class="bi bi-<?= $req['type'] === 'request' ? 'hand-thumbs-up' : 'exclamation-triangle' ?> me-1"></i>
                                                <?= ucfirst($req['type']) ?> • 
                                                <i class="bi bi-calendar me-1"></i>
                                                <?= date('M j', strtotime($req['created_at'])) ?>
                                            </div>
                                        </div>
                                        <span class="badge bg-<?= 
                                            $req['status'] === 'pending' ? 'warning' : 
                                            ($req['status'] === 'in_progress' ? 'info' : 
                                            ($req['status'] === 'resolved' ? 'success' : 'secondary')) 
                                        ?> small px-2 py-1">
                                            <?= ucfirst(str_replace('_', ' ', $req['status'])) ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="text-center mt-4">
                                    <a href="requests_complaints.php" class="btn btn-outline-gradient btn-sm px-3 py-2">
                                        <i class="bi bi-arrow-right me-1"></i>View All Requests
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitForm()">Submit Request</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character count for description
        const descriptionTextarea = document.getElementById('description');
        const charCount = document.getElementById('charCount');
        
        descriptionTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Preview function
        function previewRequest() {
            const form = document.getElementById('requestForm');
            const formData = new FormData(form);
            
            const type = formData.get('type');
            const title = formData.get('title');
            const description = formData.get('description');
            const priority = formData.get('priority');
            
            if (!type || !title || !description) {
                alert('Please fill in all required fields before previewing.');
                return;
            }
            
            const previewContent = `
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-${type === 'request' ? 'primary' : 'danger'}">${type.charAt(0).toUpperCase() + type.slice(1)}</span>
                        <span class="badge bg-${priority === 'urgent' ? 'dark' : (priority === 'high' ? 'danger' : (priority === 'medium' ? 'warning' : 'success'))}">${priority.charAt(0).toUpperCase() + priority.slice(1)} Priority</span>
                    </div>
                    <div class="card-body">
                        <h5>${title}</h5>
                        <p class="text-muted">${description}</p>
                        <div class="small text-muted">
                            <i class="bi bi-person me-1"></i>Submitted by: <?= htmlspecialchars($_SESSION['user']) ?><br>
                            <i class="bi bi-calendar me-1"></i>Date: ${new Date().toLocaleDateString()}
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('previewContent').innerHTML = previewContent;
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        }

        // Submit form from preview
        function submitForm() {
            document.getElementById('requestForm').submit();
        }

        // Form validation
        document.getElementById('requestForm').addEventListener('submit', function(e) {
            const type = document.querySelector('input[name="type"]:checked');
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            
            if (!type || !title || !description) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            if (title.length < 5) {
                e.preventDefault();
                alert('Title must be at least 5 characters long.');
                return;
            }
            
            if (title.length > 255) {
                e.preventDefault();
                alert('Title must be less than 255 characters.');
                return;
            }
            
            if (description.length < 20) {
                e.preventDefault();
                alert('Description must be at least 20 characters long.');
                return;
            }
            
            if (description.length > 65535) {
                e.preventDefault();
                alert('Description is too long.');
                return;
            }
        });
    </script>
</body>
</html>
