DROP DATABASE IF EXISTS STREAMX;

CREATE DATABASE STREAMX;

USE STREAMX;

CREATE TABLE Telefone (
    Telefone_PK INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Telefone VARCHAR(20)
);

CREATE TABLE Cliente (
    ID_Cliente INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100),
    Email VARCHAR(50) UNIQUE,
    Senha VARCHAR(30),
    AnoNasc YEAR,
    CEP VARCHAR(9),
    CPF VARCHAR(15) UNIQUE,
    Numero VARCHAR(15),
    Logradouro VARCHAR(100),
    fk_Telefone_Telefone_PK INT
);

CREATE TABLE Aluguel_Aluga (
    ID_Aluguel INT AUTO_INCREMENT PRIMARY KEY,
    Valor DECIMAL(10,2),
    Data_Fim DATE,
    Data_Inicio DATE,
    fk_Filme_ID_Filme INT,
    fk_Cliente_ID_Cliente INT
);

CREATE TABLE Filme (
    ID_Filme INT AUTO_INCREMENT PRIMARY KEY,
    fk_Classificacao_indicativa_ID_Classificacao INT,
    Titulo VARCHAR(100),
    Ano YEAR,
    Ativo BOOLEAN
);

CREATE TABLE Genero (
    ID_Genero INT AUTO_INCREMENT PRIMARY KEY,
    Descricao VARCHAR(100),
    Nome VARCHAR(45)
);

CREATE TABLE Admin (
    ID_Admin INT AUTO_INCREMENT PRIMARY KEY,
    Nome VARCHAR(100),
    Email VARCHAR(50),
    Senha VARCHAR(8)
);

CREATE TABLE Classificacao_indicativa (
    ID_Classificacao INT AUTO_INCREMENT PRIMARY KEY,
    Descricao VARCHAR(20)
);

CREATE TABLE Pertence (
    fk_Filme_ID_Filme INT NULL,
    fk_Genero_ID_Genero INT NULL,
    Relevancia INT,
    ID_Pertence INT AUTO_INCREMENT PRIMARY KEY
);

CREATE TABLE Edita (
    ID_Edicao INT AUTO_INCREMENT PRIMARY KEY,
    fk_Admin_ID_Admin INT,
    fk_Filme_ID_Filme INT,
    Dt_Adicao DATETIME NOT NULL,
    Nm_Adicao VARCHAR(50) NOT NULL,
    Dt_Alteracao DATETIME,
    Nm_Alteracao VARCHAR(50)
);

-- Adicionando as restrições de chave estrangeira com as ações de ON DELETE:

ALTER TABLE Cliente 
    ADD CONSTRAINT FK_Cliente_2
        FOREIGN KEY (fk_Telefone_Telefone_PK)
        REFERENCES Telefone (Telefone_PK)
        ON DELETE NO ACTION;

ALTER TABLE Aluguel_Aluga 
    ADD CONSTRAINT FK_Aluguel_Aluga_2
        FOREIGN KEY (fk_Filme_ID_Filme)
        REFERENCES Filme (ID_Filme);

ALTER TABLE Aluguel_Aluga 
    ADD CONSTRAINT FK_Aluguel_Aluga_3
        FOREIGN KEY (fk_Cliente_ID_Cliente)
        REFERENCES Cliente (ID_Cliente);

ALTER TABLE Filme 
    ADD CONSTRAINT FK_Filme_2
        FOREIGN KEY (fk_Classificacao_indicativa_ID_Classificacao)
        REFERENCES Classificacao_indicativa (ID_Classificacao)
        ON DELETE RESTRICT;

ALTER TABLE Pertence 
    ADD CONSTRAINT FK_Pertence_1
        FOREIGN KEY (fk_Filme_ID_Filme)
        REFERENCES Filme (ID_Filme)
        ON DELETE SET NULL;

ALTER TABLE Pertence 
    ADD CONSTRAINT FK_Pertence_2
        FOREIGN KEY (fk_Genero_ID_Genero)
        REFERENCES Genero (ID_Genero)
        ON DELETE RESTRICT;

ALTER TABLE Edita 
    ADD CONSTRAINT FK_Edita_2
        FOREIGN KEY (fk_Admin_ID_Admin)
        REFERENCES Admin (ID_Admin)
        ON DELETE RESTRICT;

ALTER TABLE Edita 
    ADD CONSTRAINT FK_Edita_3
        FOREIGN KEY (fk_Filme_ID_Filme)
        REFERENCES Filme (ID_Filme)
        ON DELETE RESTRICT;
        
ALTER TABLE Filme ADD COLUMN Imagem VARCHAR(255) DEFAULT NULL;

INSERT INTO Genero (ID_Genero, Descricao, Nome)
VALUES
    (1, 'Ação e Aventura', 'Ação'),
    (2, 'Animação', 'Infantil'),
    (3, 'Suspense', 'Drama'),
    (4, 'Terror e Suspense', 'Terror'),
    (5, 'Comédia', 'Comédia'),
    (6, 'Documentário', 'Documentário'),
    (7, 'Romance', 'Romance'),
    (8, 'Ficção Científica', 'Sci-Fi'),
    (9, 'Musical', 'Musical'),
    (10, 'Fantasia', 'Fantasia'),
    (11, 'Histórico', 'História'),
    (12, 'Guerra', 'Guerra'),
    (13, 'Esporte', 'Esporte'),
    (14, 'Família', 'Família'),
    (15, 'Mistério', 'Mistério');

INSERT INTO Classificacao_indicativa (ID_Classificacao, Descricao)
VALUES
    (1, '18'),
    (2, 'Livre'),
    (3, '16'),
    (4, '14'),
    (5, '12'),
    (6, '10'),
    (7, '13+'),
    (8, '16'),
    (9, '16'),
    (10, '18'),
    (11, '13'),
    (12, 'R'),
    (13, '12'),
    (14, '12'),
    (15, '16');