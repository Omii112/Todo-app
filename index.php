<?php
require_once 'includes/config.php';

// Initialize database
$db = initDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_todo'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $stmt = $db->prepare('INSERT INTO todos (task) VALUES (:task)');
            $stmt->bindValue(':task', $task, SQLITE3_TEXT);
            $stmt->execute();
        }
    } elseif (isset($_POST['toggle_todo'])) {
        $id = (int)$_POST['todo_id'];
        $completed = (int)$_POST['completed'];
        $stmt = $db->prepare('UPDATE todos SET completed = :completed WHERE id = :id');
        $stmt->bindValue(':completed', $completed, SQLITE3_INTEGER);
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif (isset($_POST['delete_todo'])) {
        $id = (int)$_POST['todo_id'];
        $stmt = $db->prepare('DELETE FROM todos WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
    }
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all todos
$todos = $db->query('SELECT * FROM todos ORDER BY created_at DESC');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Todo App</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <!-- Header -->
        <header class="text-center mb-12">
            <h1 class="text-4xl font-bold text-indigo-600 mb-2">
                <i class="fas fa-tasks mr-2"></i>Todo App
            </h1>
            <p class="text-gray-600">Stay organized and get things done</p>
        </header>

        <!-- Add Todo Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-8 transform transition-all hover:shadow-lg">
            <form method="POST" class="flex gap-4">
                <input type="text" 
                       name="task" 
                       placeholder="What needs to be done?" 
                       class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       required>
                <button type="submit" 
                        name="add_todo"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i> Add
                </button>
            </form>
        </div>

        <!-- Todo Stats -->
        <?php
        $total = $db->querySingle('SELECT COUNT(*) FROM todos');
        $completed = $db->querySingle('SELECT COUNT(*) FROM todos WHERE completed = 1');
        $pending = $total - $completed;
        ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                <div class="text-3xl font-bold text-indigo-600"><?php echo $total; ?></div>
                <div class="text-gray-500 text-sm uppercase tracking-wider mt-1">Total Tasks</div>
            </div>
            <div class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                <div class="text-3xl font-bold text-green-600"><?php echo $completed; ?></div>
                <div class="text-gray-500 text-sm uppercase tracking-wider mt-1">Completed</div>
            </div>
            <div class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                <div class="text-3xl font-bold text-yellow-600"><?php echo $pending; ?></div>
                <div class="text-gray-500 text-sm uppercase tracking-wider mt-1">Pending</div>
            </div>
        </div>

        <!-- Todo List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800">My Tasks</h2>
            </div>
            
            <?php if ($todos->numColumns() > 0): ?>
                <ul class="divide-y divide-gray-100">
                    <?php while ($todo = $todos->fetchArray(SQLITE3_ASSOC)): ?>
                        <li class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 flex items-center group">
                            <form method="POST" class="flex items-center flex-1">
                                <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                <input type="hidden" name="completed" value="<?php echo $todo['completed'] ? '0' : '1'; ?>">
                                <button type="submit" 
                                        name="toggle_todo"
                                        class="w-6 h-6 rounded-full border-2 <?php echo $todo['completed'] ? 'bg-green-500 border-green-500' : 'border-gray-300'; ?> flex items-center justify-center mr-4 focus:outline-none transition-colors duration-200"
                                        aria-label="<?php echo $todo['completed'] ? 'Mark as incomplete' : 'Mark as complete'; ?>">
                                    <?php if ($todo['completed']): ?>
                                        <i class="fas fa-check text-white text-xs"></i>
                                    <?php endif; ?>
                                </button>
                                <span class="flex-1 text-gray-800 <?php echo $todo['completed'] ? 'line-through text-gray-400' : ''; ?>">
                                    <?php echo htmlspecialchars($todo['task']); ?>
                                </span>
                                <span class="text-xs text-gray-400 mr-3">
                                    <?php echo date('M j, Y', strtotime($todo['created_at'])); ?>
                                </span>
                                <form method="POST" class="ml-2">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo['id']; ?>">
                                    <button type="submit" 
                                            name="delete_todo"
                                            class="text-red-400 hover:text-red-600 transition-colors duration-200 opacity-0 group-hover:opacity-100 focus:opacity-100 focus:outline-none"
                                            onclick="return confirm('Are you sure you want to delete this task?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                    <p>No tasks yet. Add one above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center text-gray-500 text-sm mt-12 pb-8">
        <p>© <?php echo date('Y'); ?> Todo App. All rights reserved.</p>
    </footer>

    <script>
        // Add smooth scrolling to top when clicking the header
        document.querySelector('header').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>