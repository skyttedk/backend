<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billede Upload, Crop og Gem</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        #image-container {
            width: 80vw;
            height: 80vh;
            margin: 20px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        #image-container img, #image-container canvas {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        #button-container {
            text-align: center;
        }
        button {
            margin: 10px;
        }
    </style>
</head>
<body>
<h1>Billede Upload, Crop og Gem</h1>
<input type="file" id="file-input" accept="image/*">
<div id="image-container"></div>
<div id="button-container">
    <button id="start-crop-button" style="display: none;">Start Crop</button>
    <button id="cancel-crop-button" style="display: none;">Annuller Crop</button>
    <button id="crop-button" style="display: none;">Crop Billede</button>
    <button id="recrop-button" style="display: none;">Gen-crop Billede</button>
    <button id="save-button" style="display: none;">Gem Billede</button>
</div>

<script>
    let cropper;
    let originalImage;
    const fileInput = document.getElementById('file-input');
    const imageContainer = document.getElementById('image-container');
    const startCropButton = document.getElementById('start-crop-button');
    const cancelCropButton = document.getElementById('cancel-crop-button');
    const cropButton = document.getElementById('crop-button');
    const recropButton = document.getElementById('recrop-button');
    const saveButton = document.getElementById('save-button');

    function initCropper(imageElement) {
        cropper = new Cropper(imageElement, {
            aspectRatio: NaN,
            viewMode: 0,
            zoomable: false,
            scalable: false,
            minContainerWidth: imageContainer.clientWidth,
            minContainerHeight: imageContainer.clientHeight,
        });
        cropButton.style.display = 'inline-block';
        cancelCropButton.style.display = 'inline-block';
        startCropButton.style.display = 'none';
        recropButton.style.display = 'none';
        saveButton.style.display = 'none';
    }

    function resetImage() {
        if (cropper) {
            cropper.destroy();
        }
        displayImage(originalImage);
        startCropButton.style.display = 'inline-block';
        cancelCropButton.style.display = 'none';
        cropButton.style.display = 'none';
        recropButton.style.display = 'none';
        saveButton.style.display = 'none';
    }

    function displayImage(src) {
        imageContainer.innerHTML = '<img id="image" src="' + src + '">';
        const img = document.getElementById('image');
        img.style.maxWidth = '100%';
        img.style.maxHeight = '100%';
    }

    function scaleImage(imageData, maxDimension) {
        const img = new Image();
        return new Promise((resolve) => {
            img.onload = function() {
                let width = img.width;
                let height = img.height;
                if (width > height) {
                    if (width > maxDimension) {
                        height *= maxDimension / width;
                        width = maxDimension;
                    }
                } else {
                    if (height > maxDimension) {
                        width *= maxDimension / height;
                        height = maxDimension;
                    }
                }
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                resolve(canvas.toDataURL());
            };
            img.src = imageData;
        });
    }

    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const reader = new FileReader();

        reader.onload = function(event) {
            originalImage = event.target.result;
            displayImage(originalImage);
            startCropButton.style.display = 'inline-block';
        }

        reader.readAsDataURL(file);
    });

    startCropButton.addEventListener('click', function() {
        const image = document.getElementById('image');
        initCropper(image);
    });

    cancelCropButton.addEventListener('click', function() {
        resetImage();
    });

    cropButton.addEventListener('click', function() {
        const croppedCanvas = cropper.getCroppedCanvas();
        cropper.destroy();
        imageContainer.innerHTML = '';
        imageContainer.appendChild(croppedCanvas);
        croppedCanvas.style.maxWidth = '100%';
        croppedCanvas.style.maxHeight = '100%';
        cropButton.style.display = 'none';
        cancelCropButton.style.display = 'none';
        recropButton.style.display = 'inline-block';
        saveButton.style.display = 'inline-block';
    });

    recropButton.addEventListener('click', function() {
        resetImage();
        const image = document.getElementById('image');
        initCropper(image);
    });

    saveButton.addEventListener('click', async function() {
        const imageData = imageContainer.querySelector('canvas').toDataURL('image/jpeg');
        const scaledImageData = await scaleImage(imageData, 1000);

        // Her sender vi billedet til backend
        fetch('/upload', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ image: scaledImageData }),
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                alert('Billede gemt succesfuldt!');
            })
            .catch((error) => {
                console.error('Error:', error);
                alert('Der opstod en fejl ved gemning af billedet.');
            });
    });
</script>
</body>
</html>