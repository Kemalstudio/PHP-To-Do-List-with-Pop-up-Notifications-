This code creates a "To-Do List" web page where you can add, complete, restore, and delete tasks. Here's a breakdown of what each part does, in English:

Overall Idea:
Users can input tasks. Active tasks are shown in one list, and completed ones in another (the "bin"). User actions (adding, completing, etc.) are handled by PHP, data is stored in the PHP session, and notifications about these actions appear as pop-up messages (toasts) using JavaScript and Bootstrap.

1. PHP (Server-Side Logic)

session_start();:

Starts or resumes a session. Sessions allow storing user-specific information (in this case, the task lists) across different page requests to the server.

Initializing Task Lists in the Session:

if (!isset($_SESSION['active_tasks'])) {
    $_SESSION['active_tasks'] = [];
}
if (!isset($_SESSION['completed_tasks'])) {
    $_SESSION['completed_tasks'] = [];
}


If there are no arrays for active (active_tasks) or completed (completed_tasks) tasks in the current session, empty arrays are created. This happens on the first visit or if the session was cleared.

function set_toast($message, $type = 'info'):

A helper function to set a message that will be shown to the user as a pop-up notification (toast).

$_SESSION['toast_message'] = $message;: Stores the message text in the session.

$_SESSION['toast_type'] = $type;: Stores the message type (e.g., 'success', 'danger', 'info', 'warning') in the session. This is used to style the notification.

Handling POST Requests (if ($_SERVER['REQUEST_METHOD'] === 'POST')):

This block of code executes when the user submits data to the server (e.g., clicks the "Add" or "Complete" button).

Adding a Task (isset($_POST['add_task'])):

$newTask = trim($_POST['task_name']);: Gets the new task text from the form and removes extra spaces.

if (!empty($newTask)): If the task is not empty:

array_unshift($_SESSION['active_tasks'], $newTask);: Adds the new task to the beginning of the active tasks array.

set_toast(...): Sets a success message.

else: If the task is empty, sets a warning message.

Completing a Task (isset($_POST['complete_task'])):

Gets the task's index from a hidden form field.

Moves the task from the active list to the completed list (to the beginning of the list).

array_values() re-indexes the array to avoid "gaps" in indices after removing an element.

Sets an appropriate notification.

Restoring a Task (isset($_POST['restore_task'])):

Similar to completing, but moves a task from the completed list back to the active list.

Permanently Deleting a Task (isset($_POST['delete_permanently'])):

Removes a task from the completed list.

Clearing All Completed Tasks (isset($_POST['clear_completed_tasks'])):

Empties the array of completed tasks.

htmlspecialchars($text, ENT_QUOTES, 'UTF-8'): An important security function. It converts special HTML characters (like <, >) into their safe equivalents (like &lt;, &gt;). This prevents XSS (Cross-Site Scripting) attacks, where an attacker might try to inject malicious code through input fields.

header("Location: " . $_SERVER['PHP_SELF']); exit();:

A very important line! This implements the Post/Redirect/Get (PRG) pattern.

After processing a POST request (e.g., adding a task), the server redirects the browser to the same page, but via a GET request.

Why?

Prevents form resubmission if the user refreshes the page (F5).

Allows the page to be displayed "cleanly" after an action, and the notification (toast), stored in the session, will be shown by JavaScript on this "clean" page.

2. HTML (Web Page Structure)

<head>:

Connects Bootstrap styles (bootstrap.min.css) for appearance.

Connects Bootstrap Icons (bootstrap-icons.css).

Includes inline CSS styles (<style>...</style>) for additional customization and animations.

<body>:

Container (<div class="container">): Limits the content width.

Card for Adding Tasks:

Title "Мой список дел" (My To-Do List).

A form (<form method="POST" ...>) with an input field (<input type="text" name="task_name" ...>) and an "Add" button.

Card for Active Tasks:

Title "Активные задачи" (Active Tasks).

PHP code to display the list of active tasks:

<?php if (!empty($_SESSION['active_tasks'])): ?>: Checks if there are any active tasks.

<?php foreach ($_SESSION['active_tasks'] as $index => $task): ?>: A loop to output each task.

<span><?php echo htmlspecialchars($task, ENT_QUOTES, 'UTF-8'); ?></span>: Displays the task text (safely).

A form with a "Complete" button, containing a hidden task_index field to pass the task's index.

Card for Completed Tasks (Bin):

Similar to active tasks, but for $_SESSION['completed_tasks'].

"Restore" and "Delete Permanently" buttons.

"Clear All" button (if the bin is not empty).

<div class="toast-container ...">: An empty div that JavaScript will use to display pop-up notifications (toasts). The Bootstrap classes position-fixed top-0 start-50 translate-middle-x p-3 position it at the top center of the screen.

Linking Bootstrap JavaScript (bootstrap.bundle.min.js): Necessary for Bootstrap's interactive components, including toasts.

Inline JavaScript (<script>...</script>): Logic for displaying notifications.

3. CSS (Styles and Animations)

General Styles: Page background, font, maximum container width.

Card Animation (.card-animated, @keyframes fadeInUp): Cards smoothly appear when the page loads.

Task Item Styles: Appearance of list items, action buttons, strikethrough for completed tasks.

Toast Notification Styles:

.toast-container { z-index: 1090; }: Sets a high z-index so notifications appear above other elements.

.toast.toast-slide-in: Initial state of the notification (transparent and shifted upwards).

.toast.toast-slide-in.show: Visible state of the notification (opaque, in its normal position).

transition: Defines the smoothness of the notification's appearance/disappearance animation.

.toast-body .bi: Styles for the icon inside the notification.

4. JavaScript (Interactivity and Notifications)

document.addEventListener('DOMContentLoaded', function () { ... });:

The code inside this function will only run after the entire HTML page structure has loaded.

PHP Block Inside JavaScript:

<?php
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])) {
    // ... prepare variables $toast_message, $bs_class, $icon_class ...
    echo "showToast('{$toast_message}', '{$bs_class}', '{$icon_class}');";

    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_type']);
}
?>
IGNORE_WHEN_COPYING_START
content_copy
download
Use code with caution.
JavaScript
IGNORE_WHEN_COPYING_END

This PHP code runs on the server before the page is sent to the browser.

If there's a notification message in the session (set earlier by the set_toast() function):

PHP generates JavaScript code that calls the showToast() function with the necessary parameters (message text, CSS class for color, CSS class for icon).

After generating the showToast() call, PHP removes the message from the session (unset(...)). This is important so the notification doesn't show up again on a simple page refresh.

function showToast(message, bs_class, icon_class):

This JavaScript function is responsible for creating and displaying the notification.

Finds the div.toast-container.

Creates HTML markup for the notification, using the passed parameters (message, bs_class, icon_class).

Adds this markup to the toast-container.

Initializes the Bootstrap Toast component: new bootstrap.Toast(toastElement).

Adds an event listener hidden.bs.toast to remove the notification's HTML element from the DOM after it hides.

Shows the notification: toast.show(). It will appear with a smooth animation thanks to CSS.

In summary, when you perform an action:

The form sends data to the server (POST request).

PHP processes the data, updates the task lists in the session, and stores a notification message in the session (set_toast).

PHP redirects the browser to the same page (GET request).

When the page loads, PHP sees the notification message in the session.

PHP inserts a JavaScript call to showToast() with this message into the page and then removes the message from the session.

JavaScript, after the page loads, executes showToast(), and you see an animated notification.
