<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slide Vælger</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .sidepanel {
            position: fixed;
            left: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background-color: #f8f9fa;
            transition: left 0.3s ease-in-out;
            z-index: 1000;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidepanel.active {
            left: 0;
        }

        .gift-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fff;
            cursor: move;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .gift-row.dragging {
            opacity: 0.5;
            border: 2px dashed #666;
            position: relative;
            z-index: 1000;
        }

        .gift-row.drag-over {
            border-top: 2px solid #4CAF50;
        }

        .gift-name {
            width: 120px;
            font-weight: bold;
            margin-right: 15px;
        }

        .placeholder-container {
            display: flex;
            flex-grow: 1;
            gap: 10px;
            flex-wrap: wrap;
        }

        .placeholder {
            border: 2px dashed #ccc;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            min-height: 60px;
            background-color: #f8f9fa;
            flex: 1;
            min-width: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .placeholder.selected {
            border: 2px solid #28a745;
            background-color: #e9f7ef;
        }

        .gift-list {
            margin-top: 20px;
        }

        .gift-item {
            padding: 10px;
            margin: 5px 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }

        .gift-item:hover {
            background-color: #e9ecef;
        }

        .context-menu {
            display: none;
            position: fixed;
            background-color: white;
            border: 1px solid #ccc;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
            padding: 5px 0;
        }

        .context-menu-item {
            padding: 8px 15px;
            cursor: pointer;
        }

        .context-menu-item:hover {
            background-color: #f0f0f0;
        }

        #scrollIndicator {
            position: fixed;
            width: 100%;
            height: 100px;
            pointer-events: none;
            z-index: 1001;
            display: none;
        }

        #scrollIndicator.top {
            top: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), transparent);
        }

        #scrollIndicator.bottom {
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.1), transparent);
        }
    </style>
</head>
<body>
<div id="scrollIndicator"></div>

<div class="context-menu" id="placeholderMenu">
    <div class="context-menu-item" id="removeGift">Fjern gave</div>
</div>

<div class="sidepanel" id="giftSidepanel">
    <h3>Vælg et slide</h3>
    <div class="gift-list" id="giftList"></div>
</div>

