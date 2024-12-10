<?php
if (!isset($_GET['movie_id'])) {
    die("Film non spécifié.");
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PokemonMovies";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$movie_id = $_GET['movie_id'];
$sql = "SELECT title, movie_file FROM Movies WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$movie = $result->fetch_assoc();

if (!$movie || !$movie['movie_file']) {
    die("Film non disponible.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['title']); ?> - Lecture</title>
    <link rel="stylesheet" href="style-watch.css">
</head>
<body>
    <div class="movie-player">
        <h1><?php echo htmlspecialchars($movie['title']); ?></h1>
        <video controls autoplay width="800">
            <source src="<?php echo htmlspecialchars($movie['movie_file']); ?>" type="video/mp4">
            Votre navigateur ne supporte pas la lecture de vidéos.
        </video>
        <a href="index.php" class="back-button">Retour à l'accueil</a>
    </div>
</body>
</html>
