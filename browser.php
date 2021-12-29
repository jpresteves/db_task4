<?php
 // duas janelas com as encomendas e as linhas da encomenda seleccionada
// btn de refresh
// possibilidade de ter um timer de refresh
// 'e possivel ter um timer de 1ms ?

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== 1) {
    // require_once './connect.php';
    header('Location: ./connect.php');
}
$encid = -1;
$refreshrate=5; // seconds
$servername = $_SESSION['servername'];
$database = $_SESSION['database'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];

if (isset($_GET["encid"]) && !Empty($_GET["encid"])) {
    $encid = $_GET["encid"];
}

if (isset($_GET["refreshrate"]) && !Empty($_GET["refreshrate"])) {
    $refreshrate = $_GET["refreshrate"];
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "Select * from Encomenda order by EncID";

$result = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($encid < 0) {
    $encid = $rows[0]["EncID"];
}

$sqllin="select * from EncLinha where EncId=".$encid;
$resultlin = mysqli_query($conn, $sqllin);
$rowslin = mysqli_fetch_all($resultlin, MYSQLI_ASSOC);

echo "<html>";
    echo "<head>";
      echo "<meta http-equiv=\"refresh\" content=\"".$refreshrate."\">";
    echo "</head>";
    echo "<body>";
        echo "<table width='500px'>";
        echo "<thead><td>EncID</td><td>ClienteID</td><td>Nome</td><td>Morada</td></thead>";
        foreach($rows as $row) {
            echo "<tr>";
            echo "<td><a href=\"./browser.php?refreshrate=".$refreshrate."&encid=".$row["EncID"]."\">".$row["EncID"]."</a>  </td>";
            echo "<td>".$row["ClienteID"]."</td>";
            echo "<td>".$row["Nome"]."</td>";
            echo "<td>".$row["Morada"]."</td>";
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
            echo "<td>".$lin["Qtd"]."</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<hr>";
        echo "<form action=\"./browser.php\" method=\"get\" name=\"setrefreshrate\">";
            echo "<label for='refreshrate'>Set refresh rate (seconds):</label>";
            echo "<input type='text' name='refreshrate' value='".$refreshrate."'></input>";
            echo "<input type='submit' name='submit' value='Set rate'></input>";
        echo "</form>";
        echo "<button onClick=\"window.location.reload();\">Refresh Page</button>";
        echo "<hr><a href=\"main.php\">Back to main </a>";



    echo "</body>";
echo "</html>";





