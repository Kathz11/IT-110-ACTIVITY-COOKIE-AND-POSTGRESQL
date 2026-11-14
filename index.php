<?php
session_start();

include "db.php";

$error = "";  


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $author = trim($_POST["author"]);
    $body   = trim($_POST["body"]);

    if ($author == "" || $body == "") {
        $error = "Please type your name and a message.";
    } else {
        $sql  = "INSERT INTO messages (author, body) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$author, $body]);

        if (isset($_POST["remember"])) {
            setcookie("author_name", $author, time() + 60 * 60 * 24 * 30);
        } else {
            setcookie("author_name", "", time() - 3600);
        }

        header("Location: index.php");
        exit;
    }
}

if (!isset($_SESSION["visits"])) {
    $_SESSION["visits"] = 0;      
}
$_SESSION["visits"] = $_SESSION["visits"] + 1;   


$savedName = "";
if (isset($_COOKIE["author_name"])) {
    $savedName = $_COOKIE["author_name"];
}

$search = "";
if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search != "") {
    $sql  = "SELECT * FROM messages WHERE body ILIKE ? ORDER BY created_at DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%" . $search . "%"]);
} else {
    $sql  = "SELECT * FROM messages ORDER BY created_at DESC, id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$messages = $stmt->fetchAll();   
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Class Wall</title>
    <style>
        body  { font-family: Arial, sans-serif; max-width: 650px; margin: 30px auto; background: #f2f2f2; }
        .box  { background: white; padding: 15px; margin-bottom: 15px; border-radius: 6px; }
        input[type=text], textarea { width: 100%; padding: 6px; margin: 4px 0 10px 0; box-sizing: border-box; }
        .small { color: gray; font-size: 13px; }
        .error { color: red; }
    </style>
</head>
<body>

    <h1>Class Message Board</h1>
    <p>You've visited <?php echo htmlspecialchars($_SESSION["visits"]); ?> time(s).</p>


    <div class="box">
        <h3>Post a message</h3>

        <?php if ($error != "") { ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST" action="index.php">
            Name:
            <input type="text" name="author" value="<?php echo htmlspecialchars($savedName); ?>">

            Message:
            <textarea name="body" rows="3"></textarea>

            <input type="checkbox" name="remember" <?php if ($savedName != "") { echo "checked"; } ?>>
            Remember my name
            <br><br>
            <button type="submit">Post</button>
        </form>
    </div>


    <div class="box">
        <form method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search messages..."
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
            <a href="index.php">Show all</a>
        </form>
    </div>


    <h3>Messages</h3>

    <?php if (count($messages) == 0) { ?>
        <p>No messages found.</p>
    <?php } ?>

    <?php foreach ($messages as $row) { ?>
        <div class="box">
            <b><?php echo htmlspecialchars($row["author"]); ?></b>
            <span class="small"> - <?php echo htmlspecialchars($row["created_at"]); ?></span>
            <p><?php echo htmlspecialchars($row["body"]); ?></p>
        </div>
    <?php } ?>

</body>
</html>