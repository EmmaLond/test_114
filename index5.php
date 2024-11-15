<?php

session_start();
require_once 'auth.php';

// Check if user is logged in
//ADD COMMENT
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$host = 'localhost'; 
$dbname = 'test'; 
$user = 'emma'; 
$pass = '1163';
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
    $search_sql = 'SELECT entry_id, author, quote, category FROM test WHERE category LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['author']) && isset($_POST['quote']) && isset($_POST['category'])) {
        // Insert new entry
        $author = htmlspecialchars($_POST['author']);
        $quote = htmlspecialchars($_POST['quote']);
        $category = htmlspecialchars($_POST['category']);
        
        $insert_sql = 'INSERT INTO quotes (author, quote, category) VALUES (:author,  quote, :category)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['author' => $author,  'quote' =>  $quote, 'category' => $category]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        
        $delete_sql = 'DELETE FROM quotes WHERE entry_id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['entry_id' => $delete_id]);
    }
}

// Get all quotes for main table
$sql = 'SELECT entry_id, author, quote, category FROM quotes';
$stmt = $pdo->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
     <quote>Find Motivational Quotes </quote>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero quote">Find Motivational Quotes </h1>
        <p class="hero-su quote">"Life doesn't have to be totally doom and gloom"</p>
        
        <!-- Search moved to hero section -->
        <div class="hero-search">
            <h2>Search for a Quote to Use</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by category:</label>
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
                                    <th>Author</th>
                                    <th>Quote</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['entry_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                                    <td><?php echo htmlspecialchars($row[ 'quote']); ?></td>
                                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td>
                                        <form action="index5.php" method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['entry_id']; ?>">
                                            <input type="submit" value="Done!">
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No quotes found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table section with container -->
    <div class="table-container">
        <h2>All quotes in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Author</th>
                    <th>Quote</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['entry_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                    <td><?php echo htmlspecialchars($row['quote']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td>
                        <form action="index5.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Done!">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form section with container -->
    <div class="form-container">
        <h2>Add Your Own Quote</h2>
        <form action="index5.php" method="post">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" required>
            <br><br>
            <label for= "quote">Quote:</label>
            <input type="text" id= quote" name= quote" required>
            <br><br>
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>
            <br><br>
            <input type="submit" value="Add Quote">
        </form>
    </div>
</body>
</html>