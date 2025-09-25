<div style="width: 500px; height: 500px; margin: 50px; background: rgba(0,0,0,0.1); border-radius: 10px; padding: 10px; text-align: center;">
    <h2>This is the example component</h2>
    <p>ASSET PATH:<br><?php echo $assetPath; ?></p>
    <p>SERVICE PATH:<br><?php echo $servicePath; ?></p>

    <input type="text" id="number1" style="width:50px;"> +
    <input type="text" id="number2" style="width:50px;"> = <input type="text" id="numberresult" style="width:50px;">

    <button type="button" id="exampleButton">Calculate</button>

    <script src="<?php echo GFConfig::BACKEND_URL; ?>views/lib/jquery.min.js"></script>
    <script>

        var unitObj = null;
        function exampleUnitReady() {
            console.log('example unit ready');
            unitObj = new exampleUnit();
            unitObj.run('<?php echo $assetPath; ?>','<?php echo $servicePath; ?>');
        }

    </script>
    <script src="<?php echo $assetPath ?>unit.js"></script>


</div>