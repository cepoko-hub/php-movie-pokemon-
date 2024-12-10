<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PokemonMovies";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movie'])) {
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];
    $desc_film = $_POST['desc_film'];
    $imagePath = "asset/img/default_image.jpg";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid() . ".$imageExt";
            $imagePath = "asset/img/" . $newImageName;
            move_uploaded_file($imageTmpName, $imagePath);
        } else {
            $error = "Le format d'image n'est pas supporté.";
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO Movies (title, release_date, duration, rating, image_url, desc_film, creator) VALUES (?, ?, ?, ?, ?, ?, 'admin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidsis", $title, $release_date, $duration, $rating, $imagePath, $desc_film);
        if ($stmt->execute()) {
            $success = "Film ajouté avec succès !";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_movie'])) {
    $movie_id = $_POST['movie_id'];
    $title = $_POST['title'];
    $release_date = $_POST['release_date'];
    $duration = $_POST['duration'];
    $rating = $_POST['rating'];
    $desc_film = $_POST['desc_film'];

    $sql = "SELECT image_url FROM Movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $oldImage = $movie['image_url'];
    $imagePath = $oldImage;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpName = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'webp', 'gif'];
        if (in_array($imageExt, $allowedExtensions)) {
            $newImageName = uniqid() . ".$imageExt";
            $imagePath = "asset/img/" . $newImageName;
            move_uploaded_file($imageTmpName, $imagePath);

            if ($oldImage !== "asset/img/default_image.jpg" && file_exists($oldImage)) {
                unlink($oldImage);
            }
        } else {
            $error = "Le format d'image n'est pas supporté.";
        }
    }

    if (empty($error)) {
        $sql = "UPDATE Movies SET title = ?, release_date = ?, duration = ?, rating = ?, image_url = ?, desc_film = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidsii", $title, $release_date, $duration, $rating, $imagePath, $desc_film, $movie_id);
        if ($stmt->execute()) {
            $success = "Film modifié avec succès !";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movie'])) {
    $movie_id = $_POST['movie_id'];

    $sql = "SELECT image_url FROM Movies WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();

    if ($movie) {
        $imagePath = $movie['image_url'];
        if ($imagePath !== "asset/img/default_image.jpg" && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $sql = "DELETE FROM Movies WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        if ($stmt->execute()) {
            $success = "Film supprimé avec succès !";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    } else {
        $error = "Impossible de supprimer ce film.";
    }
}

if (isset($_FILES['movie_file']) && $_FILES['movie_file']['error'] === UPLOAD_ERR_OK) {
    $movieTmpName = $_FILES['movie_file']['tmp_name'];
    $movieName = basename($_FILES['movie_file']['name']);
    $movieExt = strtolower(pathinfo($movieName, PATHINFO_EXTENSION));

    if ($movieExt === 'mp4') {
        $newMovieName = uniqid() . ".mp4";
        $moviePath = "asset/movies/" . $newMovieName;

        if (!file_exists("asset/movies/")) {
            mkdir("asset/movies/", 0777, true);
        }

        $sql = "SELECT movie_file FROM Movies WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $movie = $result->fetch_assoc();

        $oldMoviePath = isset($movie['movie_file']) ? $movie['movie_file'] : null;

        if (move_uploaded_file($movieTmpName, $moviePath)) {
            if ($oldMoviePath && $oldMoviePath !== "asset/movies/default_video.mp4" && file_exists($oldMoviePath)) {
                unlink($oldMoviePath);
            }

            $sql = "UPDATE Movies SET movie_file = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $moviePath, $movie_id);
            $stmt->execute();
        } else {
            $error = "Impossible de déplacer le fichier vidéo téléchargé.";
        }
    } else {
        $error = "Le format de fichier n'est pas supporté.";
    }
}

$sql = "SELECT * FROM Movies";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier les films</title>
    <link rel="stylesheet" href="style-admin-edit.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
    <div class="movie-container">
        <h1>Modifier les films</h1>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <h2>Ajouter un film</h2>
            <input type="text" name="title" placeholder="Titre" required>
            <input type="date" name="release_date" placeholder="Date de sortie" required>
            <input type="number" name="duration" placeholder="Durée (en minutes)" required>
            <input type="number" step="0.1" name="rating" placeholder="Note (sur 10)" required>
            <textarea name="desc_film" placeholder="Description du film" required></textarea>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif">
            <button type="submit" name="add_movie">Ajouter</button>
        </form>

        <?php if ($result->num_rows > 0): ?>
            <h2>Liste des films</h2>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="POST" enctype="multipart/form-data" class="movie-form">
                    <input type="hidden" name="movie_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                    <input type="date" name="release_date" value="<?php echo $row['release_date']; ?>" required>
                    <input type="number" name="duration" value="<?php echo $row['duration']; ?>" required>
                    <input type="number" step="0.1" name="rating" value="<?php echo $row['rating']; ?>" required>
                    <textarea name="desc_film" required><?php echo htmlspecialchars($row['desc_film']); ?></textarea>
                    <p class="creator">Créateur : <?php echo htmlspecialchars($row['creator']); ?></p>
                    <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif">
                    <input type="file" name="movie_file" accept=".mp4">
                    <button type="submit" name="edit_movie">Modifier</button>
                    <button type="submit" name="delete_movie" class="delete-button">Supprimer</button>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucun film trouvé.</p>
        <?php endif; ?>
    </div>
</body>
</html>