<div class="container mt-5" id="giftContainer">
    <!-- Gave rækker genereres dynamisk -->
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        let activePlaceholder = null;
        let gifts = [];
        const selectedGifts = new Set();
        const placeholdersPerGift = 4;
        let autoScrollInterval = null;
        const SCROLL_SPEED = 15;
        const SCROLL_ZONE = 100; // pixels from top/bottom that triggers scroll

        function createGiftRows(count) {
            gifts = Array.from({length: count}, (_, i) => ({
                id: i + 1,
                name: `Gave ${i + 1}`
            }));

            const container = $('#giftContainer');
            container.empty();

            // Create slides (rows)
            for (let slideNum = 1; slideNum <= count; slideNum++) {
                const placeholders = Array.from({length: placeholdersPerGift}, (_, i) => {
                    // For first placeholder of each row, pre-fill with corresponding gift if it matches the slide number
                    if (i === 0 && slideNum <= 2) {  // Kun de første to slides skal have forudfyldt første placeholder
                        selectedGifts.add(slideNum);
                        return `<div class="placeholder" data-gift="${slideNum}" data-position="${i + 1}">Gave ${slideNum}</div>`;
                    }
                    return `<div class="placeholder" data-gift="${slideNum}" data-position="${i + 1}">Placeholder</div>`;
                }).join('');

                const slideRow = $(`
                <div class="gift-row" draggable="true">
                    <div class="gift-name">Slide ${slideNum}</div>
                    <div class="placeholder-container">
                        ${placeholders}
                    </div>
                </div>
            `);

                container.append(slideRow);

                // Hvis denne række havde en forudfyldt gave, tilføj den til selectedGifts
                if (slideNum <= 2) {  // Kun de første to slides har forudfyldte gaver
                    selectedGifts.add(slideNum);
                }
            };

            initializeEventListeners();
        }

        function handleDragScroll(e) {
            const windowHeight = window.innerHeight;
            const mouseY = e.clientY;

            clearInterval(autoScrollInterval);

            if (mouseY < SCROLL_ZONE) {
                // Scroll up
                autoScrollInterval = setInterval(() => {
                    window.scrollBy(0, -SCROLL_SPEED);
                }, 20);
                $('#scrollIndicator').show().removeClass('bottom').addClass('top');
            } else if (mouseY > windowHeight - SCROLL_ZONE) {
                // Scroll down
                autoScrollInterval = setInterval(() => {
                    window.scrollBy(0, SCROLL_SPEED);
                }, 20);
                $('#scrollIndicator').show().removeClass('top').addClass('bottom');
            } else {
                $('#scrollIndicator').hide();
            }
        }

        function hasAssignedGifts(giftId) {
            const placeholders = $(`.placeholder[data-gift="${giftId}"]`);
            return placeholders.toArray().some(placeholder =>
                $(placeholder).text() !== 'Placeholder'
            );
        }

        function showSidepanel(placeholder) {
            const giftId = parseInt($(placeholder).data('gift'));
            const $giftList = $('#giftList');
            $giftList.empty();

            const availableGifts = gifts.filter(gift =>
                gift.id !== giftId &&
                !selectedGifts.has(gift.id) &&
                !hasAssignedGifts(gift.id)
            );

            availableGifts.forEach(gift => {
                $('<div>')
                    .addClass('gift-item')
                    .attr('data-id', gift.id)
                    .text(gift.name)
                    .appendTo($giftList);
            });

            $('#giftSidepanel').addClass('active');
        }

        function initializeEventListeners() {
            // Placeholder click
            $('.placeholder').off('click').on('click', function() {
                activePlaceholder = this;
                $('.placeholder').removeClass('selected');
                $(this).addClass('selected');
                showSidepanel(this);
            });

            // Context menu for placeholders
            $('.placeholder').off('contextmenu').on('contextmenu', function(e) {
                if ($(this).text() !== 'Placeholder') {
                    e.preventDefault();
                    activePlaceholder = this;
                    $('#placeholderMenu').css({
                        top: e.pageY,
                        left: e.pageX,
                        display: 'block'
                    });
                }
            });

            // Drag and drop
            $('.gift-row').off('dragstart').on('dragstart', function() {
                $(this).addClass('dragging');
            }).off('dragend').on('dragend', function() {
                $(this).removeClass('dragging');
                $('.gift-row').removeClass('drag-over');
                clearInterval(autoScrollInterval);
                $('#scrollIndicator').hide();
            });

            $('.gift-row').off('dragover').on('dragover', function(e) {
                e.preventDefault();
                handleDragScroll(e);
                const draggingItem = $('.dragging');
                if (!draggingItem.is(this)) {
                    $(this).addClass('drag-over');
                }
            }).off('dragleave').on('dragleave', function() {
                $(this).removeClass('drag-over');
            });

            $('.gift-row').off('drop').on('drop', function(e) {
                e.preventDefault();
                const draggingItem = $('.dragging');
                if (!draggingItem.is(this)) {
                    if ($(this).index() > draggingItem.index()) {
                        $(this).after(draggingItem);
                    } else {
                        $(this).before(draggingItem);
                    }
                }
                $('.gift-row').removeClass('drag-over');
                clearInterval(autoScrollInterval);
                $('#scrollIndicator').hide();
            });
        }

        // Gift selection
        $('#giftList').on('click', '.gift-item', function() {
            if (!activePlaceholder) return;

            const giftId = $(this).data('id');
            const giftName = $(this).text();

            $(activePlaceholder).text(giftName);
            $(activePlaceholder).removeClass('selected');

            selectedGifts.add(giftId);

            // Find og fjern rækken for den valgte gave
            $(`.gift-row:has(.gift-name:contains("${giftName}"))`).fadeOut(300, function() {
                $(this).remove();
            });

            $('#giftSidepanel').removeClass('active');
            activePlaceholder = null;
        });

        // Remove gift
        $('#removeGift').on('click', function() {
            if (!activePlaceholder) return;

            const giftName = $(activePlaceholder).text();
            const giftId = parseInt(giftName.replace('Gave ', ''));

            selectedGifts.delete(giftId);
            $(activePlaceholder).text('Placeholder');
            $('#placeholderMenu').hide();

            // Tilføj ny gaverækkke i bunden
            const newRow = $(`
            <div class="gift-row" draggable="true" style="display: none;">
                <div class="gift-name">${giftName}</div>
                <div class="placeholder-container">
                    <div class="placeholder" data-gift="${giftId}" data-position="1">Placeholder</div>
                    <div class="placeholder" data-gift="${giftId}" data-position="2">Placeholder</div>
                    <div class="placeholder" data-gift="${giftId}" data-position="3">Placeholder</div>
                    <div class="placeholder" data-gift="${giftId}" data-position="4">Placeholder</div>
                </div>
            </div>
        `);

            $('#giftContainer').append(newRow);
            newRow.fadeIn(300);

            // Genaktiver event handlers på den nye række
            newRow.find('.placeholder').on('click', function() {
                activePlaceholder = this;
                $('.placeholder').removeClass('selected');
                $(this).addClass('selected');
                showSidepanel(this);
            }).on('contextmenu', function(e) {
                if ($(this).text() !== 'Placeholder') {
                    e.preventDefault();
                    activePlaceholder = this;
                    $('#placeholderMenu').css({
                        top: e.pageY,
                        left: e.pageX,
                        display: 'block'
                    });
                }
            });

            // Genaktiver drag and drop på den nye række
            newRow.on('dragstart', function() {
                $(this).addClass('dragging');
            }).on('dragend', function() {
                $(this).removeClass('dragging');
                $('.gift-row').removeClass('drag-over');
                clearInterval(autoScrollInterval);
                $('#scrollIndicator').hide();
            }).on('dragover', function(e) {
                e.preventDefault();
                handleDragScroll(e);
                const draggingItem = $('.dragging');
                if (!draggingItem.is(this)) {
                    $(this).addClass('drag-over');
                }
            }).on('dragleave', function() {
                $(this).removeClass('drag-over');
            }).on('drop', function(e) {
                e.preventDefault();
                const draggingItem = $('.dragging');
                if (!draggingItem.is(this)) {
                    if ($(this).index() > draggingItem.index()) {
                        $(this).after(draggingItem);
                    } else {
                        $(this).before(draggingItem);
                    }
                }
                $('.gift-row').removeClass('drag-over');
                clearInterval(autoScrollInterval);
                $('#scrollIndicator').hide();
            });

            activePlaceholder = null;
        });

        // Close menus when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.sidepanel, .placeholder, .context-menu').length) {
                $('#giftSidepanel').removeClass('active');
                $('#placeholderMenu').hide();
                $('.placeholder').removeClass('selected');
                activePlaceholder = null;
            }
        });

        // Stop auto-scroll when mouse leaves window
        $(window).on('mouseout', function(e) {
            if (!e.relatedTarget) {
                clearInterval(autoScrollInterval);
                $('#scrollIndicator').hide();
            }
        });

        // Initial setup med 50 gaver
        createGiftRows(50);
    });
</script>
</body>
</html>