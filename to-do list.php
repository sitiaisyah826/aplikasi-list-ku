<?php
// Konfigurasi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todolist";

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Buat tabel Database Dengan Script PHP
$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    status_task ENUM('Biasa','Cukup','Penting') DEFAULT 'Cukup',
    status_completed ENUM('Selesai','Belum Selesai') DEFAULT 'Belum Selesai',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    task_date DATE
)";
$conn->query($sql);

// Tambah data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_task'])) {
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $status_task = $conn->real_escape_string($_POST['status_task']);
    $status_completed = $conn->real_escape_string($_POST['status_completed']);
    $task_date = $conn->real_escape_string($_POST['task_date']);

    if (!empty($task_name) && !empty($status_task) && !empty($task_date)) {
        $stmt = $conn->prepare("INSERT INTO tasks (task_name, status_task, status_completed, task_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $task_name, $status_task, $status_completed, $task_date);
        $stmt->execute();
        $stmt->close();
    }
}

// Update data
// Memperbarui data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_task'])) {
    $id = (int)$_POST['task_id'];
    $task_name = $conn->real_escape_string($_POST['task_name']);
    $status_task = $conn->real_escape_string($_POST['status_task']);
    $status_completed = $conn->real_escape_string($_POST['status_completed']);
    $task_date = $conn->real_escape_string($_POST['task_date']);

    if (!empty($task_name) && !empty($status_task) && !empty($task_date)) {
        $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, status_task = ?, status_completed = ?, task_date = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $task_name, $status_task, $status_completed, $task_date, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Hapus data
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Ambil semua data
$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aplikasi List-Ku</title>
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Times New Roman', sans-serif;
    background: linear-gradient(180deg, #F8F8FF,#FFDEAD);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: start;
    color: #fff;
    padding: 30px;
}
h1 {
   font-size: 3rem;
   margin-bottom: 20px;
   color: black;
}
.form-container {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    justify-content: center;
}
input[type="text"], select, input[type="date"] {
    padding: 12px;
    font-size: 1rem;
    border-radius: 8px;
    border: none;
    width: 220px;
    outline: none;
}
button {
    background-color: #F4A460;
    color: white;
    border: none;
    padding: 12px 25px;
    font-size: 1rem;
    border-radius: 8px;
    cursor: pointer;
}
button:hover {
    background-color: #FFE4B5
}
.task-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
    gap: 20px;
    width: 90%;
    max-width: 900px;
}
.task-item {
    background-color: #fff;
    color: #333;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
.task-item button {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    margin-top: 10px;
}
.task-item .delete {
    background-color: #F4A460
}
.task-item .delete:hover {
    background-color: #FFA07A;
}
.task-item .task-date {
    font-size: 0.9rem;
    color: #666;
    margin-top: 10px;
}
</style>
</head>
<body>

<img src="logo.todolist.jpeg" width="155" height="100">
<h1>Aplikasi List-Ku</h1>

<!-- Form tambah task -->
<div class="form-container">
    <form method="POST" action="">
        <input type="text" name="task_name" placeholder="Kegiatan Baru" required>
        <select name="status_task" required>
            <option value="Biasa">Biasa</option>
            <option value="Cukup">Cukup Penting</option>
            <option value="Penting">Penting Sekali</option>
        </select>
        <select name="status_completed" required>
            <option value="Belum Selesai">Belum Selesai</option>
            <option value="Selesai">Selesai</option>
        </select>
        <input type="date" name="task_date" required>
        <button type="submit" name="add_task">Tambah List</button>
    </form>
</div>

<!-- List tugas -->
<div class="task-list">
    <?php while ($row = $result->fetch_assoc()): ?>
    <div class="task-item">
        <form method="POST" action="">
            <input type="hidden" name="task_id" value="<?= $row['id'] ?>">
            <input type="text" name="task_name" value="<?= htmlspecialchars($row['task_name']) ?>" required>
            <select name="status_task" required>
                <option value="Biasa" <?= $row['status_task'] == 'Biasa' ? 'selected' : '' ?>>Biasa</option>
                <option value="Cukup" <?= $row['status_task'] == 'Cukup' ? 'selected' : '' ?>>Cukup</option>
                <option value="Penting" <?= $row['status_task'] == 'Penting' ? 'selected' : '' ?>>Penting</option>
            </select>
            <select name="status_completed" required>
                <option value="Belum Selesai" <?= $row['status_completed'] == 'Belum Selesai' ? 'selected' : '' ?>>Belum Selesai</option>
                <option value="Selesai" <?= $row['status_completed'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
            <input type="date" name="task_date" value="<?= $row['task_date'] ?>" required>
            <button type="submit" name="edit_task">Edit</button>
        </form>
        <form method="GET" action="" onsubmit="return confirm('Apakah yakin menghapus list?');">
            <input type="hidden" name="delete" value="<?= $row['id'] ?>">
            <button type="submit" class="delete">Hapus</button>
        </form>
        <div class="task-date">Due Date: <?= date("d M Y", strtotime($row['task_date'])) ?></div>
    </div>
    <?php endwhile; ?>
</div>

</body>
</html>

<?php
$conn->close();
?>

todolist.php
Buka dengan 
Bagikan 

Menampilkan todolist.php