<?php
// Configurações de conexão com o banco de dados
$servername = "localhost"; // Substitua pelo seu endereço de servidor
$username = "root";        // Substitua pelo seu nome de usuário
$password = "PUC@1234";    // Substitua pela sua senha
$dbname = "STREAMX";       // Nome do banco de dados

// Criando a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificando a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Query para obter filmes do banco de dados
$sql = "SELECT Filme.Titulo, Filme.Ano, Filme.Imagem FROM Filme WHERE Filme.Ativo = 1"; // Apenas filmes ativos

$result = $conn->query($sql);
?>

<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - StreamX</title>
    <link rel="stylesheet" href="../styles.css/menu.css">
</head>
<body>
    <!-- Navbar -->
    <header>
        <div class="navbar">
            <div class="logo">
                <img src="../images/logoStreamX.png" alt="Logo da StreamX">
            </div>
            <nav>
                <ul>
                    <li><a href="menu.html">Início</a></li>
                    <li><a href="#">Filmes</a></li>
                    <li><a href="#">Séries</a></li>
                    <li><a href="#">Categorias</a></li>
                </ul>
            </nav>
            <div class="search-bar">
                <input type="text" placeholder="O que deseja assistir?">
                <button>Pesquisar</button>
            </div>
            <div class="user-profile">
                <a href="login.php">Entrar</a>
            </div>
        </div>
    </header>

    <!-- Filme em Destaque -->
    <section class="filme-destaque">
        <img src="../images/jurassic3.jpg" alt="Jurassic Park 3">
        <div class="descricao-destaque">
            <h1>Jurassic Park 3</h1>
            <p>O paleontólogo Alan Grant é contratado por um empresário e sua esposa para fazer uma excursão aérea pela Ilha Sorna,
                onde dinossauros clonados vivem livremente. No entanto, um acidente deixa o grupo de sete pessoas preso na ilha,
                cercado por dinossauros carnívoros. Agora, eles precisam lutar para sobreviver e encontrar uma maneira de escapar.</p>
            <button class="trailer-btn">Trailer</button>
        </div>
    </section>

    <!-- Filmes Comédia (Carrossel) -->
    <section class="filmes-comedia">
        <div class="carousel-container" id="comedia-carousel">
            <button class="prev" onclick="moveCarousel(-1, 'comedia-carousel')">&#10094;</button>
            <div class="carousel">
                <?php
                if ($result->num_rows > 0) {
                    // Loop para exibir os filmes
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="filme-item">';
                        echo '<img src="../images/' . htmlspecialchars($row["Imagem"]) . '" alt="' . htmlspecialchars($row["Titulo"]) . '">';
                        echo '<p>' . htmlspecialchars($row["Titulo"]) . ' (' . htmlspecialchars($row["Ano"]) . ')</p>';
                        echo '</div>';
                    }
                } else {
                    echo "<p>Nenhum filme encontrado.</p>";
                }
                // Fechar a conexão
                $conn->close();
                ?>
            </div>
            <button class="next" onclick="moveCarousel(1, 'comedia-carousel')">&#10095;</button>
        </div>
    </section>

    <!-- Filmes Ação -->
    <section class="filmes-acao">
        <h2>Ação</h2>
        <div class="carousel-container" id="acao-carousel">
            <button class="prev" onclick="moveCarousel(-1, 'acao-carousel')">&#10094;</button>
            <div class="carousel">
                <div class="filme-item">
                    <img src="../images/matrix.jpg" alt="Matrix">
                    <p>Matrix</p>
                    <span>Avaliação: 8.7/10</span>
                </div>
                <div class="filme-item">
                    <img src="../images/vingadores-ultimato.webp" alt="Vingadores: Ultimato">
                    <p>Vingadores: Ultimato</p>
                    <span>Avaliação: 9.8/10</span>
                </div>
                <div class="filme-item">
                    <img src="../images/missao-impossivel.webp" alt="Missão Impossível">
                    <p>Missão Impossível</p>
                    <span>Avaliação: 7.8/10</span>
                </div>
                <div class="filme-item">
                    <img src="../images/atraves-do-aranhaverso.jpg" alt="Homem-Aranha: Através do Aranhaverso">
                    <p>Homem-Aranha: Através do Aranhaverso</p>
                    <span>Avaliação: 9.2/10</span>
                </div>
            </div>
            <button class="next" onclick="moveCarousel(1, 'acao-carousel')">&#10095;</button>
        </div>
    </section>

    <script>
        function moveCarousel(direction, carouselId) {
            const carouselContainer = document.querySelector(`#${carouselId} .carousel`);
            const filmeItems = Array.from(carouselContainer.children);
            const itemWidth = filmeItems[0].offsetWidth + 20;

            let currentIndex = parseInt(carouselContainer.getAttribute('data-current-index') || 1);
            currentIndex += direction;

            if (currentIndex >= filmeItems.length) {
                currentIndex = 0;
            } else if (currentIndex < 0) {
                currentIndex = filmeItems.length - 1;
            }

            carouselContainer.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
            carouselContainer.setAttribute('data-current-index', currentIndex);
        }
    </script>
</body>
</html>
