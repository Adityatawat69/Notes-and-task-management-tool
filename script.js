let currentMode = 'notes';
let notes = [];
let tasks = [];
let currentEditIndex = null;
let showCompleted = false;

document.addEventListener('DOMContentLoaded', () => {
    const notesLink = document.getElementById('notesLink');
    const tasksLink = document.getElementById('tasksLink');
    const addButton = document.getElementById('addButton');
    const itemModal = document.getElementById('itemModal');
    const editModal = document.getElementById('editModal');
    const saveButton = document.getElementById('saveButton');
    const completedToggle = document.getElementById('completedToggle');
    const toggleLabel = document.getElementById('toggleLabel');

    completedToggle.addEventListener('change', () => {
        showCompleted = completedToggle.checked;
        toggleLabel.textContent = showCompleted ? 'Completed' : 'Active';
        renderContent();
    });

    notesLink.addEventListener('click', () => switchMode('notes'));
    tasksLink.addEventListener('click', () => switchMode('tasks'));
    addButton.addEventListener('click', () => openEditModal());

    document.querySelectorAll('.modal .close').forEach(closeBtn => {
        closeBtn.addEventListener('click', () => {
            itemModal.style.display = 'none';
            editModal.style.display = 'none';
        });
    });

    document.getElementById('editButton').addEventListener('click', () => {
        itemModal.style.display = 'none';
        openEditModal(currentEditIndex);
    });

    document.getElementById('priorityButton').addEventListener('click', () => {
        togglePriority(currentEditIndex);
        updateItemModal(currentEditIndex);
    });

    document.getElementById('summarizeButton').addEventListener('click', () => {
        summarizeContent(currentEditIndex);
    });

    document.getElementById('professionalismButton').addEventListener('click', () => {
        makeContentProfessional(currentEditIndex);
    });

    saveButton.addEventListener('click', saveItem);

    document.querySelectorAll('.priority-option').forEach(option => {
        option.addEventListener('click', (e) => {
            document.querySelectorAll('.priority-option').forEach(opt => opt.classList.remove('selected'));
            e.target.classList.add('selected');
        });
    });

    renderContent();
});

function switchMode(mode) {
    currentMode = mode;
    renderContent();
}

function renderContent() {
    const contentGrid = document.getElementById('contentGrid');
    contentGrid.innerHTML = '';
    const items = currentMode === 'notes' ? notes : tasks;
    items.forEach((item, index) => {
        if (item.completed === showCompleted) {
            const card = document.createElement('div');
            card.className = 'card';
            card.innerHTML = `
                <div class="card-title">${item.title}</div>
                <div class="card-content">${item.content.substring(0, 50)}${item.content.length > 50 ? '...' : ''}</div>
                <div class="card-footer">
                    <span class="priority-tag priority-${item.priority.toLowerCase()}">${item.priority}</span>
                    <label class="checkbox-container">
                        <input type="checkbox" ${item.completed ? 'checked' : ''}>
                        <span class="checkmark"></span>
                    </label>
                </div>
            `;
            card.addEventListener('click', (e) => {
                if (!e.target.matches('input[type="checkbox"]')) {
                    openItemModal(index);
                }
            });
            card.querySelector('input[type="checkbox"]').addEventListener('change', (e) => {
                e.stopPropagation();
                toggleItemCompletion(index);
            });
            contentGrid.appendChild(card);
        }
    });
}

function toggleItemCompletion(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    items[index].completed = !items[index].completed;
    renderContent();
}

function openItemModal(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    const item = items[index];
    currentEditIndex = index;

    document.getElementById('modalTitle').textContent = item.title;
    document.getElementById('modalContent').textContent = item.content;
    
    document.getElementById('itemModal').style.display = 'block';
}

function updateItemModal(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    const item = items[index];
    
    document.getElementById('modalTitle').textContent = item.title;
    document.getElementById('modalContent').textContent = item.content;
}

function openEditModal(index = null) {
    currentEditIndex = index;
    const editModal = document.getElementById('editModal');
    const titleInput = document.getElementById('editTitle');
    const contentInput = document.getElementById('editContent');

    if (index !== null) {
        const items = currentMode === 'notes' ? notes : tasks;
        const item = items[index];
        titleInput.value = item.title;
        contentInput.value = item.content;
        setPriority(item.priority);
    } else {
        titleInput.value = '';
        contentInput.value = '';
        setPriority('Low');
    }

    editModal.style.display = 'block';
}

