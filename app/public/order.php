<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHOTO.KODAL | Digitale Fotos und Abzüge bestellen</title>
    <link href="./assets/styles.css" type="text/css" rel="stylesheet" />
    <script>
        let highlighted;
        function validateForm(event) {
            console.log(event);
            let email = document.forms["order"]["email"].value;
            if (/^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                return true;
            } else {
                alert("Bitte gültige E-Mailadresse angeben!");
                return false;
            }
        }
        function highlightImage(id) {
            if (highlighted) {
                highlighted.classList.remove('highlight');
            }
            history.pushState(null, '', '#anchor'+id);
            highlighted = document.getElementById('image'+id);
            highlighted.classList.add('highlight');
        }
        function onBodyLoad() {
            let hash = window.location.hash.substr("#anchor".length);
            if (hash) {
                highlightImage(hash);
            }
        }
    </script>
</head>
<body onload="onBodyLoad()">
<?php
$dataFile = 'data/data.final.json';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function array_filter_recursive($input)
{
    foreach ($input as $key => &$value) {
        if (empty($value) || !$value) {
            unset($input[$key]);
        } else if (is_array($value)) {
            $value = array_filter_recursive($value);
        }
    }
    return array_filter($input);
}
if (file_exists($dataFile)) {
    $content = file_get_contents($dataFile);
    $data = json_decode($content);
}
if ($_POST['files'] ?? false) {
    $order = array_filter_recursive($_POST['files']);
    echo "<form method='POST' class='order' name='order' onsubmit='return validateForm()'>";
    echo "<p>Bitte überprüfen Sie Ihre Bestellung:</p>";
    echo "<table class='order'>";
    echo "<tr><th>Foto</th><th>Abzüge</th><th>Datei</th><th>Preis</th></tr>";
    $total = 0;
    $prints = 0;
    $digital = 0;
    $discount = 0;
    foreach($order as $file => $item) {
        $price = ( $item['prints'] ?? 0 ) * 8 + ( $item['digital'] ?? 0) * 12;
        echo "<tr>";
        echo "</e><td><img src='/assets/images/$file'></td>";
        echo "<td>".($item['prints'] ?? '-')."</td>";
        echo "<td>".($item['digital'] ?? '-')."</td>";
        echo "<td>".sprintf("%01.2f", $price)."</td>";
        echo "</tr>";
        $prints += ($item['prints'] ?? 0);
        $digital += ($item['digital'] ?? 0);
        $total += $price;
    }
    if ($total > 50) {
        $discount = $total * 10 / 100;
    }
    if ($total > 100) {
        $discount = $total * 20 / 100;
    }
    if ($total > 200) {
        $discount = $total * 40 / 100;
    }
    if ($total > 300) {
        $discount = $total * 50 / 100;
    }

    $due = $total - $discount;
    echo "<tr><td>Gesamt</td><td>$prints</td><td>$digital</td><td>".sprintf("%01.2f", $total)."</td></tr>";
    echo "<tr><td>Rabatt</td><td colspan='2'></td><td>- ".sprintf("%01.2f", $discount)."</td></tr>";
    echo "<tr><td>Zahlbetrag</td><td colspan='2'></td><td><strong>".sprintf("%01.2f", $due)."</strong></td></tr>";
    echo "</table>";
    echo "<p>Bitte geben Sie hier Ihre E-Mailadresse an: <br />";
    echo "<input type='email' name='email'> <br />";
    echo "<p>Bemerkungen zur Bestellung<br />";
    echo "<textarea name='comment'></textarea><br />";
    echo "<input type='hidden' name='data' value='".serialize($order)."'>";
    echo "<input type='hidden' name='total' value='$total'><br />";
    echo "<input type='hidden' name='discount' value='$discount'><br />";
    echo "<input type='hidden' name='due' value='$due'><br />";
    echo "<input type='submit' class='order' name='order' value='Zahlungspflichtig bestellen'>";
    echo "</form>";
    die();
} else if ($_POST['email'] ?? false) {
    $data = unserialize($_POST['data']);
    $due = $_POST['due'];
    $t = '';
    $t .= "Bitte überweisen Sie den <strong>Gesamtbetrag in Höhe von ".sprintf("%01.2f", $due)."</strong> ";
    $t .= "Euro";
    $t .= "<p><strong>per PayPal an timor@kodal.de</strong></p>";
    $t .= "<p>ODER per Überweisung an:</p>";
    $t .= "<p>Timor Kodal<br />";
    $t .= "IBAN: DE98 1004 0000 0551 9459 00 <br />";
    $t .= "BIC:  COBADEFFXXX <br />";
    $t .= "BANK: Commerzbank Berlin <br />";
    $t .= "</p><p>";
    $t .= "Verwendungszweck:<br />Kitafotos Bethaniendamm + Name des Kindes</p>";
    $t .= "<p>Nach Zahlungseingang werden die Fotos umgehend angefertigt bzw. die digitalen Abzüge per E-Mail an <strong>$_POST[email]</strong> versendet.</p>";
    $t .= "<p>Vielen Dank für Ihre Bestellung!</p>";
    echo $t;
    $headers = "From: orders@photo.kodal.de \r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $data['email'] = $_POST['email'];
    mail($data['email'], "Bestellung bei photo.kodal.de eingegangen!", $t, $headers);

    $headers .= "Reply-To: $_POST[email]";
    $data['total'] = $_POST['total'];
    $data['discount'] = $_POST['discount'];
    $data['due'] = $_POST['due'];
    $data['comment'] = $_POST['comment'];
    $body = json_encode($data, true);
    mail("timor@kodal.de", "Bestellung eingegangen", $body, $headers);
    die();
}
?>
<form method="POST">
    <table>
        <tr>
            <th>Foto</th><th>Person(en)*</th><th>Gruppe(n)*</th>
            <th>Anzahl Abzüge</th>
            <th>Digital</th>
            <th><input type="submit" value="senden" /></th>
        </tr>
        <?php
        $files = scandir('./assets/images');
        foreach($files as $key => $file) {
            if (substr($file,0,1) !== '.') {
                echo "<tr>";
                echo "<td><a name='anchor$key'><img id='image$key' src='./assets/images/$file' onclick='highlightImage(\"$key\");'></a></td>";
                echo "<td>".($data->$file->persons ?? '')."</td>";
                echo "<td>".($data->$file->groups ?? '')."</td>";
                echo "<td><input onfocus='highlightImage(\"$key\");' name='files[$file][prints]'></td>";
                echo "<td><input onfocus='highlightImage(\"$key\");' type='checkbox' name='files[$file][digital]' value='1'></td>";
                echo "<td></td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
    <input type="submit" value="senden" />
</form></body>

