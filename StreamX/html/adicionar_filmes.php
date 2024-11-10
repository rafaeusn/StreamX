<?php
// Configuração do banco de dados
$servername = "localhost:3306";
$username = "root";
$password = "PUC@1234";
$dbname = "StreamX";

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}
date_default_timezone_set('America/Sao_Paulo'); // Definindo o fuso horário para São Paulo
mysqli_query($conn, "SET time_zone = '+00:00'");

// Configura para trabalhar com caracteres acentuados do português
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, 'SET character_set_connection=utf8');
mysqli_query($conn, 'SET character_set_client=utf8');
mysqli_query($conn, 'SET character_set_results=utf8');

// Consulta para buscar filmes, classificação, data de adição e gênero (somente ativos)
$query = "SELECT f.ID_Filme, f.Titulo, f.Ano, c.Descricao AS Classificacao, 
                 IFNULL(e.Dt_Adicao, 'Data não registrada') AS Dt_Adicao,
                 g.Nome AS Genero, f.Imagem
          FROM Filme f
          JOIN Classificacao_indicativa c ON f.fk_Classificacao_indicativa_ID_Classificacao = c.ID_Classificacao
          LEFT JOIN Edita e ON f.ID_Filme = e.fk_Filme_ID_Filme
          LEFT JOIN Pertence p ON f.ID_Filme = p.fk_Filme_ID_Filme
          LEFT JOIN Genero g ON p.fk_Genero_ID_Genero = g.ID_Genero
          WHERE f.Ativo = 1";
$stmt = $conn->query($query);

// Consulta para buscar todos os gêneros e armazenar em um array
$queryGeneros = "SELECT ID_Genero, Nome FROM Genero";
$resultGeneros = $conn->query($queryGeneros);
$generosArray = [];
while ($genre = $resultGeneros->fetch_assoc()) {
    $generosArray[] = $genre;
}

// Consulta para buscar todas as classificações indicativas e armazenar em um array
$queryClassificacao = "SELECT ID_Classificacao, Descricao FROM Classificacao_indicativa";
$resultClassificacao = $conn->query($queryClassificacao);
$classificacaoArray = [];
while ($class = $resultClassificacao->fetch_assoc()) {
    $classificacaoArray[] = $class;
}


// Processamento do formulário para adicionar filme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['addMovie'])) {
    $movieName = $_POST['movieName'];
    $rentalPrice = $_POST['rentalPrice'];
    $premierYear = $_POST['premierYear'];
    $genre = $_POST['genre'];
    $ageClassification = $_POST['ageClassification'];

    // Processar a imagem
    $imagePath = '';
    if (isset($_FILES['movieImage']) && $_FILES['movieImage']['error'] == 0) {
        $imageTmpName = $_FILES['movieImage']['tmp_name'];
        $imageName = $_FILES['movieImage']['name'];
        $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);

        // Definir o diretório de upload
        $uploadDir = '../uploads/';
        $imagePath = $uploadDir . uniqid() . '.' . $imageExt;

        // Validar e mover a imagem
        if (in_array($imageExt, ['jpg', 'jpeg', 'png', 'gif'])) {
            move_uploaded_file($imageTmpName, $imagePath);
        } else {
            echo "<p>Formato de imagem inválido! Apenas JPG, JPEG, PNG e GIF são permitidos.</p>";
            exit;
        }
    }

    if (!empty($movieName) && !empty($rentalPrice) && !empty($premierYear) && !empty($genre) && !empty($ageClassification)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO Filme (Titulo, Ano, fk_Classificacao_indicativa_ID_Classificacao, Ativo, Imagem) VALUES (?, ?, ?, ?, ?)");
            $ativo = 1;
            $stmt->bind_param('siiis', $movieName, $premierYear, $ageClassification, $ativo, $imagePath);
            $stmt->execute();
            $filmId = $stmt->insert_id;
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO Edita (fk_Filme_ID_Filme, Dt_Adicao) VALUES (?, ?)");
            $currentDate = date('Y-m-d H:i:s');
            $stmt->bind_param('is', $filmId, $currentDate);
            $stmt->execute();
            $stmt->close();

            if (!empty($genre)) {
                $stmt = $conn->prepare("INSERT INTO Pertence (fk_Filme_ID_Filme, fk_Genero_ID_Genero) VALUES (?, ?)");
                $stmt->bind_param('ii', $filmId, $genre);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Erro ao adicionar o filme: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Preencha todos os campos obrigatórios!</p>";
    }
}

// Processamento do formulário para inativar filme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['inactivateMovie'])) {
    $movieIdToInactivate = $_POST['movieId'];

    if (!empty($movieIdToInactivate)) {
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("UPDATE Filme SET Ativo = 0 WHERE ID_Filme = ?");
            $stmt->bind_param('i', $movieIdToInactivate);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Erro ao inativar o filme: " . $e->getMessage() . "</p>";
        }
    }
}

