<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] === 'admin') {
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
$current_user = $_SESSION['username'];

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
        $sql = "INSERT INTO Movies (title, release_date, duration, rating, image_url, desc_film, creator) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidsis", $title, $release_date, $duration, $rating, $imagePath, $desc_film, $current_user);
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

    $sql = "SELECT image_url FROM Movies WHERE id = ? AND creator = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $movie_id, $current_user);
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
        $sql = "UPDATE Movies SET title = ?, release_date = ?, duration = ?, rating = ?, image_url = ?, desc_film = ? WHERE id = ? AND creator = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssidsisi", $title, $release_date, $duration, $rating, $imagePath, $desc_film, $movie_id, $current_user);
        if ($stmt->execute()) {
            $success = "Film modifié avec succès !";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movie'])) {
    $movie_id = $_POST['movie_id'];

    $sql = "SELECT image_url FROM Movies WHERE id = ? AND creator = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $movie_id, $current_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();

    if ($movie) {
        $imagePath = $movie['image_url'];
        if ($imagePath !== "asset/img/default_image.jpg" && file_exists($imagePath)) {
            unlink($imagePath);
        }

        $sql = "DELETE FROM Movies WHERE id = ? AND creator = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $movie_id, $current_user);
        if ($stmt->execute()) {
            $success = "Film supprimé avec succès !";
        } else {
            $error = "Une erreur est survenue. Veuillez réessayer.";
        }
    } else {
        $error = "Vous n'avez pas la permission de supprimer ce film.";
    }
}

$sql = "SELECT * FROM Movies WHERE creator = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes films</title>
    <link rel="stylesheet" href="style-user-movies.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="index.php">Accueil</a></li>
        </ul>
    </nav>
    <div class="movie-container">
        <h1>Mes films</h1>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
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
            <h2>Mes films ajoutés</h2>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 1rem;">
                    <input type="hidden" name="movie_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>
                    <input type="date" name="release_date" value="<?php echo $row['release_date']; ?>" required>
                    <input type="number" name="duration" value="<?php echo $row['duration']; ?>" required>
                    <input type="number" step="0.1" name="rating" value="<?php echo $row['rating']; ?>" required>
                    <textarea name="desc_film" required><?php echo htmlspecialchars($row['desc_film']); ?></textarea>
                    <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp,.gif">
                    <button type="submit" name="edit_movie">Modifier</button>
                    <button type="submit" name="delete_movie">Supprimer</button>
                </form>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Vous n'avez ajouté aucun film.</p>
        <?php endif; ?>
    </div>
</body>
</html>
