<?php
// Configurações de conexão com o banco de dados
$servername = "localhost";
$username = "root";
$password = "PUC@1234";
$dbname = "STREAMX";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$idGenero = isset($_GET['id_genero']) ? intval($_GET['id_genero']) : 0;

$sql = "SELECT Filme.Titulo, Filme.Ano, Filme.Imagem FROM Filme
        INNER JOIN Pertence ON Filme.ID_Filme = Pertence.fk_Filme_ID_Filme
        WHERE Pertence.fk_Genero_ID_Genero = ? AND Filme.Ativo = 1"; 

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idGenero);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filmes de Ação - StreamX</title>
    <link rel="stylesheet" href="../styles.css/generos.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../images/logoStreamX.png" alt="Logo da StreamX">
            </div>
            <nav>
                <ul>
                <li><a href="menu.php">Início</a></li>
                <li><a href="menu.php">Filmes</a></li>
                <li class="nav-item dropdown">
                    <a class="dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Gêneros
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <?php
$sqlGeneros = "SELECT ID_Genero, Nome FROM Genero"; // Seleciona os gêneros

$resultGeneros = $conn->query($sqlGeneros);
?>
        <?php
if ($resultGeneros->num_rows > 0) {
    while ($row = $resultGeneros->fetch_assoc()) {
        // Adicionando o link para a página do gênero
        echo '<a class="dropdown-item" href="filmes_genero.php?id_genero=' . $row["ID_Genero"] . '">' . htmlspecialchars($row["Nome"]) . '</a>';
    }
} else {
    echo "<a class='dropdown-item' href='#'>Nenhum gênero encontrado</a>";
}
?>

    </div>
</li>
                </ul>
            </nav>
        </div>
    </header>
    <section class="titulo-genero">
        <h1>Filmes no gênero: <?php
            // Query para obter o nome do gênero
            $sqlGenero = "SELECT Nome FROM Genero WHERE ID_Genero = ?";
            $stmtGenero = $conn->prepare($sqlGenero);
            $stmtGenero->bind_param("i", $idGenero);
            $stmtGenero->execute();
            $resultGenero = $stmtGenero->get_result();
            if ($resultGenero->num_rows > 0) {
                $rowGenero = $resultGenero->fetch_assoc();
                echo htmlspecialchars($rowGenero["Nome"]);
            }
            ?></h1>
    </section>
    <section class="lista-filmes">
    <div class="descricao-filme">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="filme-item">';
                echo '<img src="../images/' . htmlspecialchars($row["Imagem"]) . '" alt="' . htmlspecialchars($row["Titulo"]) . '">';
                echo '<p>' . htmlspecialchars($row["Titulo"]) . ' (' . htmlspecialchars($row["Ano"]) . ')</p>';
                echo '</div>';
            }
        } else {
            echo "<p>Nenhum filme encontrado neste gênero.</p>";
        }9
        ?>
    </div>
</section>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
