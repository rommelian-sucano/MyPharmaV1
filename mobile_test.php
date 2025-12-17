<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Mobile Test - MyPharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mobile-specific styles */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .test-card {
                margin-bottom: 15px;
            }
            
            .btn {
                min-height: 44px;
                min-width: 44px;
                margin-bottom: 10px;
            }
        }
        
        /* Touch device improvements */
        button, a {
            -webkit-tap-highlight-color: transparent;
            touch-action: manipulation;
        }
        
        .btn {
            min-height: 44px; /* Minimum touch target size */
            min-width: 44px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-bold">
                        <i class="fas fa-mobile-alt text-primary"></i> Mobile Test
                    </h1>
                    <p class="text-muted">Test page to verify mobile functionality</p>
                </div>
                
                <div class="card test-card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Navigation Tests</h5>
                    </div>
                    <div class="card-body">
                        <p>Try clicking these buttons to test mobile navigation:</p>
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-home me-2"></i>Home Page
                            </a>
                            <a href="login.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login Page
                            </a>
                            <a href="search.php?query=biogesic" class="btn btn-success btn-lg">
                                <i class="fas fa-search me-2"></i>Search Results
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card test-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Touch Target Tests</h5>
                    </div>
                    <div class="card-body">
                        <p>These buttons should be easy to tap on mobile devices:</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-lg" onclick="showAlert('Button 1 clicked!')">
                                <i class="fas fa-hand-point-up me-2"></i>Button 1
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-lg" onclick="showAlert('Button 2 clicked!')">
                                <i class="fas fa-hand-point-up me-2"></i>Button 2
                            </button>
                            <button type="button" class="btn btn-outline-success btn-lg" onclick="showAlert('Button 3 clicked!')">
                                <i class="fas fa-hand-point-up me-2"></i>Button 3
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card test-card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Form Tests</h5>
                    </div>
                    <div class="card-body">
                        <p>Test form elements on mobile:</p>
                        <form>
                            <div class="mb-3">
                                <label for="testInput" class="form-label">Test Input</label>
                                <input type="text" class="form-control form-control-lg" id="testInput" placeholder="Tap to test input">
                            </div>
                            <div class="mb-3">
                                <label for="testSelect" class="form-label">Test Select</label>
                                <select class="form-select form-select-lg" id="testSelect">
                                    <option selected>Choose an option</option>
                                    <option>Option 1</option>
                                    <option>Option 2</option>
                                    <option>Option 3</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="button" class="btn btn-danger btn-lg" onclick="showAlert('Form submitted!')">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showAlert(message) {
            alert(message);
        }
    </script>
</body>
</html>