<?php
session_start();
if (!isset($_SESSION['id']) || $_SESSION['type'] !== 'resident') {
    header('Location: index.php');
    exit();
}

$current_page = 'requests_complaints';

// Initialize variables
$requests = [];
$status_counts = [
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'closed' => 0
];

// Initialize variables for JavaScript to populate
$requests = [];
$status_counts = [
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0,
    'closed' => 0
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../ico/house-icon.ico">
    <title>Requests & Complaints - Resident Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css.css">
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
        }
        .priority-high { 
            border-left: 4px solid #dc3545; 
            background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
        }
        .priority-medium { 
            border-left: 4px solid #ffc107; 
            background: linear-gradient(135deg, #fffbf0 0%, #ffffff 100%);
        }
        .priority-low { 
            border-left: 4px solid #28a745; 
            background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
        }
        .priority-urgent { 
            border-left: 4px solid #6f42c1; 
            background: linear-gradient(135deg, #f8f5ff 0%, #ffffff 100%);
        }
        .card-hover:hover { 
            transform: translateY(-4px); 
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .status-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        .request-card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .request-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
        }
        .empty-state {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px dashed #dee2e6;
        }
        .btn-gradient {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            color: white;
        }
        .icon-lg {
            font-size: 2.5rem;
        }
        .text-gradient {
            background: linear-gradient(135deg, #007bff, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .timeline {
            position: relative;
            padding-left: 2rem;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .timeline-marker {
            position: absolute;
            left: -2rem;
            top: 0.5rem;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
        }
        .timeline-content {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            border-left: 3px solid currentColor;
        }
        .timeline-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .timeline-text {
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
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
                    <h2 class="mb-1 text-gradient fw-bold">Requests & Complaints</h2>
                    <p class="text-muted mb-0 fs-6">Manage your requests and complaints efficiently</p>
                </div>
                <a href="create_request.php" class="btn btn-gradient btn-lg px-4 py-2 shadow-sm">
                    <i class="bi bi-plus-circle-fill me-2"></i>New Request/Complaint
                </a>
            </div>

            <!-- Status Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card status-card bg-warning bg-opacity-10 border-warning h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-clock-history text-warning icon-lg mb-3"></i>
                            <h3 class="fw-bold text-warning mb-2"><?= $status_counts['pending'] ?></h3>
                            <p class="text-muted mb-0 fw-medium">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card status-card bg-info bg-opacity-10 border-info h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-arrow-repeat text-info icon-lg mb-3"></i>
                            <h3 class="fw-bold text-info mb-2"><?= $status_counts['in_progress'] ?></h3>
                            <p class="text-muted mb-0 fw-medium">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card status-card bg-success bg-opacity-10 border-success h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-check-circle text-success icon-lg mb-3"></i>
                            <h3 class="fw-bold text-success mb-2"><?= $status_counts['resolved'] ?></h3>
                            <p class="text-muted mb-0 fw-medium">Resolved</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card status-card bg-secondary bg-opacity-10 border-secondary h-100">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-archive text-secondary icon-lg mb-3"></i>
                            <h3 class="fw-bold text-secondary mb-2"><?= $status_counts['closed'] ?></h3>
                            <p class="text-muted mb-0 fw-medium">Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="card filter-card mb-4">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="bi bi-funnel me-2"></i>Filter & Search
                    </h6>
                </div>
                <div class="card-body pt-3">
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label for="statusFilter" class="form-label fw-medium">Filter by Status</label>
                            <select class="form-select form-select-lg" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending">🟡 Pending</option>
                                <option value="in_progress">🔵 In Progress</option>
                                <option value="resolved">🟢 Resolved</option>
                                <option value="closed">⚫ Closed</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <label for="typeFilter" class="form-label fw-medium">Filter by Type</label>
                            <select class="form-select form-select-lg" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="request">📝 Request</option>
                                <option value="complaint">⚠️ Complaint</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12">
                            <label for="searchInput" class="form-label fw-medium">Search</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput" 
                                       placeholder="Search by title or description...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Requests/Complaints List -->
            <div class="row" id="requestsList">
                <!-- Content will be loaded dynamically via JavaScript -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                            </div>
                    <p class="mt-3 text-muted">Loading requests...</p>
                                </div>
            </div>
        </div>
    </div>

    <!-- Request Details Modal -->
    <div class="modal fade" id="requestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="requestModalTitle">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="requestModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = '../api.php';
        let requestsData = [];
        let statusCounts = {
            'pending': 0,
            'in_progress': 0,
            'resolved': 0,
            'closed': 0
        };

        // Fetch requests/complaints from API
        function fetchRequests() {
            const url = `${API_URL}?type=resident&fetch=requests_complaints&created_by=<?= $_SESSION['id'] ?>`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                requestsData = data;
                calculateStatusCounts();
                renderRequests();
            })
            .catch(error => {
                console.error('Error fetching requests:', error);
                showError('Failed to load requests. Please try again.');
            });
        }

        // Calculate status counts
        function calculateStatusCounts() {
            statusCounts = {
                'pending': 0,
                'in_progress': 0,
                'resolved': 0,
                'closed': 0
            };
            
            requestsData.forEach(request => {
                if (statusCounts.hasOwnProperty(request.status)) {
                    statusCounts[request.status]++;
                }
            });
            
            updateStatusCards();
        }

        // Update status cards
        function updateStatusCards() {
            document.querySelector('.bg-warning .fw-bold').textContent = statusCounts.pending;
            document.querySelector('.bg-info .fw-bold').textContent = statusCounts.in_progress;
            document.querySelector('.bg-success .fw-bold').textContent = statusCounts.resolved;
            document.querySelector('.bg-secondary .fw-bold').textContent = statusCounts.closed;
        }

        // Render requests
        function renderRequests() {
            const container = document.getElementById('requestsList');
            
            if (requestsData.length === 0) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="card empty-state text-center py-5">
                            <div class="card-body p-5">
                                <i class="bi bi-inbox text-muted icon-lg mb-4"></i>
                                <h4 class="text-muted mb-3">No requests or complaints found</h4>
                                <p class="text-muted mb-4 fs-5">You haven't submitted any requests or complaints yet.</p>
                                <a href="create_request.php" class="btn btn-gradient btn-lg px-4 py-2">
                                    <i class="bi bi-plus-circle-fill me-2"></i>Create Your First Request
                                </a>
                            </div>
                        </div>
                    </div>
                `;
                return;
            }

            let html = '';
            requestsData.forEach(request => {
                const priorityClass = `priority-${request.priority}`;
                const typeIcon = request.type === 'request' ? 'hand-thumbs-up' : 'exclamation-triangle';
                const typeColor = request.type === 'request' ? 'primary' : 'danger';
                const statusColor = request.status === 'pending' ? 'warning' : 
                                  (request.status === 'in_progress' ? 'info' : 
                                  (request.status === 'resolved' ? 'success' : 'secondary'));
                const priorityColor = request.priority === 'urgent' ? 'danger' : 
                                    (request.priority === 'high' ? 'warning' : 
                                    (request.priority === 'medium' ? 'info' : 'success'));
                
                const createdDate = new Date(request.created_at).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });

                html += `
                    <div class="col-lg-4 col-md-6 mb-4 request-item" 
                         data-status="${request.status}" 
                         data-type="${request.type}"
                         data-title="${request.title.toLowerCase()}"
                         data-description="${request.description.toLowerCase()}">
                        <div class="card request-card h-100 card-hover ${priorityClass}">
                            <div class="card-header bg-transparent border-0 pb-0">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-${typeColor} px-3 py-2">
                                        <i class="bi bi-${typeIcon} me-1"></i>
                                        ${request.type.charAt(0).toUpperCase() + request.type.slice(1)}
                                    </span>
                                    <span class="badge status-badge bg-${statusColor} px-3 py-2">
                                        ${request.status.charAt(0).toUpperCase() + request.status.slice(1).replace('_', ' ')}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3">${request.title}</h6>
                                <p class="card-text text-muted mb-3">
                                    ${request.description.length > 120 ? request.description.substring(0, 120) + '...' : request.description}
                                </p>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="bi bi-flag-fill me-2 text-${priorityColor}"></i>
                                            <span class="fw-medium">${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)} Priority</span>
                                        </div>
                                    </div>
                                    ${request.assigned_admin_name ? `
                                    <div class="col-12">
                                        <div class="d-flex align-items-center text-muted small">
                                            <i class="bi bi-person-fill me-2 text-primary"></i>
                                            <span>Assigned to: <strong>${request.assigned_admin_name}</strong></span>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                                
                                <div class="border-top pt-3">
                                    <div class="d-flex justify-content-between align-items-center text-muted small">
                                        <div>
                                            <i class="bi bi-calendar-event me-1"></i>
                                            ${createdDate}
                                        </div>
                                        ${request.updated_at && request.updated_at !== request.created_at ? `
                                        <div>
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Updated
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <button class="btn btn-outline-primary w-100" onclick="viewRequest(${request.id})">
                                    <i class="bi bi-eye me-2"></i>View Details
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            container.innerHTML = html;
        }

        // Show error message
        function showError(message) {
            const container = document.getElementById('requestsList');
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                </div>
            `;
        }

        // Filter functionality
        function filterRequests() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            
            const requestItems = document.querySelectorAll('.request-item');
            
            requestItems.forEach(item => {
                const status = item.dataset.status;
                const type = item.dataset.type;
                const title = item.dataset.title;
                const description = item.dataset.description;
                
                const statusMatch = !statusFilter || status === statusFilter;
                const typeMatch = !typeFilter || type === typeFilter;
                const searchMatch = !searchInput || 
                    title.includes(searchInput) || 
                    description.includes(searchInput);
                
                if (statusMatch && typeMatch && searchMatch) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function viewRequest(requestId) {
            // Show loading state
            document.getElementById('requestModalTitle').textContent = 'Request #' + requestId;
            document.getElementById('requestModalBody').innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading request details...</p>
                </div>
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('requestModal'));
            modal.show();
            
            // Fetch request details from API
            fetchRequestDetails(requestId);
        }

        function fetchRequestDetails(requestId) {
            const url = `${API_URL}?type=resident&fetch=request_details&created_by=<?= $_SESSION['id'] ?>&request_id=${requestId}`;
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.length > 0) {
                    renderRequestDetails(data[0]);
                } else {
                    showRequestError('Request details not found.');
                }
            })
            .catch(error => {
                console.error('Error fetching request details:', error);
                showRequestError('Failed to load request details. Please try again.');
            });
        }

        function renderRequestDetails(request) {
            const statusColor = request.status === 'pending' ? 'warning' : 
                              (request.status === 'in_progress' ? 'info' : 
                              (request.status === 'resolved' ? 'success' : 'secondary'));
            const typeColor = request.type === 'request' ? 'primary' : 'danger';
            const typeIcon = request.type === 'request' ? 'hand-thumbs-up' : 'exclamation-triangle';
            const priorityColor = request.priority === 'urgent' ? 'danger' : 
                                (request.priority === 'high' ? 'warning' : 
                                (request.priority === 'medium' ? 'info' : 'success'));
            
            const createdDate = new Date(request.created_at).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            const updatedDate = request.updated_at ? new Date(request.updated_at).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }) : null;

            document.getElementById('requestModalBody').innerHTML = `
                <div class="row">
                    <div class="col-12">
                        <!-- Request Header -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h4 class="mb-2">${request.title}</h4>
                                <div class="d-flex gap-2 mb-2">
                                    <span class="badge bg-${typeColor} px-3 py-2">
                                        <i class="bi bi-${typeIcon} me-1"></i>
                                        ${request.type.charAt(0).toUpperCase() + request.type.slice(1)}
                                    </span>
                                    <span class="badge bg-${statusColor} px-3 py-2">
                                        ${request.status.charAt(0).toUpperCase() + request.status.slice(1).replace('_', ' ')}
                                    </span>
                                    <span class="badge bg-${priorityColor} px-3 py-2">
                                        <i class="bi bi-flag-fill me-1"></i>
                                        ${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)} Priority
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Description</h6>
                            <div class="border rounded p-3 bg-light">
                                <p class="mb-0">${request.description}</p>
                            </div>
                        </div>

                        <!-- Request Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Request Information</h6>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Created:</span>
                                        <span class="text-muted">${createdDate}</span>
                                    </div>
                                    ${updatedDate ? `
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Last Updated:</span>
                                        <span class="text-muted">${updatedDate}</span>
                                    </div>
                                    ` : ''}
                                    ${request.assigned_admin_name ? `
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Assigned to:</span>
                                        <span class="text-primary fw-medium">${request.assigned_admin_name}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2">Status Information</h6>
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Current Status:</span>
                                        <span class="badge bg-${statusColor}">${request.status.charAt(0).toUpperCase() + request.status.slice(1).replace('_', ' ')}</span>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Priority:</span>
                                        <span class="badge bg-${priorityColor}">${request.priority.charAt(0).toUpperCase() + request.priority.slice(1)}</span>
                                    </div>
                                    ${request.resolved_at ? `
                                    <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <span class="fw-medium">Resolved:</span>
                                        <span class="text-muted">${new Date(request.resolved_at).toLocaleDateString('en-US', {
                                            weekday: 'long',
                                            year: 'numeric',
                                            month: 'long',
                                            day: 'numeric'
                                        })}</span>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>

                        ${request.admin_response ? `
                        <!-- Admin Response -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Admin Response</h6>
                            <div class="border rounded p-3 bg-info bg-opacity-10">
                                <p class="mb-0">${request.admin_response}</p>
                            </div>
                        </div>
                        ` : ''}

                        <!-- Status History -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Status History</h6>
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-${statusColor}"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Request Created</h6>
                                        <p class="timeline-text text-muted">Request was created by resident</p>
                                        <small class="text-muted">${createdDate}</small>
                                    </div>
                                </div>
                                ${request.status !== 'pending' ? `
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-${statusColor}"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Status Updated</h6>
                                        <p class="timeline-text text-muted">Status changed to ${request.status.replace('_', ' ')}</p>
                                        ${updatedDate ? `<small class="text-muted">${updatedDate}</small>` : ''}
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function showRequestError(message) {
            document.getElementById('requestModalBody').innerHTML = `
                <div class="alert alert-danger text-center">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    ${message}
                </div>
            `;
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            fetchRequests();
            
            // Add event listeners for filters
            document.getElementById('statusFilter').addEventListener('change', filterRequests);
            document.getElementById('typeFilter').addEventListener('change', filterRequests);
            document.getElementById('searchInput').addEventListener('input', filterRequests);
        });
    </script>
</body>
</html>