// Processamento do formulário para excluir filme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteMovie'])) {
    $movieIdToDelete = $_POST['movieId'];

    if (!empty($movieIdToDelete)) {
        $conn->begin_transaction();

        try {
            // Remover a associação do filme na tabela Edita
            $stmt = $conn->prepare("DELETE FROM Edita WHERE fk_Filme_ID_Filme = ?");
            $stmt->bind_param('i', $movieIdToDelete);
            $stmt->execute();
            $stmt->close();

            // Excluir o filme da tabela Filme
            $stmt = $conn->prepare("DELETE FROM Filme WHERE ID_Filme = ?");
            $stmt->bind_param('i', $movieIdToDelete);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Erro ao excluir o filme: " . $e->getMessage() . "</p>";
        }
    }
}

// Processamento do formulário para atualizar filme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateMovie'])) {
    $movieIdUpdate = $_POST['movieId'];
    $movieName = $_POST['movieName'];
    $rentalPrice = $_POST['rentalPrice'];
    $premierYear = $_POST['premierYear'];
    $genre = $_POST['genre'];
    $ageClassification = $_POST['ageClassification'];

    // Processar a imagem
    $imagePath = '';
    if (isset($_FILES['movieImage']) && $_FILES['movieImage']['error'] == 0) {
        $imageTmpName = $_FILES['movieImage']['tmp_name'];
        $imageName = $_FILES['movieImage']['name'];
        $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);

        // Definir o diretório de upload
        $uploadDir = '../uploads/';
        $imagePath = $uploadDir . uniqid() . '.' . $imageExt;

        // Validar e mover a imagem
        if (in_array($imageExt, ['jpg', 'jpeg', 'png', 'gif'])) {
            move_uploaded_file($imageTmpName, $imagePath);
        } else {
            echo "<p>Formato de imagem inválido! Apenas JPG, JPEG, PNG e GIF são permitidos.</p>";
            exit;
        }
    }

    if (!empty($movieIdUpdate) && !empty($movieName) && !empty($rentalPrice) && !empty($premierYear) && !empty($genre) && !empty($ageClassification)) {
        $conn->begin_transaction();

        try {
            // Atualizar informações do filme
            $stmt = $conn->prepare("UPDATE Filme SET Titulo = ?, Ano = ?, fk_Classificacao_indicativa_ID_Classificacao = ?, Imagem = ? WHERE ID_Filme = ?");
            if ($stmt === false) {
                throw new Exception("Erro ao preparar a declaração: " . $conn->error);
            }

            $stmt->bind_param('siisi', $movieName, $premierYear, $ageClassification, $imagePath, $movieIdUpdate);
            $stmt->execute();
            $stmt->close();

            // Atualizar gênero do filme
            $stmt = $conn->prepare("UPDATE Pertence SET fk_Genero_ID_Genero = ? WHERE fk_Filme_ID_Filme = ?");
            $stmt->bind_param('ii', $genre, $movieIdUpdate);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Erro ao atualizar o filme: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Preencha todos os campos obrigatórios!</p>";
    }
}
?>




<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles.css/add.filmes.css">
    <script>
        function openPopup() {
            document.getElementById('addMoviePopup').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('addMoviePopup').style.display = 'none';
        }

        function openEditPopup(id, title, year, genreId, classificationId, imagePath) {
            document.getElementById('editMoviePopup').style.display = 'block';
            document.getElementById('movieIdUpdate').value = id;
            document.getElementById('editMovieName').value = title;
            document.getElementById('editPremierYear').value = year;
            document.getElementById('editGenre').value = genreId;
            document.getElementById('editAgeClassification').value = classificationId;

            if (imagePath && imagePath.trim() !== '') {
                document.getElementById('currentImage').src = imagePath;
                document.getElementById('currentImage').style.display = 'block';
            } else {
                document.getElementById('currentImage').style.display = 'none';
            }
        }

        function closeEditPopup() {
            document.getElementById('editMoviePopup').style.display = 'none';
        }
    </script>
