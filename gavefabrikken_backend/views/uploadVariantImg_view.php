<!DOCTYPE HTML>

<html>

<head>
  <title>Untitled</title>
   <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <link rel="stylesheet" href="https://rawgit.com/enyo/dropzone/master/dist/dropzone.css">
   <script src="lib/jquery.min.js"></script>
   <script src="js/drop.js"></script>

<style>
html,body{
  margin:0px;
  padding:0px;
  width: 100%;
  height:100%;
}

</style>

<script>
var _single = true
function controlDropElemet(activeElementName)
{
    // dropzone.destroy()
    if(_single == true){
        _single = false;
        var obj = JSON.parse(activeElementName);
        parent.variant.insertImg(obj.newName)
    }


}

</script>

</head>

<body>

<form action="../index.php?rt=upload/giftImg&target=variant" class="dropzone" style="height: 100%; width: 100%;" ></form>


</body>

</html>



