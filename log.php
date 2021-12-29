<?php
// N linhas mais recentes da tabela LogOperations.
// Considerar s'o eventos do tipo I, U, D.
// Deve ser incluido um timer para refresh.

// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== 1) {
    // require_once './connect.php';
    header('Location: ./connect.php');
}
$encid = -1;
$refreshrate=5; // seconds
$pagesize=20;
$servername = $_SESSION['servername'];
$database = $_SESSION['database'];
$username = $_SESSION['username'];
$password = $_SESSION['password'];


if (isset($_GET["refreshrate"]) && !Empty($_GET["refreshrate"])) {
    $refreshrate = $_GET["refreshrate"];
}

if (isset($_GET["pagesize"]) && !Empty($_GET["pagesize"])) {
    $pagesize = $_GET["pagesize"];
}

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = " SELECT NumReg, EventType, Objecto, Valor, Referencia, UserID, TerminalD, TerminalName, DCriacao ";
$sql=$sql." from LogOperations where EventType in ('U', 'D', 'I')";
$sql=$sql." order by DCriacao DESC Limit ".$pagesize;

$result = mysqli_query($conn, $sql);
$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);


echo "<html>";
echo "<head>";
echo "<meta http-equiv=\"refresh\" content=\"".$refreshrate."\">";
echo "</head>";
echo "<body>";
echo "<table with='900px'>";
echo "<thead><td>NumReg</td><td>EventType</td><td>Objecto</td><td>Valor</td><td>Referencia</td><td>UserID</td><td>TerminalD</td><td>TerminalName</td><td>DCriacao</td></thead>";
    foreach($rows as $row) {
        echo "<tr>";
        echo "<td>".$row["NumReg"]."</td>";
        echo "<td>".$row["EventType"]."</td>";
        echo "<td>".$row["Objecto"]."</td>";
        echo "<td>".$row["Valor"]."</td>";
        echo "<td>".$row["Referencia"]."</td>";
        echo "<td>".$row["UserID"]."</td>";
        echo "<td>".$row["TerminalD"]."</td>";
        echo "<td>".$row["TerminalName"]."</td>";
        echo "<td>".$row["DCriacao"]."</td>";
        echo "</tr>";
    }
echo "</table>";

echo "<hr>";

echo "<form action=\"./log.php\" method=\"get\" name=\"setrefreshrate\">";
    echo "<label for='refreshrate'>Set refresh rate (seconds):</label>";
    echo "<input type='text' name='refreshrate' value='".$refreshrate."'></input>";
    echo "<label for='pagesize'>Show N records :</label>";
    echo "<input type='text' name='pagesize' value='".$pagesize."'></input>";
    echo "<input type='submit' name='submit' value='Set values'></input>";
echo "</form>";
echo "<button onClick=\"window.location.reload();\">Refresh Page</button>";
echo "<hr><a href=\"main.php\">Back to main </a>";



echo "</body>";
echo "</html>";
