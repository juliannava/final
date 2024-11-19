<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
// any stupid coment 
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

//HERE IS LAB18

$host = 'localhost'; 
$dbname = 'books'; 
$user = 'julian'; 
$pass = 'julian';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle book search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, player, position, club FROM books WHERE player LIKE :player';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['player' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['player']) && isset($_POST['position']) && isset($_POST['club'])) {
        // Insert new entry
        $player = htmlspecialchars($_POST['player']);
        $position = htmlspecialchars($_POST['position']);
        $club = htmlspecialchars($_POST['club']);
        
        $insert_sql = 'INSERT INTO books (player, position, club) VALUES (:player, :position, :club)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['player' => $player, 'position' => $position, 'club' => $club]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM books WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    }
}

// Get all books for main table
$sql = 'SELECT id, player, position, club FROM books';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Players Banning</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Players Banning</h1>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a player to Ban</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by player:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Player</th>
                                    <th>Position</th>
                                    <th>Club</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['player']); ?></td>
                                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                                    <td><?php echo htmlspecialchars($row['club']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <input type="submit" value="Ban!">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No players found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All Books in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Player</th>
                    <th>Position</th>
                    <th>Club</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['player']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td><?php echo htmlspecialchars($row['club']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Ban!">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Condemn a player Today</h2>
        <form action="index5.php" method="post">
            <label for="player">Player:</label>
            <input type="text" id="player" name="player" required>
            <br><br>
            <label for="position">Position:</label>
            <input type="text" id="position" name="position" required>
            <br><br>
            <label for="club">Club:</label>
            <input type="text" id="club" name="club" required>
            <br><br>
            <input type="submit" value="Condemn player">
        </form>
    </div>
</body>
</html>