function saveItem() {
    const title = document.getElementById('editTitle').value;
    const content = document.getElementById('editContent').value;
    const priority = document.querySelector('.priority-option.selected').dataset.priority;

    if (currentEditIndex === null) {
        // Add new item
        const newItem = { title, content, priority, completed: false };
        if (currentMode === 'notes') {
            notes.push(newItem);
        } else {
            tasks.push(newItem);
        }
    } else {
        // Edit existing item
        const items = currentMode === 'notes' ? notes : tasks;
        items[currentEditIndex] = { ...items[currentEditIndex], title, content, priority };
    }

    document.getElementById('editModal').style.display = 'none';
    renderContent();
}

function togglePriority(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    const priorities = ['Low', 'Medium', 'High'];
    const currentPriority = items[index].priority || 'Low';
    const nextPriority = priorities[(priorities.indexOf(currentPriority) + 1) % 3];
    items[index].priority = nextPriority;
    renderContent();
}

function setPriority(priority) {
    document.querySelectorAll('.priority-option').forEach(option => {
        option.classList.remove('selected');
        if (option.dataset.priority === priority) {
            option.classList.add('selected');
        }
    });
}

async function summarizeContent(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    const item = items[index];
    
    if (!item.content || item.content.trim().length === 0) {
        alert('No content to summarize');
        return;
    }
    
    try {
        const response = await fetch('./summarize.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: item.content }),
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.summary) {
            item.content = result.summary;
            updateItemModal(index);
            renderContent();
        } else if (result.error) {
            throw new Error(result.error);
        } else {
            throw new Error('Unexpected response format');
        }
    } catch (error) {
        console.error('Error summarizing content:', error);
        alert('An error occurred while summarizing the content: ' + error.message);
    }
}
async function makeContentProfessional(index) {
    const items = currentMode === 'notes' ? notes : tasks;
    const item = items[index];
    
    if (!item.content || item.content.trim().length === 0) {
        alert('No content to make professional');
        return;
    }
    
    // Create modal for options
    const optionsModal = document.createElement('div');
    optionsModal.className = 'modal';
    optionsModal.innerHTML = `
        <div class="modal-content">
            <h2>Professionalization Options</h2>
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
            <button id="apply-professionalization" class="curved-button">Apply</button>
            <button class="curved-button cancel">Cancel</button>
        </div>
    `;
    
    document.body.appendChild(optionsModal);
    optionsModal.style.display = 'block';

    // Handle option submission
    document.getElementById('apply-professionalization').addEventListener('click', async () => {
        const options = {
            formality_level: document.getElementById('formality-level').value,
            preserve_contractions: document.getElementById('preserve-contractions').checked,
            industry_specific: document.getElementById('industry-specific').value || null
        };
        
        optionsModal.style.display = 'none';
        
        try {
            const response = await fetch('./professionalize.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    content: item.content,
                    action: 'professionalize',
                    options: options
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            
            if (result.professional) {
                item.content = result.professional;
                updateItemModal(index);
                renderContent();
                
                // Show applied options
                const appliedOptions = result.applied_options;
                alert(`Content professionalized with options:\n` +
                      `Formality: ${appliedOptions.formality_level}\n` +
                      `Preserved Contractions: ${appliedOptions.preserve_contractions}\n` +
                      `Industry: ${appliedOptions.industry_specific || 'None'}`);
            } else if (result.error) {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error('Error making content professional:', error);
            alert('An error occurred while making the content professional: ' + error.message);
        } finally {
            optionsModal.remove();
        }
    });

    // Handle cancel
    optionsModal.querySelector('.cancel').addEventListener('click', () => {
        optionsModal.remove();
    });
}

// Add some CSS for the options modal
const style = document.createElement('style');
style.textContent = `
    .option-group {
        margin-bottom: 15px;
    }
    .option-group label {
        display: block;
        margin-bottom: 5px;
    }
    .option-group select,
    .option-group input[type="checkbox"] {
        margin-top: 5px;
    }
    .modal .curved-button {
        margin-right: 10px;
    }
    .modal .curved-button.cancel {
        background-color: #ccc;
    }
`;
document.head.appendChild(style);

// Profile menu functionality
const profileButton = document.getElementById('profileButton');
const profileMenu = document.getElementById('profileMenu');

profileButton.addEventListener('click', function() {
    profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', function(e) {
    if (!profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
        profileMenu.style.display = 'none';
    }
});