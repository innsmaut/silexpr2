<?php
$baseLink = 'http://'.$_SERVER['SERVER_NAME'].strstr($_SERVER['SCRIPT_NAME'], 'index.php', true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insert links</title>
</head>
<body>
<p><a href="<?= $baseLink.'create'; ?>">Create new entry</a></p>
<p>Active entries:</p>

<?php if ($result !== []){
    echo '<table>';
    foreach ($result as $item) {
        $item['expired_on'] = ($item['expired_on'] === '0')?'-':date_timestamp_set(date_create(), $item['expired_on'])
            ->format("Y-m-d H:i:s");
        $item['password'] = ($item['password'] === '')?'No':'Yes';
        echo <<<"STR"
<tr>
    <td>Link: <a href='{$item['redirect_link']}'>{$item['claimed_link']}</a></td>
    <td>Expiration: {$item['expired_on']}</td>
    <td>Secured: {$item['password']}</td>
</tr>
STR;
    }
    echo '</table>';
} else {
    echo 'No active links found.';
};
?>

<p>Current time is: <?= date_create()->format("Y-m-d H:i:s"); ?></p>
</body>
</html>
<?php return ''; // otherwise including this file will return '1' for success into main page
?>