</head>
<body>

    <div class="admin-container">
        <aside class="sidebar">
            <h2>StreamX</h2>
            <nav>
                <ul>
                    <li><a href="#">Dashboard</a></li>
                </ul>
            </nav>
        </aside>
        
        <section class="main-content">
            <header>
                <h1>Filmes</h1>
                <button class="add-movie-btn" onclick="openPopup()">Adicionar Filme</button>
            </header>

            <?php while ($row = $stmt->fetch_assoc()): ?>
                <?php
                $dateTime = new DateTime($row['Dt_Adicao'], new DateTimeZone('UTC'));
                $dateTime->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                $formattedDate = $dateTime->format('d/m/Y');
                ?>
                <div class="movie-item">
                    <h3><?php echo htmlspecialchars($row['Titulo']); ?></h3>
                    <p><strong>Ano de Lançamento:</strong> <?php echo htmlspecialchars($row['Ano']); ?></p>
                    <p><strong>Classificação Indicativa:</strong> <?php echo htmlspecialchars($row['Classificacao']); ?></p>
                    <p><strong>Data de Adição:</strong> <?php echo htmlspecialchars($formattedDate); ?></p>
                    <p><strong>Gênero:</strong> <?php echo htmlspecialchars($row['Genero']); ?></p>
                    <p><strong>Imagem:</strong></p>
                    <?php if (!empty($row['Imagem']) && file_exists($row['Imagem'])): ?>
                        <img src="<?php echo htmlspecialchars($row['Imagem']); ?>" alt="Imagem do Filme" width="100">
                    <?php else: ?>
                        <p>Imagem não disponível</p>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="movieId" value="<?php echo $row['ID_Filme']; ?>">
                        <button type="submit" name="inactivateMovie">Inativar</button>
                        <button type="button" onclick="openEditPopup(
                            <?php echo htmlspecialchars(json_encode($row['ID_Filme'])); ?>,
                            <?php echo htmlspecialchars(json_encode($row['Titulo'])); ?>,
                            <?php echo htmlspecialchars(json_encode($row['Ano'])); ?>,
                            <?php echo htmlspecialchars(json_encode($row['Genero'])); ?>,
                            <?php echo htmlspecialchars(json_encode($row['Classificacao'])); ?>,
                            '<?php echo htmlspecialchars($row['Imagem']); ?>'
                        )"name="alterMovie">Alterar</button>
                        <button type="submit" name="deleteMovie">Excluir</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </section>
    </div>
    <div id="addMoviePopup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2>Adicionar Filme</h2>
            <form method="POST" enctype="multipart/form-data">
                <label for="movieName">Nome do Filme:</label>
                <input type="text" id="movieName" name="movieName" required>

                <label for="rentalPrice">Preço de Aluguel:</label>
                <input type="number" id="rentalPrice" name="rentalPrice" required>

                <label for="premierYear">Ano de Lançamento:</label>
                <input type="number" name="premierYear" id="premierYear" required min="1900" max="2100"
                oninvalid="this.setCustomValidity('Por favor, insira um ano entre 1900 e 2100.')"
                oninput="this.setCustomValidity('')">

                <label for="genre">Gênero:</label>
<select name="genre" required>
    <option value="">Selecione</option>
    <?php foreach ($generosArray as $genre): ?>
        <option value="<?php echo $genre['ID_Genero']; ?>"><?php echo htmlspecialchars($genre['Nome']); ?></option>
    <?php endforeach; ?>
</select>

<label for="ageClassification">Classificação Indicativa:</label>
<select name="ageClassification" required>
    <option value="">Selecione</option>
    <?php foreach ($classificacaoArray as $class): ?>
        <option value="<?php echo $class['ID_Classificacao']; ?>"><?php echo htmlspecialchars($class['Descricao']); ?></option>
    <?php endforeach; ?>
</select>


                <label for="movieImage">Imagem do Filme:</label>
                <input type="file" name="movieImage" id="movieImage" accept="image/*">

                <button type="submit" name="addMovie">Adicionar Filme</button>
            </form>
        </div>
    </div>

    <!-- Popup Editar Filme -->
    <div id="editMoviePopup" class="popup" style="display: none;">
        <div class="popup-content">
            <span class="close" onclick="closeEditPopup()">&times;</span>
            <h2>Editar Filme</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" id="movieIdUpdate" name="movieId">
                
                <label for="editMovieName">Nome do Filme:</label>
                <input type="text" id="editMovieName" name="movieName" required>

                <label for="editRentalPrice">Preço de Aluguel:</label>
                <input type="number" id="editRentalPrice" name="rentalPrice" required>

                <label for="editPremierYear">Ano de Lançamento:</label>
                <input type="number" name="premierYear" id="editPremierYear" required min="1900" max="2100">

                <label for="editGenre">Gênero:</label>
<select name="genre" id="editGenre" required>
    <option value="">Selecione</option>
    <?php foreach ($generosArray as $genre): ?>
        <option value="<?php echo $genre['ID_Genero']; ?>"><?php echo htmlspecialchars($genre['Nome']); ?></option>
    <?php endforeach; ?>
</select>

<label for="editAgeClassification">Classificação Indicativa:</label>
<select name="ageClassification" id="editAgeClassification" required>
    <option value="">Selecione</option>
    <?php foreach ($classificacaoArray as $class): ?>
        <option value="<?php echo $class['ID_Classificacao']; ?>"><?php echo htmlspecialchars($class['Descricao']); ?></option>
    <?php endforeach; ?>
</select>


                <label for="editMovieImage">Imagem do Filme:</label>
                <input type="file" name="movieImage" id="editMovieImage" accept="image/*">
                <img id="currentImage" src="" alt="Imagem atual do filme" width="100" style="display: none;">

                <button type="submit" name="updateMovie">Salvar Alterações</button>
            </form>
        </div>
    </div>

</body>
</html>
