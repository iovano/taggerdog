<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHOTO.KODAL | Image Tagger</title>
    <link href="./assets/styles.css" type="text/css" rel="stylesheet" />
    <script>
        let highlighted;
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
$dataFile = 'data/data.json';
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
    echo "<form method='POST'>";
    echo "<p>Bitte überprüfen Sie Ihre Bestellung:</p>";
    echo "<table>";
    echo "<tr><th>Foto</th><th>Abzüge</th><th>Datei</th><th>Preis</th></tr>";
    $total = 0;
    foreach($order as $file => $item) {
        $price = ( $item['prints'] ?? 0 ) * 8 + ( $item['digital'] ?? 0) * 12;
        echo "<tr>";
        echo "</e><td><img src='/assets/images/$file'></td>";
        echo "<td>".($item['prints'] ?? '-')."</td>";
        echo "<td>".($item['digital'] ?? '-')."</td>";
        echo "<td>".sprintf("%01.2f", $price)."</td>";
        echo "</tr>";
        $total += $price;
    }
    echo "</table>";
    echo "<p>Bitte geben Sie hier Ihre E-Mailadresse an:";
    echo "<input type='email' name='email'> <br />";
    echo "<input type='hidden' name='data' value='".serialize($order)."'>";
    echo "<input type='hidden' name='total' value='$total'>";
    echo "<input type='submit' name='order' value='Zahlungspflichtig bestellen'>";
    echo "</form>";
    die();
} else if ($_POST['email'] ?? false) {
    $data = unserialize($_POST['data']);
    $total = $_POST['total'];
    echo "Bitte überweisen Sie den <strong>Gesamtbetrag in Höhe von ".sprintf("%01.2f", $total)."</strong> ";
    echo "Euro";
    echo "<p><strong>per PayPal an timor@kodal.de</strong></p>";
    echo "<p>ODER per Überweisung an:</p>";
    echo "<p>Timor Kodal<br />";
    echo "IBAN: DE98 1004 0000 0551 9459 00 <br />";
    echo "BIC:  COBADEFFXXX <br />";
    echo "BANK: Commerzbank Berlin <br />";
    echo "</p><p>";
    echo "Verwendungszweck:<br />Kitafotos Bethaniendamm + Name des Kindes</p>";
    echo "<p>Nach Zahlungseingang werden die Fotos umgehend angefertigt bzw. die digitalen Abzüge per E-Mail an <strong>$_POST[email]</strong> versendet.</p>";
    echo "<p>Vielen Dank für Ihre Bestellung!</p>";
    $headers = "From: orders@photo.kodal.de \r\n";
    $headers .= "Reply-To: $_POST[email]";
    $data['email'] = $_POST['email'];
    $data['total'] = $_POST['total'];
    $body = var_export($data, true);
    mail("timor@kodal.de", "Bestellung eingegangen", $body, $headers);
    die();
}
?>
<form method="POST">
    <input type="submit" value="absenden" />
    <table>
        <tr>
            <th>Foto</th><th>Person(en)*</th><th>Gruppe(n)*</th>
            <th>Anzahl Abzüge</th>
            <th>Digital</th>
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
                echo "</tr>";
            }
        }
        ?>
    </table>
    <input type="submit" value="absenden" />
</form></body>