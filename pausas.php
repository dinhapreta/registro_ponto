<?php
//Isso instrui o navegador a não armazenar em cache a página
header("Cache-Control: no-cache, must-revalidate");


session_start();

// Verifica se o usuário está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Se não estiver autenticado, redireciona para a página de login
    header('Location: logout.php');
    exit;
}

// Verifica se a sessão de redirecionamento está configurada
if(isset($_SESSION['redirect_to_login']) && $_SESSION['redirect_to_login'] === true) {
    // Limpa a sessão de redirecionamento
    unset($_SESSION['redirect_to_login']);
    // Redireciona para a página de login
    header('Location: index.php');
    exit;
}

// Configurações do banco de dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'registro_ponto';

// Conexão com o MySQL
 $conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Verifica se o formulário foi submetido
if(isset($_POST['tipo_pausa'])) {
    // Obtém o tipo de pausa do formulário
    $tipo_pausa = $_POST['tipo_pausa'];
    $id_usuario = $_SESSION['id_usuario'];
    
    // Verifica se o usuário já registrou uma pausa do tipo no dia atual
    $sql = "SELECT COUNT(*) AS total FROM pausas WHERE tipo = ? AND id_usuario = ? AND DATE(data_hora) = CURDATE()";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $tipo_pausa, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row['total'] > 0) {
            echo "Você já registrou essa pausa hoje.";
        } else {
            // Insere a pausa na tabela
            $sql_insert = "INSERT INTO pausas (tipo, id_usuario) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            if ($stmt_insert) {
                $stmt_insert->bind_param("si", $tipo_pausa, $id_usuario);
                if ($stmt_insert->execute()) {
                    // Pausa registrada com sucesso
                    //echo "Pausa registrada com sucesso!";
                } else {
                    echo "Erro ao registrar a pausa: " . $stmt_insert->error;
                }
            } else {
                echo "Erro ao preparar a consulta: " . $conn->error;
            }
        }
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
}



// Função para fechar a conexão e redirecionar para a página de login
function logout() {
    // Fecha a conexão
    global $conn;
    $conn->close();

    // Redireciona para a página de login
    header("Location: index.php");
    exit; // Garante que o script não continue executando após o redirecionamento
}

// Verifica se o botão "Sair" foi clicado
if(isset($_POST['sair'])) {
    // Exibe um prompt de confirmação
    echo '<script>
            var confirmar = confirm("Deseja realmente sair?");
            if (confirmar) {
                location.href = "logout.php";
            }
          </script>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Pausas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />
  </head>
  <style>
    * {
      font-family: "Popins";
    }
    body {
      background-image: linear-gradient(to right, #ffffff, #00000056);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      flex-direction:column;
}

h2 {
  text-align: center;
  margin-left: 20px;
}

button {
  padding: 10px;
  border-radius: 5px 5px 0px 0px;
  color: black;
  font-size: 18px;
  font-weight: 400;
  transition: 0.3ms;
  cursor: pointer;
}
button:hover {
  transform: scale(1.05);
}
form {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  flex-direction: column;
}
.entrada button {
  background-color: #83e509;
}
.cafe button {
  background-color: #ad6961;
}
.almoco button {
  background-color: #ea515192;
}
.reuniao button {
  background-color: #676767b3;
}

.banheiro button {
  background-color: #b2b3da;
}
.saida button {
  background-color: #e84227;
}
.sair-usuario button {
  background-color: black;
  color: white;
}
a {
  color: #007bff;
text-decoration: none;
  color: black;
  font-weight: bold;
  font-size: 18px;
}
a:hover {
text-decoration: underline;
}
.historico{
position: absolute;
top: 20px;
right: 50px;
color:#2b13ef;

  

}
.mensagem {
   margin-top: 10px;
        color: #007bff;
        font-size: 14px;
        font-weight: bold;
        
    }
</style>
<body>
<section>
  <h2>Registrar Pausas</h2>
  <form method="post">
    <!-- entrada -->

    <div class="entrada">
      <img src="images/entrada.png" alt="entrada" />
      <button type="submit" name="tipo_pausa" value="Entrada">
        Entrada
      </button>
    </div>

    <!-- cafe -->

    <div class="cafe">
      <img src="images/cafe.png" alt="cafe" />
      <button type="submit" name="tipo_pausa" value="Pausa Café">
        Pausa Café
      </button>

      <button type="submit" name="tipo_pausa" value="Retorno Café">
        Retorno Café
      </button>
    </div>

    <!-- Almoço -->

    <div class="almoco">
      <img src="images/almoço.png" alt="almoço" />

      <button type="submit" name="tipo_pausa" value="Pausa Almoço">
        Pausa Almoço
      </button>
      <button type="submit" name="tipo_pausa" value="Retorno Almoço">
        Retorno Almoço
      </button>
    </div>

    <!-- reuniao -->
    <div class="reuniao">
      <img src="images/reuniao.png" alt="reunião" />
      <button type="submit" name="tipo_pausa" value="Pausa Reunião">
        Pausa Reunião
      </button>
      <button type="submit" name="tipo_pausa" value="Retorno Reunião">
        Retorno Reunião
      </button>
    </div>

    <!-- Banheiro -->

    <div class="Banheiro">
      <img src="images/banheiro.png" alt="banheiro" />
      <button type="submit" name="tipo_pausa" value="Pausa Banheiro">
        Pausa Banheiro
      </button>
      <button type="submit" name="tipo_pausa" value="Retorno Banheiro">
        Retorno Banheiro
      </button>
    </div>

    <!-- saida -->

    <div class="saida">
      <img src="images/saida.png" alt="saida" />
      <button type="submit" name="tipo_pausa" value="Saída">Saída</button>
    </div>
    <br />
    <!-- Adiciona um link para o histórico -->
    <a class="historico" href="historico.php">Ver Histórico ➡️</a> <br />
    <!-- Adiciona um botão "Sair" -->
    <div class="sair-usuario">
      <button type="submit" name="sair">Sair</button>
    </div>
  </form>
</section>

<!-- Div para exibir a pausa registrada -->
<div class="mensagem">
  <?php
    // Exibe a mensagem de pausa registrada, se houver
    if(isset($_POST['tipo_pausa'])) {
        echo "Pausa registrada: " . $_POST['tipo_pausa'];
    }
    ?>
</div>

<script>
  // Detecta quando o usuário tenta navegar para trás
  window.addEventListener("popstate", function (event) {
    // Redireciona para a página de login
    window.location.href = "index.php";
  });
</script>
</body>
</html>