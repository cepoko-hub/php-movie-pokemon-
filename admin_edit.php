<?php
session_start();

// Vérifier si l'utilisateur est connecté en tant qu'admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PokemonMovies";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$uploadDir = "asset/img/";

// Ajouter un film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];

    // Vérification et upload de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Vérifier l'extension de l'image
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid() . ".$imageExt";
            $imagePath = $uploadDir . $newImageName;
            move_uploaded_file($imageTmpName, $imagePath);
        } else {
            $error = "Le format d'image n'est pas supporté.";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO Movies (title, release_date, duration, rating, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssids", $title, $release_date, $duration, $rating, $imagePath);
        $stmt->execute();
    }
}

// Modifier un film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];

    // Récupérer l'ancienne image pour suppression
    $sql = "SELECT image_url FROM Movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $oldImage = $movie['image_url'];

    // Vérification et upload de la nouvelle image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        // Vérifier l'extension de l'image
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid() . ".$imageExt";
            $imagePath = $uploadDir . $newImageName;
            move_uploaded_file($imageTmpName, $imagePath);

            // Supprimer l'ancienne image (sauf si c'est `default_image.jpg`)
            if ($oldImage !== $uploadDir . "default_image.jpg" && file_exists($oldImage)) {
                unlink($oldImage);
            }
        } else {
            $error = "Le format d'image n'est pas supporté.";
        }
    } else {
        $imagePath = $oldImage; 
    }

    if (empty($error)) {
        $sql = "UPDATE Movies SET title = ?, release_date = ?, duration = ?, rating = ?, image_url = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidsi", $title, $release_date, $duration, $rating, $imagePath, $movie_id);
        $stmt->execute();
    }
}

// Supprimer un film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movie'])) {
    $movie_id = $_POST['movie_id'];

    // Supprimer l'image associée (sauf si c'est `default_image.jpg`)
    $sql = "SELECT image_url FROM Movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $imagePath = $movie['image_url'];

    if ($imagePath !== $uploadDir . "default_image.jpg" && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Supprimer le film de la base de données
    $sql = "DELETE FROM Movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
}

// Récupérer tous les films
$sql = "SELECT * FROM Movies";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier les films</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="index.php">Home</a></li>
        </ul>
    </nav>
    <div class="movie-container">

        <!-- Formulaire pour ajouter un film -->
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Titre" required>
            <input type="date" name="release_date" placeholder="Date de sortie" required>
            <input type="number" name="duration" placeholder="Durée (en minutes)" required>
            <input type="number" step="0.1" name="rating" placeholder="Note (sur 10)" required>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif" required>
            <button type="submit" name="add_movie">Ajouter</button>
        </form>

        <!-- Liste des films avec options de modification et suppression -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 1rem;">
                    <input type="hidden" name="movie_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                    <input type="date" name="release_date" value="<?php echo $row['release_date']; ?>" required>
                    <input type="number" name="duration" value="<?php echo $row['duration']; ?>" required>
                    <input type="number" step="0.1" name="rating" value="<?php echo $row['rating']; ?>" required>
                    <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif">
                    <button type="submit" name="edit_movie">Modifier</button>
                    <button type="submit" name="delete_movie">Supprimer</button>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucun film trouvé.</p>
        <?php endif; ?>
    </div>
</body>
</html>
