<?php
session_start();

if (!isset($_SESSION['active_tasks'])) {
    $_SESSION['active_tasks'] = [];
}
if (!isset($_SESSION['completed_tasks'])) {
    $_SESSION['completed_tasks'] = [];
}

function set_toast($message, $type = 'info') { 
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $newTask = trim($_POST['task_name']);
        if (!empty($newTask)) {
            array_unshift($_SESSION['active_tasks'], $newTask);
            set_toast("Задача '" . htmlspecialchars($newTask, ENT_QUOTES, 'UTF-8') . "' успешно добавлена!", 'success');
        } else {
            set_toast("Название задачи не может быть пустым.", 'warning');
        }
    }
    elseif (isset($_POST['complete_task'])) {
        $taskIndexToComplete = $_POST['task_index'];
        if (isset($_SESSION['active_tasks'][$taskIndexToComplete])) {
            $completedTask = $_SESSION['active_tasks'][$taskIndexToComplete];
            array_unshift($_SESSION['completed_tasks'], $completedTask);
            unset($_SESSION['active_tasks'][$taskIndexToComplete]);
            $_SESSION['active_tasks'] = array_values($_SESSION['active_tasks']);
            set_toast("Задача '" . htmlspecialchars($completedTask, ENT_QUOTES, 'UTF-8') . "' отмечена как выполненная.", 'info');
        }
    }
    elseif (isset($_POST['restore_task'])) {
        $taskIndexToRestore = $_POST['task_index'];
        if (isset($_SESSION['completed_tasks'][$taskIndexToRestore])) {
            $restoredTask = $_SESSION['completed_tasks'][$taskIndexToRestore];
            array_unshift($_SESSION['active_tasks'], $restoredTask);
            unset($_SESSION['completed_tasks'][$taskIndexToRestore]);
            $_SESSION['completed_tasks'] = array_values($_SESSION['completed_tasks']);
            set_toast("Задача '" . htmlspecialchars($restoredTask, ENT_QUOTES, 'UTF-8') . "' восстановлена.", 'info');
        }
    }
    elseif (isset($_POST['delete_permanently'])) {
        $taskIndexToDelete = $_POST['task_index'];
        if (isset($_SESSION['completed_tasks'][$taskIndexToDelete])) {
            $deletedTask = $_SESSION['completed_tasks'][$taskIndexToDelete];
            unset($_SESSION['completed_tasks'][$taskIndexToDelete]);
            $_SESSION['completed_tasks'] = array_values($_SESSION['completed_tasks']);
            set_toast("Задача '" . htmlspecialchars($deletedTask, ENT_QUOTES, 'UTF-8') . "' удалена навсегда.", 'danger');
        }
    }
    elseif (isset($_POST['clear_completed_tasks'])) {
        $_SESSION['completed_tasks'] = [];
        set_toast("Все завершенные задачи удалены из корзины.", 'danger');
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ToDo List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #e9ecef;
            padding-top: 30px;
            padding-bottom: 30px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 750px;
        }
        .card {
            border: none;
            transition: all 0.3s ease-in-out;
        }

        .card-animated {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .card-animated:nth-child(1) { animation-delay: 0.1s; }
        .card-animated:nth-child(2) { animation-delay: 0.2s; }
        .card-animated:nth-child(3) { animation-delay: 0.3s; }


        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.85rem 1.25rem;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            transition: background-color 0.2s ease-in-out;
        }
        .list-group-flush .list-group-item:last-child {
            border-bottom: none;
        }
        .task-item:hover {
            background-color: #f8f9fa;
        }
        .task-actions button {
            margin-left: 8px;
            transition: all 0.2s ease-in-out;
        }
        .task-actions button:hover {
            transform: scale(1.1);
        }
        .completed-task span {
            text-decoration: line-through;
            color: #6c757d;
            font-style: italic;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .card-header {
            font-weight: 500;
        }

        .toast-container {
            z-index: 1090;
        }

        .toast.toast-slide-in {
            opacity: 0;
            transform: translateY(-100%); 
            transition: opacity 0.3s ease-in-out, transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .toast.toast-slide-in.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast-body .bi { 
            font-size: 1.25rem; 
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card shadow-lg mb-4 card-animated">
            <div class="card-body p-4">
                <h1 class="card-title text-center mb-4 display-6">Мой список дел</h1>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-3">
                    <div class="input-group input-group-lg">
                        <input type="text" class="form-control" name="task_name" placeholder="Напишите..." required>
                        <button class="btn btn-primary" type="submit" name="add_task">
                            <i class="bi bi-plus-circle-fill"></i> Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-lg mb-4 card-animated">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">
                    <i class="bi bi-lightning-charge-fill"></i> Активные задачи</h2>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (!empty($_SESSION['active_tasks'])): ?>
                    <?php foreach ($_SESSION['active_tasks'] as $index => $task): ?>
                        <li class="list-group-item task-item">
                            <span><?php echo htmlspecialchars($task, ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="task-actions">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                                    <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="complete_task" class="btn btn-outline-success btn-sm" title="Завершить">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-center text-muted py-3">Задачи отсутствуют. Добавьте первую!</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card shadow-lg card-animated">
            <div class="card-header bg-dark text-white">
                <h2 class="h5 mb-0"><i class="bi bi-archive-fill"></i> Корзина (Завершенные)</h2>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (!empty($_SESSION['completed_tasks'])): ?>
                    <?php foreach ($_SESSION['completed_tasks'] as $index => $task): ?>
                        <li class="list-group-item task-item completed-task">
                            <span><?php echo htmlspecialchars($task, ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="task-actions">
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                                    <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="restore_task" class="btn btn-outline-warning btn-sm" title="Восстановить">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                                    <input type="hidden" name="task_index" value="<?php echo $index; ?>">
                                    <button type="submit" name="delete_permanently" class="btn btn-outline-danger btn-sm" title="Удалить навсегда"
                                            onclick="return confirm('Вы уверены, что хотите удалить эту задачу навсегда?');">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-center text-muted py-3">Корзина пуста.</li>
                <?php endif; ?>
            </ul>
            <?php if (!empty($_SESSION['completed_tasks'])): ?>
            <div class="card-footer text-end bg-light">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: inline;">
                    <button type="submit" name="clear_completed_tasks" class="btn btn-danger btn-sm"
                            onclick="return confirm('Вы уверены, что хотите очистить всю корзину? Это действие необратимо.');">
                        <i class="bi bi-trash2-fill"></i> Очистить всё
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            <?php
            if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])) {
                $toast_message = htmlspecialchars(addslashes($_SESSION['toast_message']), ENT_QUOTES, 'UTF-8');
                $toast_type = $_SESSION['toast_type']; 

                $bs_class = '';
                $icon_class = '';
                switch ($toast_type) {
                    case 'success':
                        $bs_class = 'text-bg-success';
                        $icon_class = 'bi-check-circle-fill';
                        break;
                    case 'danger':
                        $bs_class = 'text-bg-danger';
                        $icon_class = 'bi-x-octagon-fill';
                        break;
                    case 'warning':
                        $bs_class = 'text-bg-warning';
                        $icon_class = 'bi-exclamation-triangle-fill';
                        break;
                    case 'info':
                    default:
                        $bs_class = 'text-bg-info';
                        $icon_class = 'bi-info-circle-fill';
                        break;
                }

                echo "showToast('{$toast_message}', '{$bs_class}', '{$icon_class}');";

                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
            }
            ?>
        });

        function showToast(message, bs_class, icon_class) {
            const toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) return;

            const toastId = 'toast-' + Math.random().toString(36).substr(2, 9);

            const toastHTML = `
                <div id="${toastId}" class="toast toast-slide-in align-items-center ${bs_class} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center">
                            <i class="bi ${icon_class} me-2"></i>
                            <span>${message}</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHTML);

            const toastElement = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastElement); 

            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });

            toast.show();
        }
    </script>
</body>
</html>