<html>
<head>

    <style>
        body { font-family: verdana; font-size: 16px; padding: 0px; margin: 0px; background: #F0F0F0; }
    </style>

</head>
<body>

<form method="post" action="index.php?rt=unit/postnord/varerapport/download">
    <div style="width: 400px; text-align: center; margin-top: 50px; margin-left: auto; margin-right: auto; background: #FFFFFF; border-radius: 10px; border: 1px solid #E0E0E0; padding: 20px;">
        <h2>Postnord lager rapport</h2>

        V<?php echo utf8_decode("æ"); ?>lg varer i rapport:<br>
        <select name="showitem">
            <option value="0">Vis kun varer postnord mangler</option>
            <option value="2">Vis kun varer med aktivitet</option>
            <option value="1">Vis alle varer</option>
        </select><br><br>

        <button>Hent rapport</button><br><br>

    </div>
</form>

</body>
</html>