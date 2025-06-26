
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Add new styles for professionalization options */
        .modal-actions .option-group {
            margin-bottom: 15px;
        }
        .modal-actions .option-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        .modal-actions select,
        .modal-actions input[type="checkbox"] {
            margin-top: 5px;
        }
        .modal-actions select {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .options-container {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Existing header content remains the same -->
        <header class="dashboard-header">
            <h1>Dashboard</h1>
            <nav>
                <a href="#" id="notesLink">Notes</a>
                <a href="#" id="tasksLink">Tasks</a>
            </nav>
            <button id="addButton" class="curved-button">Add New</button>
            <div class="profile-container">
                <button id="profileButton" class="profile-button">👤</button>
                <div id="profileMenu" class="profile-menu">
                    <p>Username</p>
                    <a href="login.php">
                        <button>Log Out</button>
                    </a>
                </div>
            </div>
            <div class="toggle-container">
                <label class="toggle">
                    <input type="checkbox" id="completedToggle">
                    <span class="slider round"></span>
                </label>
                <span id="toggleLabel">Active</span>
            </div>
        </header>

        <main class="main-content">
            <div id="contentGrid" class="content-grid"></div>
        </main>
    </div>

    <!-- Update the existing itemModal -->
    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalContent"></p>
            <div class="modal-actions">
                <button id="editButton" class="icon-button" title="Edit">✏️</button>
                <button id="priorityButton" class="icon-button" title="Priority">🏷️</button>
                <button id="summarizeButton" class="icon-button" title="Summarize">📝</button>
                <button id="professionalismButton" class="icon-button" title="Professionalism">👔</button>
            </div>
        </div>
    </div>
    
    <!-- Update the existing editModal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Item</h2>
            <input type="text" id="editTitle" placeholder="Title">
            <textarea id="editContent" placeholder="Content"></textarea>
            <div class="priority-selector">
                <button class="priority-option priority-high" data-priority="High">High</button>
                <button class="priority-option priority-medium" data-priority="Medium">Medium</button>
                <button class="priority-option priority-low" data-priority="Low">Low</button>
            </div>
            <button id="saveButton" class="curved-button">Save</button>
        </div>
    </div>

    <!-- Add new professionalizeModal -->
    <div id="professionalizeModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Professionalize Content</h2>
            <div class="options-container">
                <div class="option-group">
                    <label for="formality-level">Formality Level:</label>
                    <select id="formality-level">
                        <option value="standard">Standard</option>
                        <option value="highly_formal">Highly Formal</option>
                    </select>
                </div>
                <div class="option-group">
                    <label>
                        <input type="checkbox" id="preserve-contractions">
                        Preserve Contractions
                    </label>
                </div>
                <div class="option-group">
                    <label for="industry-specific">Industry-Specific Terms:</label>
                    <select id="industry-specific">
                        <option value="">None</option>
                        <option value="tech">Technology</option>
                        <option value="legal">Legal</option>
                        <option value="medical">Medical</option>
                    </select>
                </div>
            </div>
            <button id="applyProfessionalizeButton" class="curved-button">Apply</button>
            <button class="curved-button cancel">Cancel</button>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>