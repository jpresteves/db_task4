<?php
// user escolhe uma encomenda. a app mostra dos dados: cabecalho e linhas e permite a alteracao.
// lista as encomendas.
// 1) user escolhe e grava linha na tabela de log
// 2) insert into logoperations eventtype, objecto, valor, referencia) values (O, user_encid, user_datetime, user_reference) onde
//  user_encid 'e o ID da encomenda escolhida pelo utilizador
// user_datetime  'e a hora corrente,
// user_reference 'e uma referencia unica
// 3) Inicia uma transaccao
// 4) Le os dados da encomenda e mostra ao utilizador
// 5) grava as alteracoes
// 6) Termina a transaccao
// 7) Escreve uma mensagem no log insert into logoperations eventtype, objecto, valor, referencia) values (O, user_encid, user_datetime, user_reference)


// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== 1) {
    // require_once './connect.php';
    header('Location: ./connect.php');
}
$encid = -1;
$servername = $_SESSION['servername'];
$database = $_SESSION['database'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];
$identifier=time();

if (isset($_GET["encid"]) && !Empty($_GET["encid"])) {
    $encid = $_GET["encid"];
}


// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$sql = "Select * from Encomenda ";

if (($encid>=0) && !isset($_GET["submit"]))  {
    // .2
    $identifier= "G1-".time();
    $sqllog="Insert into LogOperations (EventType,Objecto, Valor,Referencia ) values ( ";
    $sqllog = $sqllog."'O', ";
    $sqllog = $sqllog.$encid.", ";
    $sqllog = $sqllog."NOW(), ";
    $sqllog = $sqllog."'".$identifier."'";
    $sqllog = $sqllog.");";
    echo $sqllog;
    $resultlog = mysqli_query($conn, $sqllog);

    // .3
    $sqllog="START TRANSACTION;";
    $resultlog = mysqli_query($conn, $sqllog);

    // .4
    $sql = $sql." where encID=".$encid;
    $sqllin="select * from EncLinha where encid=".$encid;
    $resultlin = mysqli_query($conn, $sqllin);
    $rowslin = mysqli_fetch_all($resultlin, MYSQLI_ASSOC);
}


if (($encid>=0) && isset($_GET["submit"]))  {
    // .5
    $morada=$_GET["morada"];
    $sqlupdate = "Update Encomenda set Morada ='".$morada."' where EncID=".$encid;
    $resultupdate = mysqli_query($conn, $sqlupdate);

    foreach ($_GET as $key => $value) {
        if (substr($key,0,3)=='qtd') {
            $produto=substr($key,3);
            $qtd = $value;
            $sqlupdate = "Update EncLinha set Qtd ='".$qtd."' where EncId=".$encid." and ProdutoID=".$produto;
            $resultupdate = mysqli_query($conn, $sqlupdate);
        }
    }

    // .6
    $sqllog="COMMIT;";
    $resultlog = mysqli_query($conn, $sqllog);


    // .7
    $sqllog="Insert into LogOperations (EventType,Objecto, Valor,Referencia ) values ( ";
    $sqllog = $sqllog."'O', ";
    $sqllog = $sqllog.$encid.", ";
    $sqllog = $sqllog."NOW(), ";
    $sqllog = $sqllog."'".$_GET["identifier"]."'"; // tenho de ir buscar a referencia do inicial.
    $sqllog = $sqllog.");";
    $resultlog = mysqli_query($conn, $sqllog);

}


// Se nao foi pedido nenhuma encomenda, mostra a lista
$result = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
// echo $rows;
if (($encid >= 0) && !isset($_GET["submit"])){
    echo "<form action=\"./appedit.php\" method=\"get\" name=\"encedit\">";
        echo "<table width='500px'>";
            echo "<thead><td>EncID</td><td>ClienteID</td><td>Nome</td><td>Morada</td></thead>";
            foreach($rows as $row) {
                echo "<tr>";
                echo "<input type='hidden' name='identifier', value='".$identifier."'></input>";
                echo "<input type='hidden' name='encid', value='".$row["EncID"]."'></input>";
                echo "<td>".$row["EncID"]."</td>";
                echo "<td>".$row["ClienteID"]."</td>";
                echo "<td>".$row["Nome"]."</td>";
                echo "<td><input type='text' name='morada' value='" .$row["Morada"]."'></input</td>";
                echo "</tr>";
            }
        echo "</table>";
        echo "<hr>";
        echo "<table width='500px'>";
            echo "<thead><td>EncID</td><td>ProdutoID</td><td>Designacao</td><td>Preco</td><td>Qtd</td></thead>";
            foreach($rowslin as $lin) {
                echo "<tr>";
                echo "<td>".$lin["EncId"]."</td>";
                echo "<td>".$lin["ProdutoID"]."</td>";
                echo "<td>".$lin["Designacao"]."</td>";
                echo "<td>".$lin["Preco"]."</td>";
                echo "<td><input type='text' name='qtd".$lin["ProdutoID"]."' value='" .$lin["Qtd"]."'></input</td>";
                echo "</tr>";
            }
            echo "<tr><td/><td/><td/><td/><td><input type='submit' name='submit' value='Save changes'></input></td></tr>";
        echo "</table>";
    echo "</form>";
    echo "<hr><a href=\"appedit.php\">Back to Enc listing</a>";
} else {
    echo "<table width='500px'>";
    echo "<thead><td>EncID</td><td>ClienteID</td><td>Nome</td><td>Morada</td></thead>";
    foreach($rows as $row) {
        echo "<tr>";
        echo "<td><a href=\"./appedit.php?encid=".$row["EncID"]."\">".$row["EncID"]."</a>  </td>";
        echo "<td>".$row["ClienteID"]."</td>";
        echo "<td>".$row["Nome"]."</td>";
        echo "<td>".$row["Morada"]."</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<hr><a href=\"main.php\">Back to APP Menu </a>";
}



?>




